<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$pdo  = db();
$type = 'service';

// ── GET params ────────────────────────────────────────────────────────────────
$q         = trim((string)($_GET['q']         ?? ''));
$sort      = (string)($_GET['sort']      ?? '');
$min_price = (float)($_GET['min_price']  ?? 0);
$max_price = (float)($_GET['max_price']  ?? 0);
$genre     = trim((string)($_GET['genre']     ?? ''));
$page      = max(1, (int)($_GET['page']  ?? 1));
$per_page  = 12;

$has_filters = $q !== '' || $sort !== '' || $min_price > 0 || $max_price > 0 || $genre !== '';

$sort_map = [
    'oldest'     => 'p.created_at ASC',
    'price_asc'  => 'p.price_eur ASC',
    'price_desc' => 'p.price_eur DESC',
    'rating'     => 'p.avg_rating DESC',
];
$order = $sort_map[$sort] ?? 'p.created_at DESC';

$conditions = ['p.type = ?'];
$params     = [$type];

if ($q !== '') {
    $like = '%' . $q . '%';
    $conditions[] = '(p.title LIKE ? OR p.genre LIKE ? OR u.username LIKE ?)';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($min_price > 0) { $conditions[] = 'p.price_eur >= ?'; $params[] = (int)($min_price * 100); }
if ($max_price > 0) { $conditions[] = 'p.price_eur <= ?'; $params[] = (int)($max_price * 100); }
if ($genre !== '')  { $conditions[] = 'p.genre LIKE ?';   $params[] = '%' . $genre . '%'; }

$where = implode(' AND ', $conditions);

$cnt = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN users u ON u.id=p.owner_id WHERE $where");
$cnt->execute($params);
$total_count = (int)$cnt->fetchColumn();

$offset = ($page - 1) * $per_page;
$stmt = $pdo->prepare("
    SELECT p.*, u.username AS owner_username
    FROM products p
    JOIN users u ON u.id = p.owner_id
    WHERE $where
    ORDER BY $order
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$items = $stmt->fetchAll();

$wishlisted_ids = [];
if (is_logged_in()) {
    $ws = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $ws->execute([current_user_id()]);
    $wishlisted_ids = $ws->fetchAll(\PDO::FETCH_COLUMN);
}

$base_params = $_GET;
unset($base_params['page']);
$base_url = 'services.php' . ($base_params ? '?' . http_build_query($base_params) : '');

$title = 'Услуги — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>
<div class="container page-fade" style="padding: 22px 0;">
  <div class="page-head" style="margin-bottom: 20px;">
    <h2>Услуги: Миксинг &amp; Мастъринг</h2>
    <p class="muted">Професионални услуги от нашите артисти и инженери.</p>
  </div>

  <!-- Filter bar -->
  <form method="get" class="filter-bar">
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Търси по заглавие, жанр, артист…" style="flex: 1; min-width: 160px;">

    <select name="sort">
      <option value="" <?= $sort === '' ? 'selected' : '' ?>>Най-нови</option>
      <option value="oldest"     <?= $sort === 'oldest'     ? 'selected' : '' ?>>Най-стари</option>
      <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Цена ↑</option>
      <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена ↓</option>
      <option value="rating"     <?= $sort === 'rating'     ? 'selected' : '' ?>>Най-оценявани</option>
    </select>

    <input type="number" name="min_price" placeholder="Мин. €" min="0" step="0.01"
           value="<?= $min_price > 0 ? h((string)$min_price) : '' ?>">
    <input type="number" name="max_price" placeholder="Макс. €" min="0" step="0.01"
           value="<?= $max_price > 0 ? h((string)$max_price) : '' ?>">

    <button class="btn primary" type="submit">Филтрирай</button>
    <?php if ($has_filters): ?>
      <a href="services.php" class="btn">Изчисти ×</a>
    <?php endif; ?>
  </form>

  <p class="muted" style="margin-bottom: 16px;">
    Намерени <strong><?= $total_count ?></strong> продукта
  </p>

  <section class="grid">
    <?php foreach ($items as $p): ?>
      <?php
        $userCover  = !empty($p['cover_path']) ? APP_BASE . '/' . h($p['cover_path']) : null;
        $finalCover = $userCover ?: APP_BASE . '/uploads/blog-vinyl-lp.800x0.jpg';
        $is_wishlisted = in_array((int)$p['id'], $wishlisted_ids, true);
      ?>
      <article class="card">
        <div class="card-img-container" style="background-image: url('<?= $finalCover ?>'); position: relative;">
          <?php if (!empty($p['file_path'])): ?>
            <div class="card-media-overlay">
              <div class="custom-player" data-src="<?= APP_BASE . '/' . h($p['file_path']) ?>">
                <button class="cp-play" aria-label="Пусни">
                  <svg class="icon-play" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>
                  <svg class="icon-pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                </button>
                <div class="cp-bar"><div class="cp-fill"></div></div>
                <span class="cp-time">0:00 / 0:00</span>
                <button class="cp-mute" aria-label="Заглуши">
                  <svg class="icon-sound" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                  <svg class="icon-mute" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><polygon points="11,5 6,9 2,9 2,15 6,15 11,19"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
                </button>
              </div>
            </div>
          <?php endif; ?>
          <button class="wish-btn<?= $is_wishlisted ? ' active' : '' ?>"
                  data-id="<?= (int)$p['id'] ?>"
                  data-active="<?= $is_wishlisted ? '1' : '0' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </button>
        </div>

        <div class="card-body">
          <div class="card-title">
            <h3><?= h($p['title']) ?></h3>
            <small class="muted">от <?= h($p['owner_username']) ?></small>
          </div>

          <div class="meta">
            <span><?= h((string)($p['genre'] ?? 'Професионална услуга')) ?></span>
          </div>

          <?php if ((int)($p['review_count'] ?? 0) > 0): ?>
            <div style="margin: 4px 0;">
              <span class="stars"><?= str_repeat('★', (int)round((float)$p['avg_rating'])) ?><span class="stars-empty"><?= str_repeat('★', 5 - (int)round((float)$p['avg_rating'])) ?></span></span>
              <span style="font-size: 12px; color: var(--muted);">(<?= (int)$p['review_count'] ?>)</span>
            </div>
          <?php else: ?>
            <div style="margin: 4px 0; font-size: 12px; color: var(--muted);">Няма отзиви</div>
          <?php endif; ?>

          <div class="price-row">
            <span class="price-pill"><?= format_eur((int)$p['price_eur']) ?></span>
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

    <?php if (!$items): ?>
      <div class="empty-state">
        <div class="es-icon">🎚️</div>
        <h3>Няма предложени услуги</h3>
        <p>Предложи своите умения — миксинг, мастъринг и др.</p>
        <a href="<?= APP_BASE ?>/product_create.php" class="btn primary">Предложи услуга</a>
      </div>
    <?php endif; ?>
  </section>

  <?= render_pagination($total_count, $per_page, $page, $base_url) ?>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
