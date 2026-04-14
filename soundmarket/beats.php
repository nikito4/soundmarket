<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$type = 'beat';

$stmt = db()->prepare("
  SELECT p.*, u.username AS owner_username
  FROM products p
  JOIN users u ON u.id=p.owner_id
  WHERE p.type=?
  ORDER BY p.created_at DESC
");
$stmt->execute([$type]);
$items = $stmt->fetchAll();

$title = 'Бийтове — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>
<div class="container" style="padding: 22px 0;">
  <div class="page-head">
    <h2>Каталог бийтове</h2>
    <p>Продукти, качени от потребители (артисти/продуценти).</p>
  </div>

  <section class="grid">
    <?php foreach ($items as $p): ?>
      <article class="card">
    <?php 
    // Път до качената снимка от базата данни
    $userCover = !empty($p['cover_path']) ? APP_BASE . '/' . h($p['cover_path']) : null;
    
    // Път до твоята нова снимка по подразбиране
    $defaultCover = APP_BASE . '/uploads/blog-vinyl-lp.800x0.jpg';
    
    // Финален избор на снимка
    $finalCover = $userCover ?: $defaultCover;
    ?>

    <div class="card-img-container" style="background-image: url('<?= $finalCover ?>');">
        <div class="card-media-overlay">
            <?php if (!empty($p['file_path'])): ?>
                <audio controls controlsList="nodownload">
                    <source src="<?= APP_BASE . '/' . h($p['file_path']) ?>" type="audio/mpeg">
                </audio>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-body">
        <div class="card-title">
            <h3><?= h($p['title']) ?></h3>
            <small class="muted">от <?= h($p['owner_username']) ?></small>
        </div>

        <div class="meta" style="font-size: 14px; color: var(--muted);">
            <span>#<?= h((string)($p['genre'] ?? 'General')) ?></span>
            <?php if($p['bpm']): ?> <span>• <?= (int)$p['bpm'] ?> BPM</span> <?php endif; ?>
        </div>

        <div class="price-row">
            <strong style="font-size: 1.1rem; color: var(--brand);"><?= format_bgn((int)$p['price_bgn']) ?></strong>
            <span class="pill"><?= h($p['type']) ?></span>
        </div>

        <div class="actions">
            <a class="btn" href="<?= APP_BASE ?>/product.php?id=<?= (int)$p['id'] ?>">Детайли</a>
            
            <form method="post" action="<?= APP_BASE ?>/cart.php" style="margin:0;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <button class="btn primary" type="submit" style="width: 100%;">Купи</button>
            </form>
        </div>
    </div>
</article>
    <?php endforeach; ?>

    <?php if (!$items): ?>
      <p style="color:#475467;">Няма качени бийтове още. Влез и качи първия.</p>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>