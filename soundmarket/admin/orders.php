<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

admin_require_auth();
$pdo = admin_db();

/* ── Change status ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    if (!verify_csrf()) { die('Invalid CSRF token.'); }
    $order_id  = (int)$_POST['change_status'];
    $allowed   = ['created', 'paid', 'delivered', 'cancelled'];
    $new_status = (string)($_POST['new_status'] ?? '');
    if ($order_id > 0 && in_array($new_status, $allowed, true)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$new_status, $order_id]);
        log_action($pdo, admin_id(), "Статус поръчка #$order_id → $new_status", 'orders', $order_id);

        // Notify buyer of status change
        $buyer_stmt = $pdo->prepare("SELECT user_id FROM orders WHERE id = ?");
        $buyer_stmt->execute([$order_id]);
        $buyer_id = (int)$buyer_stmt->fetchColumn();
        if ($buyer_id) {
            $status_labels = [
                'paid'      => 'платена',
                'delivered' => 'доставена',
                'cancelled' => 'отказана',
                'created'   => 'изчакваща',
            ];
            $label = $status_labels[$new_status] ?? $new_status;
            notify($pdo, $buyer_id, 'order',
                "Поръчка #$order_id е обновена: $label",
                '/soundmarket/dashboard.php'
            );
        }
    }
    $back = 'orders.php?status=' . urlencode($_POST['status_filter'] ?? '') . '&page=' . (int)($_POST['current_page'] ?? 1);
    header('Location: ' . $back);
    exit;
}

/* ── Filters & pagination ── */
$status_filter = trim((string)($_GET['status'] ?? ''));
$allowed_s = ['created', 'paid', 'delivered', 'cancelled'];
if ($status_filter && !in_array($status_filter, $allowed_s, true)) $status_filter = '';

$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;

/* scroll to order if ?id= supplied */
$focus_id = (int)($_GET['id'] ?? 0);

$where  = $status_filter ? "WHERE o.status = ?" : "";
$params = $status_filter ? [$status_filter] : [];

$cnt_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o $where");
$cnt_stmt->execute($params);
$total = (int)$cnt_stmt->fetchColumn();

$pg     = paginate($total, $page, $per_page);
$offset = $pg['offset'];

