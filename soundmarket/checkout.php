<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();

$user_id = current_user_id();
$pdo = db();

// Вземаме елементите от количката
$stmt = $pdo->prepare("
    SELECT ci.*, p.title, p.price_bgn 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE ci.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

if (!$items) {
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += $item['price_bgn'] * $item['quantity'];
}

$title = "Завършване на поръчка";
require __DIR__ . '/inc/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <div class="card" style="max-width: 600px; margin: 0 auto; padding: 30px;">
        <h2>Потвърждение на поръчката</h2>
        <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">
        
        <?php foreach ($items as $item): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span><?= h($item['title']) ?> (x<?= $item['quantity'] ?>)</span>
                <strong><?= format_bgn((int)$item['price_bgn']) ?></strong>
            </div>
        <?php endforeach; ?>

        <div style="margin-top: 20px; font-size: 1.5rem; display: flex; justify-content: space-between;">
            <span>Общо:</span>
            <strong style="color: var(--brand);"><?= format_bgn((int)$total) ?></strong>
        </div>

        <form action="process_order.php" method="POST" style="margin-top: 30px;">
            <button type="submit" class="btn primary" style="width: 100%; padding: 15px;">
                Потвърди и Плати (Демо)
            </button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>