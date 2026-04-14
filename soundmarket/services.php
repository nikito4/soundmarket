<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$type = 'service';

$stmt = db()->prepare("
  SELECT p.*, u.username AS owner_username
  FROM products p
  JOIN users u ON u.id=p.owner_id
  WHERE p.type=?
  ORDER BY p.created_at DESC
");
$stmt->execute([$type]);
$items = $stmt->fetchAll();

$title = 'Услуги — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>
<div class="container" style="padding: 22px 0;">
  <div class="page-head" style="margin-bottom: 30px;">
    <h2>Услуги: Миксинг & Мастъринг</h2>
    <p class="muted">Професионални услуги от нашите артисти и инженери.</p>
  </div>

  <section class="grid">
    <?php foreach ($items as $p): ?>
      <article class="card">
        <?php 
          $userCover = !empty($p['cover_path']) ? APP_BASE . '/' . h($p['cover_path']) : null;
          $defaultCover = APP_BASE . '/uploads/blog-vinyl-lp.800x0.jpg';
          $finalCover = $userCover ?: $defaultCover;
        ?>
        <div class="card-img-container" style="background-image: url('<?= $finalCover ?>');">
           <?php if (!empty($p['file_path'])): ?>
            <div class="card-media-overlay">
                <audio controls controlsList="nodownload">
                  <source src="<?= APP_BASE . '/' . h($p['file_path']) ?>" type="audio/mpeg">
                </audio>
            </div>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <div class="card-title">
            <h3><?= h($p['title']) ?></h3>
            <small class="muted">от <?= h($p['owner_username']) ?></small>
          </div>

          <div class="meta">
            <span><?= h((string)($p['genre'] ?? 'Професионална услуга')) ?></span>
          </div>

          <div class="price-row">
            <strong style="color: var(--brand); font-size: 1.1rem;"><?= format_bgn((int)$p['price_bgn']) ?></strong>
            <span class="pill">Service</span>
          </div>

          <div class="actions">
            <a class="btn" href="<?= APP_BASE ?>/product.php?id=<?= (int)$p['id'] ?>" style="text-align:center;">Детайли</a>
            <form method="post" action="<?= APP_BASE ?>/cart.php">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <button class="btn primary" type="submit" style="width:100%;">Поръчай</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>