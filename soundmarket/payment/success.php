<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/config.php';
require_once dirname(__DIR__) . '/inc/db.php';
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/functions.php';
require_once __DIR__ . '/stripe_helper.php';

require_login();

$session_id = trim((string)($_GET['session_id'] ?? ''));
$order_id   = (int)($_GET['order'] ?? 0);

if ($session_id === '' || $order_id === 0) {
    header('Location: ' . APP_BASE . '/dashboard.php');
    exit;
}

$pdo = db();

// ── Verify payment with Stripe ────────────────────────────────────────────────
$session = stripe_request('GET', 'checkout/sessions/' . rawurlencode($session_id));

$is_paid = isset($session['id'])
        && ($session['payment_status'] ?? '') === 'paid';

if ($is_paid) {
    // Mark order as paid (idempotent — safe to call multiple times)
    $pdo->prepare("
        UPDATE orders SET status = 'paid', stripe_session_id = ?
        WHERE id = ? AND user_id = ?
    ")->execute([$session_id, $order_id, (int)current_user_id()]);
}

// ── Load order ────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT id, total_eur, created_at
    FROM orders
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$order_id, (int)current_user_id()]);
$order = $stmt->fetch();

$paid_products = [];
if ($order) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.file_path, oi.quantity, oi.price_eur
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([(int)$order['id']]);
    $paid_products = $stmt->fetchAll();
}

// ── Render ────────────────────────────────────────────────────────────────────
if (!$is_paid) {
    $title = 'Плащане в обработка — ' . APP_NAME;
    require dirname(__DIR__) . '/inc/header.php';
    ?>
    <div class="container" style="padding:80px 0; text-align:center;">
        <div style="font-size:3rem; margin-bottom:20px;">⏳</div>
        <h2>Плащането се обработва…</h2>
        <p class="muted">Моля, изчакайте или проверете профила си след малко.</p>
        <a href="<?= APP_BASE ?>/dashboard.php" class="btn">Моят профил</a>
    </div>
    <?php
    require dirname(__DIR__) . '/inc/footer.php';
    exit;
}

$title = 'Успешно плащане — ' . APP_NAME;
require dirname(__DIR__) . '/inc/header.php';
?>

<div class="container" style="padding: 60px 0; text-align: center;">
    <div style="font-size:4rem; margin-bottom:20px;">🎉</div>
    <h1 style="color: #4ade80;">Плащането е успешно!</h1>
    <p class="muted">Благодарим за покупката. Вашите файлове са готови за сваляне.</p>

    <?php if ($order): ?>
        <p class="muted" style="font-size:14px;">
            Поръчка <strong>#<?= (int)$order['id'] ?></strong> &nbsp;•&nbsp;
            Общо: <strong><?= format_eur((int)$order['total_eur']) ?></strong>
        </p>
    <?php endif; ?>

    <div class="grid" style="max-width:800px; margin:40px auto;">
        <?php foreach ($paid_products as $p): ?>
            <div class="card" style="padding:20px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:bold;"><?= h($p['title']) ?></span>
                <?php if (!empty($p['file_path'])): ?>
                    <a href="<?= APP_BASE ?>/download.php?id=<?= (int)$p['id'] ?>"
                       class="btn primary">&#x2B07; Свали файла</a>
                <?php else: ?>
                    <span class="pill">Без файл</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if (empty($paid_products)): ?>
            <p class="muted">Няма намерени продукти за тази поръчка.</p>
        <?php endif; ?>
    </div>

    <div style="display:flex; gap:15px; justify-content:center;">
        <a href="<?= APP_BASE ?>/index.php" class="btn">Начална страница</a>
        <a href="<?= APP_BASE ?>/dashboard.php" class="btn primary">Моят профил</a>
    </div>
</div>

<?php require dirname(__DIR__) . '/inc/footer.php'; ?>
