<?php
declare(strict_types=1);
$_active     = basename($_SERVER['PHP_SELF']);
$_pending    = (int)admin_db()->query(
    "SELECT COUNT(*) FROM orders WHERE status='created' OR status=''"
)->fetchColumn();

function _nav(string $file, string $icon, string $label, string $active, ?int $badge = null): void {
    $cls = $active === $file ? ' active' : '';
    echo '<a href="/soundmarket/admin/' . h($file) . '" class="nav-item' . $cls . '">';
    echo '<span class="nav-icon">' . $icon . '</span>';
    echo '<span class="nav-label">' . h($label) . '</span>';
    if ($badge !== null && $badge > 0) {
        echo '<span class="nav-badge">' . (int)$badge . '</span>';
    }
    echo '</a>';
}
?>
<aside class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <a href="/soundmarket/admin/dashboard.php" class="sidebar-logo">
      <span class="logo-mark">SM</span>
      <span class="logo-text">SoundMarket<br><em>Admin</em></span>
    </a>
    <button class="sidebar-close" id="sidebarClose" aria-label="Затвори">✕</button>
  </div>

  <nav class="sidebar-nav">
    <p class="nav-group-label">Основно</p>
    <?php _nav('dashboard.php', '◈', 'Dashboard',   $_active); ?>
    <?php _nav('users.php',    '⊛', 'Потребители', $_active); ?>
    <?php _nav('products.php', '♫', 'Продукти',    $_active); ?>
    <?php _nav('orders.php',   '▤', 'Поръчки',     $_active, $_pending); ?>

    <p class="nav-group-label" style="margin-top:18px;">Система</p>
    <?php _nav('logs.php',     '≡', 'Логове',     $_active); ?>
    <?php _nav('settings.php', '⚙', 'Настройки',  $_active); ?>
  </nav>

  <div class="sidebar-footer">
    <div class="sf-info">
      <span class="sf-avatar"><?= h(strtoupper(substr(admin_username(), 0, 1))) ?></span>
      <div>
        <div class="sf-name"><?= h(admin_username()) ?></div>
        <div class="sf-role"><?= h(ucfirst(admin_role())) ?></div>
      </div>
    </div>
    <a href="/soundmarket/admin/logout.php" class="sf-logout" title="Изход">⏻</a>
  </div>

</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