$list_stmt = $pdo->prepare("
    SELECT o.id, o.total_eur, o.status, o.created_at, o.stripe_session_id, u.username,
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
    FROM orders o
    JOIN users u ON u.id = o.user_id
    $where
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
");
$list_stmt->execute(array_merge($params, [$per_page, $offset]));
$orders = $list_stmt->fetchAll();

/* ── Pre-load order items for expand rows ── */
$order_items_map = [];
if ($orders) {
    $oids  = array_column($orders, 'id');
    $in    = implode(',', array_fill(0, count($oids), '?'));
    $istmt = $pdo->prepare("
        SELECT oi.order_id, oi.quantity, oi.price_eur, p.title, p.type
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id IN ($in)
        ORDER BY oi.id
    ");
    $istmt->execute($oids);
    foreach ($istmt->fetchAll() as $item) {
        $order_items_map[$item['order_id']][] = $item;
    }
}

$page_title = 'Поръчки';
$breadcrumb = 'Поръчки';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Поръчки</h1>
    <p>Общо: <?= $total ?></p>
  </div>
</div>

<!-- Status filter -->
<div class="filters-bar mb-16">
  <?php
    $statuses = ['' => 'Всички', 'created' => 'Изчакване', 'paid' => 'Платени', 'delivered' => 'Доставени', 'cancelled' => 'Отказани'];
    foreach ($statuses as $val => $label):
      $active = ($status_filter === $val) ? ' btn-primary' : '';
  ?>
    <a href="orders.php?status=<?= urlencode($val) ?>" class="btn btn-sm<?= $active ?>">
      <?= h($label) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table" id="orders-table">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Купувач</th>
          <th>Продукти</th>
          <th>Сума</th>
          <th>Статус</th>
          <th>Stripe Session</th>
          <th>Дата</th>
          <th>Промени статус</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$orders): ?>
          <tr><td colspan="9">
            <div class="empty-state"><div class="es-icon">📦</div><p>Няма намерени поръчки.</p></div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($orders as $o): ?>
            <?php $oid = (int)$o['id']; ?>
            <!-- Main row -->
            <tr class="<?= $focus_id === $oid ? 'focused-row' : '' ?>" id="row-<?= $oid ?>">
              <td>
                <button class="btn btn-xs" data-expand="<?= $oid ?>">
                  <span class="expand-icon">▼</span>
                </button>
              </td>
              <td class="fw-700">#<?= $oid ?></td>
              <td><?= h($o['username']) ?></td>
              <td class="text-muted"><?= (int)$o['item_count'] ?> бр.</td>
              <td class="fw-700"><?= format_eur((int)$o['total_eur']) ?></td>
              <td><?= status_badge((string)$o['status']) ?></td>
              <td class="text-muted" style="font-size:11px; font-family:monospace; max-width:130px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <?php if (!empty($o['stripe_session_id'])): ?>
                  <span title="<?= h($o['stripe_session_id']) ?>"
                        style="cursor:pointer;"
                        onclick="navigator.clipboard.writeText('<?= h($o['stripe_session_id']) ?>')"><?= h(substr($o['stripe_session_id'], 0, 18)) ?>…</span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td class="text-muted"><?= h(date('d.m.Y H:i', strtotime($o['created_at']))) ?></td>
              <td>
                <form method="post" style="margin:0;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="change_status" value="<?= $oid ?>">
                  <input type="hidden" name="status_filter" value="<?= h($status_filter) ?>">
                  <input type="hidden" name="current_page" value="<?= $pg['page'] ?>">
                  <select name="new_status" class="status-select"
                          style="padding:5px 8px;font-size:12px;border-radius:6px;border:1px solid var(--border);background:var(--surface);color:var(--text);">
                    <option value="created"   <?= $o['status'] === 'created'   ? 'selected' : '' ?>>Изчакване</option>
                    <option value="paid"      <?= $o['status'] === 'paid'      ? 'selected' : '' ?>>Платено</option>
                    <option value="delivered" <?= $o['status'] === 'delivered' ? 'selected' : '' ?>>Доставено</option>
                    <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Отказано</option>
                  </select>
                </form>
              </td>
            </tr>
            <!-- Expand row -->
            <tr class="expand-row <?= $focus_id === $oid ? 'open' : '' ?>" id="expand-<?= $oid ?>">
              <td class="expand-cell" colspan="9">
                <div class="expand-inner">
                  <?php if (empty($order_items_map[$oid])): ?>
                    <p class="text-muted" style="font-size:13px;">Няма продукти в тази поръчка.</p>
                  <?php else: ?>
                    <table>
                      <thead>
                        <tr>
                          <td style="font-weight:700;color:var(--text-muted);font-size:11px;text-transform:uppercase;padding-bottom:6px;">Продукт</td>
                          <td style="font-weight:700;color:var(--text-muted);font-size:11px;text-transform:uppercase;padding-bottom:6px;">Тип</td>
                          <td style="font-weight:700;color:var(--text-muted);font-size:11px;text-transform:uppercase;padding-bottom:6px;">Бр.</td>
                          <td style="font-weight:700;color:var(--text-muted);font-size:11px;text-transform:uppercase;padding-bottom:6px;text-align:right;">Цена</td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($order_items_map[$oid] as $item): ?>
                          <tr>
                            <td><?= h($item['title']) ?></td>
                            <td><?= type_badge($item['type']) ?></td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td style="text-align:right;font-weight:700;"><?= format_eur((int)$item['price_eur']) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php endif; ?>
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
    'orders.php?status=' . urlencode($status_filter) . '&page=%d') ?>

<?php if ($focus_id): ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const row = document.getElementById('row-<?= $focus_id ?>');
    if (row) row.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
</script>
<?php endif; ?>

<style>
.focused-row td { background: var(--accent-bg) !important; }
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
