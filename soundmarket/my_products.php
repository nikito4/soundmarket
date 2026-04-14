<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();

$stmt = db()->prepare("SELECT * FROM products WHERE owner_id=? ORDER BY created_at DESC");
$stmt->execute([current_user_id()]);
$items = $stmt->fetchAll();

$title = 'Моите оферти — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>
<div class="container" style="padding: 22px 0;">
  <div class="page-head" style="margin-bottom: 30px;">
    <h2>Моите оферти</h2>
    <p class="muted">Управлявай своите бийтове, музика и услуги.</p>
  </div>

  <div class="grid">
    <?php foreach ($items as $p): ?>
      <article class="card">
        <?php 
          $userCover = !empty($p['cover_path']) ? APP_BASE . '/' . h($p['cover_path']) : null;
          $defaultCover = APP_BASE . '/uploads/blog-vinyl-lp.800x0.jpg';
          $finalCover = $userCover ?: $defaultCover;
        ?>
        <div class="card-img-container" style="background-image: url('<?= $finalCover ?>');">
            <div class="card-media-overlay">
                <span class="pill" style="background: rgba(0,0,0,0.6);">ID: #<?= (int)$p['id'] ?></span>
            </div>
        </div>

        <div class="card-body">
          <div class="card-title">
            <h3><?= h($p['title']) ?></h3>
            <small class="pill" style="font-size: 10px;"><?= h($p['type']) ?></small>
          </div>

          <div class="meta">
            <?= h((string)($p['genre'] ?? '')) ?> <?= $p['bpm'] ? '• ' . (int)$p['bpm'] . ' BPM' : '' ?>
          </div>

          <div class="price-row">
            <strong><?= format_bgn((int)$p['price_bgn']) ?></strong>
          </div>

          <div class="actions" style="grid-template-columns: 1fr 1fr;">
            <a class="btn" href="<?= APP_BASE ?>/product.php?id=<?= (int)$p['id'] ?>" style="text-align:center;">Преглед</a>
            <a class="btn" href="<?= APP_BASE ?>/product_delete.php?id=<?= (int)$p['id'] ?>" 
               style="text-align:center; color: #ff4d4d; border-color: rgba(255,77,77,0.3);"
               onclick="return confirm('Сигурни ли сте, че искате да изтриете тази оферта?');">Изтрий</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>