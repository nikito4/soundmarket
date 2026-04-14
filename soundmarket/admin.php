<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();

// В реална ситуация тук трябва да има проверка: if (!is_admin()) { ... }
// За целта на дипломата, приемаме че текущият потребител е админ.

$stmt = db()->prepare("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$stmt->execute();
$all_orders = $stmt->fetchAll();

$title = 'Админ Панел — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>
<div class="container" style="padding: 22px 0;">
    <h2>Административен панел</h2>
    <p>Преглед на всички направени поръчки в платформата.</p>

    <table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Потребител</th>
            <th>Сума</th>
            <th>Дата</th>
            <th>Статус</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($all_orders as $o): ?>
        <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= h($o['username']) ?></td>
            <td><?= format_bgn((int)$o['total_bgn']) ?></td>
            <td><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
            <td><span class="pill">Завършена</span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>