<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/config.php';
require_once dirname(__DIR__) . '/inc/db.php';
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/functions.php';
require_once __DIR__ . '/stripe_helper.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/checkout.php');
    exit;
}

$pdo = db();
$uid = (int)current_user_id();

// ── Collect and sanitise form fields ─────────────────────────────────────────
$first_name     = trim((string)($_POST['first_name']     ?? ''));
$last_name      = trim((string)($_POST['last_name']      ?? ''));
$phone          = trim((string)($_POST['phone']          ?? ''));
$city           = trim((string)($_POST['city']           ?? ''));
$address        = trim((string)($_POST['address']        ?? ''));
$payment_method = in_array($_POST['payment_method'] ?? '', ['cod', 'stripe'], true)
                    ? (string)$_POST['payment_method']
                    : 'cod';
$full_name = trim($first_name . ' ' . $last_name);

// ── Load cart ─────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT ci.product_id, ci.quantity, p.title, p.price_eur
    FROM cart_items ci
    JOIN products p ON p.id = ci.product_id
    WHERE ci.user_id = ?
");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: ' . APP_BASE . '/cart.php');
    exit;
}

// ── Calculate total (EUR cents after migration) ───────────────────────────────
$total = 0;
foreach ($items as $item) {
    $total += (int)$item['price_eur'] * (int)$item['quantity'];
}

// ── Persist order atomically ──────────────────────────────────────────────────
$pdo->beginTransaction();
try {
    $pdo->prepare("
        INSERT INTO orders
            (user_id, total_eur, customer_name, phone, city, address, payment_method, status, currency)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'created', 'EUR')
    ")->execute([$uid, $total, $full_name, $phone, $city, $address, $payment_method]);

    $order_id = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price_eur)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmt->execute([
            $order_id,
            (int)$item['product_id'],
            (int)$item['quantity'],
            (int)$item['price_eur'],
        ]);
    }

    // Notify product owners of the sale
    foreach ($items as $item) {
        $owner_stmt = $pdo->prepare("SELECT owner_id FROM products WHERE id = ?");
        $owner_stmt->execute([(int)$item['product_id']]);
        $owner_id = (int)($owner_stmt->fetchColumn() ?: 0);
        if ($owner_id && $owner_id !== $uid) {
            notify($pdo, $owner_id, 'sale',
                'Продадохте „' . $item['title'] . '" за ' . format_eur((int)$item['price_eur']),
                APP_BASE . '/dashboard.php'
            );
        }
    }

    // Clear cart
    $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$uid]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    exit('Грешка при обработка на поръчката. Моля, опитайте отново.');
}

// ── Cash-on-delivery: no Stripe needed ───────────────────────────────────────
if ($payment_method === 'cod') {
    header('Location: ' . APP_BASE . '/order_success.php?order=' . $order_id);
    exit;
}

// ── Card payment: create Stripe Checkout Session ──────────────────────────────
$line_items = [];
foreach (array_values($items) as $i => $item) {
    $line_items[$i] = [
        'price_data' => [
            'currency'     => 'eur',
            'unit_amount'  => (int)$item['price_eur'],   // already EUR cents
            'product_data' => [
                'name' => $item['title'],
            ],
        ],
        'quantity' => (int)$item['quantity'],
    ];
}

$session = stripe_request('POST', 'checkout/sessions', [
    'mode'        => 'payment',
    'line_items'  => $line_items,
    'success_url' => BASE_URL . '/payment/success.php?session_id={CHECKOUT_SESSION_ID}&order=' . $order_id,
    'cancel_url'  => BASE_URL . '/payment/cancel.php?order=' . $order_id,
]);

if (isset($session['error'])) {
    // Stripe call failed — order already saved, user can retry or contact support
    $msg = htmlspecialchars($session['error']['message'] ?? 'Неизвестна грешка', ENT_QUOTES, 'UTF-8');
    $title = 'Грешка при плащане — ' . APP_NAME;
    require dirname(__DIR__) . '/inc/header.php';
    echo '<div class="container" style="padding:80px 0;text-align:center;">';
    echo '<h2 style="color:#f87171;">Грешка при свързване с платежната система</h2>';
    echo '<p class="muted">' . $msg . '</p>';
    echo '<p class="muted">Поръчка <strong>#' . $order_id . '</strong> е запазена. ';
    echo 'Свържете се с нас или опитайте наложен платеж.</p>';
    echo '<a href="' . APP_BASE . '/checkout.php" class="btn">Обратно към поръчката</a>';
    echo '</div>';
    require dirname(__DIR__) . '/inc/footer.php';
    exit;
}

// Store Stripe session ID for webhook / success reconciliation
$pdo->prepare("UPDATE orders SET stripe_session_id = ? WHERE id = ?")
    ->execute([$session['id'], $order_id]);

// Redirect user to Stripe-hosted checkout page
header('Location: ' . $session['url']);
exit;
