<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

admin_require_auth();
$pdo = admin_db();

$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 40;

$total = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$pg    = paginate($total, $page, $per_page);

$stmt = $pdo->prepare("
    SELECT l.id, l.action, l.target_table, l.target_id,
           l.ip_address, l.created_at,
           a.username AS admin_username
    FROM activity_logs l
    LEFT JOIN admin_users a ON a.id = l.admin_id
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$pg['per_page'], $pg['offset']]);
$logs = $stmt->fetchAll();

$page_title = 'Логове';
$breadcrumb = 'Логове';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Логове на активността</h1>
    <p>Всички действия в административния панел. Общо: <?= $total ?> записа.</p>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Администратор</th>
          <th>Действие</th>
          <th>Таблица</th>
          <th>ID обект</th>
          <th>IP адрес</th>
          <th>Дата и час</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$logs): ?>
          <tr><td colspan="7">
            <div class="empty-state"><div class="es-icon">📋</div><p>Няма логове.</p></div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td class="text-muted"><?= (int)$log['id'] ?></td>
              <td class="fw-700"><?= $log['admin_username'] ? h($log['admin_username']) : '<span class="text-muted">—</span>' ?></td>
              <td><?= h($log['action']) ?></td>
              <td><?= $log['target_table'] ? '<span class="badge badge-gray">' . h($log['target_table']) . '</span>' : '—' ?></td>
              <td class="text-muted"><?= $log['target_id'] ? '#' . (int)$log['target_id'] : '—' ?></td>
              <td class="text-muted" style="font-family:monospace;font-size:12px;"><?= $log['ip_address'] ? h($log['ip_address']) : '—' ?></td>
              <td class="text-muted"><?= h(date('d.m.Y H:i:s', strtotime($log['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= pagination_links($pg['total_pages'], $pg['page'], 'logs.php?page=%d') ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
