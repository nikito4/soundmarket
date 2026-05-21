<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

admin_require_auth();
$pdo = admin_db();

/* ── Metrics ── */
$total_users    = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_products = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$pending_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='created' OR status=''")->fetchColumn();
$total_revenue  = (int)($pdo->query("SELECT COALESCE(SUM(price_eur),0) FROM order_items")->fetchColumn());

/* ── Recent orders (last 10) ── */
$recent_orders = $pdo->query("
    SELECT o.id, o.total_eur, o.status, o.created_at, u.username,
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetchAll();

/* ── Top 5 products by order count ── */
$top_products = $pdo->query("
    SELECT p.id, p.title, p.type, p.price_eur,
           COUNT(oi.id) AS order_count
    FROM products p
    JOIN order_items oi ON oi.product_id = p.id
    GROUP BY p.id, p.title, p.type, p.price_eur
    ORDER BY order_count DESC
    LIMIT 5
")->fetchAll();

/* ── Chart: orders per month, last 6 months ── */
$chart_rows = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym,
           DATE_FORMAT(created_at, '%b %y')  AS label,
           COUNT(*)                           AS cnt
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym, label
    ORDER BY ym ASC
")->fetchAll();

/* Fill missing months so we always have 6 bars */
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $ym    = date('Y-m', strtotime("-$i month"));
    $label = date('M y',  strtotime("-$i month"));
    $months[$ym] = ['label' => $label, 'cnt' => 0];
}
foreach ($chart_rows as $r) {
    if (isset($months[$r['ym']])) $months[$r['ym']]['cnt'] = (int)$r['cnt'];
}
$chart = array_values($months);
$max_cnt = max(array_column($chart, 'cnt') ?: [1]);

$page_title = 'Dashboard';
$breadcrumb = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>

<!-- Metrics -->
<div class="page-header">
  <div>
    <h1>Dashboard</h1>
    <p>Добре дошъл, <?= h(admin_username()) ?>. Ето обобщение на платформата.</p>
  </div>
  <a href="/soundmarket/admin/orders.php?status=created" class="btn btn-primary">
    📦 Поръчки за обработка (<?= $pending_orders ?>)
  </a>
</div>

<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-icon blue">👥</div>
    <div>
      <div class="metric-label">Потребители</div>
      <div class="metric-value"><?= $total_users ?></div>
      <div class="metric-sub">Регистрирани акаунти</div>
    </div>
  </div>
  <div class="metric-card">
    <div class="metric-icon green">🎵</div>
    <div>
      <div class="metric-label">Продукти</div>
      <div class="metric-value"><?= $total_products ?></div>
      <div class="metric-sub">Активни обяви</div>
    </div>
  </div>
  <div class="metric-card">
    <div class="metric-icon amber">📦</div>
    <div>
      <div class="metric-label">За обработка</div>
      <div class="metric-value"><?= $pending_orders ?></div>
      <div class="metric-sub">Нови поръчки</div>
    </div>
  </div>
  <div class="metric-card">
    <div class="metric-icon purple">💰</div>
    <div>
      <div class="metric-label">Общ приход</div>
      <div class="metric-value" style="font-size:20px;"><?= format_eur((int)$total_revenue) ?></div>
      <div class="metric-sub">От всички поръчки</div>
    </div>
  </div>
</div>

<!-- Chart + Top products -->
<div class="grid-2 mb-20">

  <!-- Bar chart -->
  <div class="card">
    <div class="card-header">
      <h3>Поръчки по месеци</h3>
      <span class="text-muted" style="font-size:12px;">Последните 6 месеца</span>
    </div>
    <div class="card-body">
      <?php
        $bar_w   = 44;
        $bar_gap = 18;
        $svg_h   = 160;
        $pad_b   = 28; // space for labels
        $pad_t   = 12;
        $chart_h = $svg_h - $pad_b - $pad_t;
        $n       = count($chart);
        $svg_w   = $n * ($bar_w + $bar_gap) - $bar_gap + 20;
      ?>
      <div class="chart-wrap">
        <svg class="chart-svg" width="<?= $svg_w ?>" height="<?= $svg_h ?>"
             viewBox="0 0 <?= $svg_w ?> <?= $svg_h ?>"
             xmlns="http://www.w3.org/2000/svg">

          <!-- Horizontal grid lines -->
          <?php for ($g = 0; $g <= 4; $g++): ?>
            <?php $gy = $pad_t + $chart_h * (1 - $g / 4); ?>
            <line x1="0" y1="<?= round($gy) ?>"
                  x2="<?= $svg_w ?>" y2="<?= round($gy) ?>"
                  stroke="currentColor" stroke-opacity=".08" stroke-width="1"/>
          <?php endfor; ?>

          <?php foreach ($chart as $i => $m): ?>
            <?php
              $x        = $i * ($bar_w + $bar_gap) + 10;
              $fill_h   = $max_cnt > 0 ? (int)round($chart_h * $m['cnt'] / $max_cnt) : 0;
              $fill_h   = max($fill_h, $m['cnt'] > 0 ? 4 : 0);
              $bar_y    = $pad_t + $chart_h - $fill_h;
              $label_y  = $svg_h - 6;
              $val_y    = $bar_y - 5;
            ?>
            <!-- Bar -->
            <rect class="chart-bar" x="<?= $x ?>" y="<?= $bar_y ?>"
                  width="<?= $bar_w ?>" height="<?= $fill_h ?>"
                  rx="4" fill="#378ADD" fill-opacity=".85">
              <title><?= h($m['label']) ?>: <?= $m['cnt'] ?> поръчки</title>
            </rect>
            <!-- Value above bar -->
            <?php if ($m['cnt'] > 0): ?>
              <text x="<?= $x + $bar_w / 2 ?>" y="<?= $val_y ?>"
                    text-anchor="middle" font-size="11" font-weight="700"
                    fill="currentColor" opacity=".7"><?= $m['cnt'] ?></text>
            <?php endif; ?>
            <!-- Month label -->
            <text x="<?= $x + $bar_w / 2 ?>" y="<?= $label_y ?>"
                  text-anchor="middle" font-size="11"
                  fill="currentColor" opacity=".6"><?= h($m['label']) ?></text>
          <?php endforeach; ?>
        </svg>
      </div>
    </div>
  </div>

  <!-- Top products -->
  <div class="card">
    <div class="card-header">
      <h3>Топ продукти</h3>
      <span class="text-muted" style="font-size:12px;">По брой поръчки</span>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Продукт</th>
            <th>Тип</th>
            <th class="text-right">Поръчки</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$top_products): ?>
            <tr><td colspan="4" class="text-center text-muted" style="padding:30px;">Няма данни</td></tr>
          <?php else: ?>
            <?php foreach ($top_products as $i => $p): ?>
              <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td>
                  <a href="/soundmarket/product.php?id=<?= (int)$p['id'] ?>" target="_blank" class="fw-700">
                    <?= h($p['title']) ?>
                  </a><br>
                  <span class="text-muted" style="font-size:12px;"><?= format_eur((int)$p['price_eur']) ?></span>
                </td>
                <td><?= type_badge($p['type']) ?></td>
                <td class="text-right fw-700"><?= (int)$p['order_count'] ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Recent orders -->
<div class="card">
  <div class="card-header">
    <h3>Последни 10 поръчки</h3>
    <a href="/soundmarket/admin/orders.php" class="btn btn-sm">Виж всички</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Потребител</th>
          <th>Продукти</th>
          <th>Сума</th>
          <th>Статус</th>
          <th>Дата</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$recent_orders): ?>
          <tr><td colspan="6" class="empty-state">Няма поръчки</td></tr>
        <?php else: ?>
          <?php foreach ($recent_orders as $o): ?>
            <tr>
              <td><a href="/soundmarket/admin/orders.php?id=<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?></a></td>
              <td><?= h($o['username']) ?></td>
              <td class="text-muted"><?= (int)$o['item_count'] ?> бр.</td>
              <td class="fw-700"><?= format_eur((int)$o['total_eur']) ?></td>
              <td><?= status_badge((string)$o['status']) ?></td>
              <td class="text-muted"><?= h(date('d.m.Y H:i', strtotime($o['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
