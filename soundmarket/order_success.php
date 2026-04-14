<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();
$pdo = db();
$user_id = current_user_id();

// Вземаме последните платени продукти на потребителя
$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.file_path 
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ? AND o.status = 'paid'
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$paid_products = $stmt->fetchAll();

$title = "Успешна поръчка";
require __DIR__ . '/inc/header.php';
?>

<div class="container" style="padding: 60px 0; text-align: center;">
    <h1 style="color: #4ade80;">🎉 Плащането е успешно!</h1>
    <p class="muted">Благодарим ви за покупката. Можете да свалите вашите файлове отдолу:</p>

    <div class="grid" style="max-width: 800px; margin: 40px auto;">
        <?php foreach ($paid_products as $p): ?>
            <div class="card" style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: bold;"><?= h($p['title']) ?></span>
                <?php if (!empty($p['file_path'])): ?>
                    <a href="download.php?id=<?= $p['id'] ?>" class="btn primary">⬇ Свали файла</a>
                <?php else: ?>
                    <span class="pill">Няма наличен файл</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <a href="index.php" class="btn">Към началната страница</a>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>