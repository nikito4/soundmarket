<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

admin_require_auth();
$pdo = admin_db();

/* ── Delete user ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!verify_csrf()) { die('Invalid CSRF token.'); }
    $del_id = (int)$_POST['delete_user'];
    if ($del_id > 0) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
        log_action($pdo, admin_id(), "Изтрит потребител #$del_id", 'users', $del_id);
    }
    header('Location: users.php');
    exit;
}

/* ── Search & pagination ── */
$search  = trim((string)($_GET['q']   ?? ''));
$page    = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;

$like = '%' . $search . '%';

$total = (int)$pdo->prepare(
    "SELECT COUNT(*) FROM users WHERE username LIKE ? OR email LIKE ?"
)->execute([$like, $like]) ? $pdo->query(
    "SELECT COUNT(*) FROM users WHERE username LIKE " . $pdo->quote($like) .
    " OR email LIKE " . $pdo->quote($like)
)->fetchColumn() : 0;

// Re-query properly
$cnt_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username LIKE ? OR email LIKE ?");
$cnt_stmt->execute([$like, $like]);
$total = (int)$cnt_stmt->fetchColumn();

$pg     = paginate($total, $page, $per_page);
$offset = $pg['offset'];

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, u.created_at,
           (SELECT COUNT(*) FROM products p WHERE p.owner_id = u.id) AS product_count,
           (SELECT COUNT(*) FROM orders   o WHERE o.user_id   = u.id) AS order_count
    FROM users u
    WHERE u.username LIKE ? OR u.email LIKE ?
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$like, $like, $per_page, $offset]);
$users = $stmt->fetchAll();

/* ── Preload products for view modals ── */
$user_products = [];
if ($users) {
    $ids   = array_column($users, 'id');
    $in    = implode(',', array_fill(0, count($ids), '?'));
    $pstmt = $pdo->prepare(
        "SELECT id, owner_id, title, type, price_eur FROM products WHERE owner_id IN ($in) ORDER BY created_at DESC"
    );
    $pstmt->execute($ids);
    foreach ($pstmt->fetchAll() as $prod) {
        $user_products[$prod['owner_id']][] = $prod;
    }
}

$page_title = 'Потребители';
$breadcrumb = 'Потребители';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Потребители</h1>
    <p>Общо: <?= $total ?> регистрирани акаунта</p>
  </div>
</div>

<!-- Search -->
<form method="get" class="filters-bar mb-16">
  <div class="search-box">
    <input name="q" type="text" value="<?= h($search) ?>"
           placeholder="Търси по потребителско име или email…">
  </div>
  <button type="submit" class="btn btn-primary btn-sm">Търси</button>
  <?php if ($search): ?>
    <a href="users.php" class="btn btn-sm">✕ Изчисти</a>
  <?php endif; ?>
</form>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Потребител</th>
          <th>Email</th>
          <th>Продукти</th>
          <th>Поръчки</th>
          <th>Регистрация</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$users): ?>
          <tr><td colspan="7">
            <div class="empty-state"><div class="es-icon">👥</div><p>Няма намерени потребители.</p></div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td class="text-muted"><?= (int)$u['id'] ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <span style="width:32px;height:32px;border-radius:50%;background:var(--accent);color:#fff;display:grid;place-items:center;font-weight:700;font-size:13px;flex-shrink:0;">
                    <?= h(strtoupper(substr($u['username'], 0, 1))) ?>
                  </span>
                  <span class="fw-700"><?= h($u['username']) ?></span>
                </div>
              </td>
              <td class="text-muted"><?= h($u['email']) ?></td>
              <td><?= (int)$u['product_count'] ?></td>
              <td><?= (int)$u['order_count'] ?></td>
              <td class="text-muted"><?= h(date('d.m.Y', strtotime($u['created_at']))) ?></td>
              <td>
                <div style="display:flex;gap:6px;">
                  <button class="btn btn-sm" data-modal-open="<?= (int)$u['id'] ?>">
                    👁 Преглед
                  </button>
                  <form method="post" style="margin:0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="delete_user" value="<?= (int)$u['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger"
                            data-confirm="Изтрий потребител «<?= h($u['username']) ?>»? Всички негови продукти и поръчки ще бъдат изтрити!">
                      🗑 Изтрий
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= pagination_links($pg['total_pages'], $pg['page'],
    'users.php?q=' . urlencode($search) . '&page=%d') ?>

<!-- Modals -->
<?php foreach ($users as $u): ?>
  <div class="modal-backdrop" id="modal-<?= (int)$u['id'] ?>">
    <div class="modal">
      <div class="modal-header">
        <h3>Потребител: <?= h($u['username']) ?></h3>
        <button class="modal-close" data-modal-close>✕</button>
      </div>
      <div class="modal-body">
        <div class="grid-2" style="gap:12px;margin-bottom:18px;">
          <div><span class="text-muted" style="font-size:12px;">ID</span><br><strong>#<?= (int)$u['id'] ?></strong></div>
          <div><span class="text-muted" style="font-size:12px;">Email</span><br><strong><?= h($u['email']) ?></strong></div>
          <div><span class="text-muted" style="font-size:12px;">Регистрация</span><br><strong><?= h(date('d.m.Y H:i', strtotime($u['created_at']))) ?></strong></div>
          <div><span class="text-muted" style="font-size:12px;">Поръчки</span><br><strong><?= (int)$u['order_count'] ?></strong></div>
        </div>

        <h4 style="font-size:13px;font-weight:700;margin-bottom:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
          Продукти (<?= count($user_products[$u['id']] ?? []) ?>)
        </h4>
        <?php if (empty($user_products[$u['id']])): ?>
          <p class="text-muted" style="font-size:13px;">Няма публикувани продукти.</p>
        <?php else: ?>
          <table class="data-table" style="font-size:13px;">
            <thead>
              <tr><th>Заглавие</th><th>Тип</th><th class="text-right">Цена</th></tr>
            </thead>
            <tbody>
              <?php foreach ($user_products[$u['id']] as $pr): ?>
                <tr>
                  <td><a href="/soundmarket/product.php?id=<?= (int)$pr['id'] ?>" target="_blank"><?= h($pr['title']) ?></a></td>
                  <td><?= type_badge($pr['type']) ?></td>
                  <td class="text-right fw-700"><?= format_eur((int)$pr['price_eur']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
