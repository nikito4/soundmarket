<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

admin_require_auth();
$pdo = admin_db();

/* ── Inline price edit ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_price'])) {
    if (!verify_csrf()) { die('Invalid CSRF token.'); }
    $prod_id   = (int)$_POST['edit_price'];
    $new_price = (float)str_replace(',', '.', $_POST['new_price'] ?? '0');
    if ($prod_id > 0 && $new_price >= 0) {
        $cents = (int)round($new_price * 100);
        $pdo->prepare("UPDATE products SET price_eur = ? WHERE id = ?")->execute([$cents, $prod_id]);
        log_action($pdo, admin_id(), "Редактирана цена на продукт #$prod_id → €{$new_price}", 'products', $prod_id);
    }
    header('Location: products.php?type=' . urlencode($_POST['type_filter'] ?? '') . '&page=' . (int)($_POST['current_page'] ?? 1));
    exit;
}

/* ── Delete product ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    if (!verify_csrf()) { die('Invalid CSRF token.'); }
    $del_id = (int)$_POST['delete_product'];
    if ($del_id > 0) {
        // Fetch file paths to delete physical files
        $f = $pdo->prepare("SELECT file_path, cover_path FROM products WHERE id = ?");
        $f->execute([$del_id]);
        $fp = $f->fetch();
        if ($fp) {
            foreach (['file_path', 'cover_path'] as $col) {
                if (!empty($fp[$col])) {
                    $full = dirname(__DIR__) . '/' . $fp[$col];
                    if (file_exists($full)) @unlink($full);
                }
            }
        }
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$del_id]);
        log_action($pdo, admin_id(), "Изтрит продукт #$del_id", 'products', $del_id);
    }
    header('Location: products.php?type=' . urlencode($_POST['type_filter'] ?? ''));
    exit;
}

/* ── Filters & pagination ── */
$type_filter = trim((string)($_GET['type'] ?? ''));
$page        = max(1, (int)($_GET['page'] ?? 1));
$per_page    = 20;
$allowed_types = ['beat', 'music', 'service', 'digital'];
if ($type_filter && !in_array($type_filter, $allowed_types, true)) $type_filter = '';

$where  = $type_filter ? "WHERE p.type = ?" : "";
$params = $type_filter ? [$type_filter] : [];

$cnt_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where");
$cnt_stmt->execute($params);
$total = (int)$cnt_stmt->fetchColumn();

$pg     = paginate($total, $page, $per_page);
$offset = $pg['offset'];

$list_params   = array_merge($params, [$per_page, $offset]);
$list_stmt     = $pdo->prepare("
    SELECT p.id, p.title, p.type, p.price_eur, p.cover_path, p.created_at,
           u.username AS owner
    FROM products p
    JOIN users u ON u.id = p.owner_id
    $where
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$list_stmt->execute($list_params);
$products = $list_stmt->fetchAll();

$page_title = 'Продукти';
$breadcrumb = 'Продукти';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Продукти</h1>
    <p>Общо: <?= $total ?> продукта<?= $type_filter ? " · филтър: <strong>$type_filter</strong>" : '' ?></p>
  </div>
</div>

<!-- Type filter -->
<div class="filters-bar mb-16">
  <?php
    $types = ['' => 'Всички', 'beat' => 'Beat', 'music' => 'Music', 'service' => 'Service', 'digital' => 'Digital'];
    foreach ($types as $val => $label):
      $active = ($type_filter === $val) ? ' btn-primary' : '';
  ?>
    <a href="products.php?type=<?= urlencode($val) ?>" class="btn btn-sm<?= $active ?>"><?= h($label) ?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Корица</th>
          <th>Заглавие</th>
          <th>Тип</th>
          <th>Собственик</th>
          <th>Цена</th>
          <th>Дата</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$products): ?>
          <tr><td colspan="7">
            <div class="empty-state"><div class="es-icon">🎵</div><p>Няма продукти.</p></div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <?php
              $cover = !empty($p['cover_path'])
                ? '/soundmarket/' . h($p['cover_path'])
                : 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42"><rect width="42" height="42" fill="%23334155"/><text x="21" y="27" text-anchor="middle" font-size="16" fill="%2394a3b8">♫</text></svg>';
            ?>
            <tr>
              <td>
                <img src="<?= $cover ?>" alt="" class="thumb">
              </td>
              <td>
                <a href="/soundmarket/product.php?id=<?= (int)$p['id'] ?>" target="_blank" class="fw-700">
                  <?= h($p['title']) ?>
                </a>
              </td>
              <td><?= type_badge($p['type']) ?></td>
              <td class="text-muted"><?= h($p['owner']) ?></td>
              <td>
                <div class="price-wrap">
                  <span class="price-display" title="Кликни за редакция">
                    <?= format_eur((int)$p['price_eur']) ?>
                  </span>
                  <form method="post" class="price-edit-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="edit_price" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="type_filter" value="<?= h($type_filter) ?>">
                    <input type="hidden" name="current_page" value="<?= $pg['page'] ?>">
                    <input type="number" name="new_price" step="0.01" min="0"
                           value="<?= number_format($p['price_eur'] / 100, 2, '.', '') ?>"
                           style="width:90px;">
                    <button type="submit" class="btn btn-primary btn-xs">✓</button>
                    <button type="button" class="btn btn-xs price-cancel">✕</button>
                  </form>
                </div>
              </td>
              <td class="text-muted"><?= h(date('d.m.Y', strtotime($p['created_at']))) ?></td>
              <td>
                <form method="post" style="margin:0;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="delete_product" value="<?= (int)$p['id'] ?>">
                  <input type="hidden" name="type_filter" value="<?= h($type_filter) ?>">
                  <button type="submit" class="btn btn-sm btn-danger"
                          data-confirm="Изтрий продукт «<?= h($p['title']) ?>»?">
                    🗑 Изтрий
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= pagination_links($pg['total_pages'], $pg['page'],
    'products.php?type=' . urlencode($type_filter) . '&page=%d') ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
