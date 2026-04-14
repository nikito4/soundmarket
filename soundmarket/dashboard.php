<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();

$title = 'Профил — ' . APP_NAME;
require __DIR__ . '/inc/header.php';

$stmt = db()->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([current_user_id()]);
$orders = $stmt->fetchAll();
?>

<div class="container" style="padding: 40px 0;">
  <div class="page-head" style="margin-bottom: 30px;">
    <h2 style="font-size: 2.5rem;">Здравей, <?= h(current_username()) ?> 👋</h2>
    <p class="muted">Добре дошъл във вашия контролен панел.</p>
  </div>

  <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <a href="<?= APP_BASE ?>/product_create.php" class="card" style="text-align: center; padding: 30px; text-decoration: none; border-color: var(--brand);">
        <span style="font-size: 32px; display: block; margin-bottom: 10px;">➕</span>
        <strong style="color: var(--text);">Качи оферта</strong>
    </a>
    <a href="<?= APP_BASE ?>/my_products.php" class="card" style="text-align: center; padding: 30px; text-decoration: none;">
        <span style="font-size: 32px; display: block; margin-bottom: 10px;">🎼</span>
        <strong style="color: var(--text);">Моите оферти</strong>
    </a>
    <a href="<?= APP_BASE ?>/cart.php" class="card" style="text-align: center; padding: 30px; text-decoration: none;">
        <span style="font-size: 32px; display: block; margin-bottom: 10px;">🛒</span>
        <strong style="color: var(--text);">Количка</strong>
    </a>
  </div>

  <section class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 20px 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Последни поръчки</h3>
        <span class="pill">История</span>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="table" style="margin: 0; width: 100%; border: none;">
            <thead>
                <tr>
                    <th>Поръчка ID</th>
                    <th>Сума</th>
                    <th>Дата</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--muted);">Все още нямате направени поръчки.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= format_bgn((int)$o['total_bgn']) ?></td>
                            <td><?= date('d.m.Y', strtotime($o['created_at'])) ?></td>
                            <td><span class="pill" style="background: rgba(34, 197, 94, 0.1); color: #4ade80;">Платено</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
  </section>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>