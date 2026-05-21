<?php
// inc/header.php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$title = $title ?? APP_NAME;

// Active nav detection
$current_page = basename($_SERVER['PHP_SELF']);

$cartCount = 0;
$notif_count = 0;
$wishlist_count = 0;
if (is_logged_in()) {
    $nav_uid_tmp = current_user_id();
    $stmt = db()->prepare("SELECT COALESCE(SUM(quantity),0) AS c FROM cart_items WHERE user_id=?");
    $stmt->execute([$nav_uid_tmp]);
    $cartCount = (int)$stmt->fetch()['c'];

    $ns = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $ns->execute([$nav_uid_tmp]);
    $notif_count = (int)$ns->fetchColumn();

    $ws = db()->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id=?");
    $ws->execute([$nav_uid_tmp]);
    $wishlist_count = (int)$ws->fetchColumn();
}
?>
<!doctype html>
<html lang="bg">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="SoundMarket is a marketplace for independent musicians to buy and sell beats, music tracks and audio services." />
  <meta name="keywords" content="beats, music marketplace, buy beats, sell beats, audio services, mixing, mastering" />
  <title><?= h($title) ?> — SoundMarket</title>
  <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/style.css?v=10">
  <script>const APP_BASE = '<?= APP_BASE ?>';</script>
</head>
<body>

<header class="site-header">
  <div class="container header-inner">

    <a href="<?= APP_BASE ?>/index.php" class="logo">
      SOUND<span>MARKET</span>
    </a>

    <nav class="nav">
      <a href="<?= APP_BASE ?>/beats.php"    <?= $current_page === 'beats.php'    ? 'class="active"' : '' ?>>Бийтове</a>
      <a href="<?= APP_BASE ?>/music.php"    <?= $current_page === 'music.php'    ? 'class="active"' : '' ?>>Музика</a>
      <a href="<?= APP_BASE ?>/services.php" <?= $current_page === 'services.php' ? 'class="active"' : '' ?>>Услуги</a>
    </nav>

    <div class="header-actions">
      <a class="icon-btn" href="<?= APP_BASE ?>/cart.php">
        🛒 <span class="cart-count"><?= $cartCount ?></span>
      </a>

      <?php if (is_logged_in()):
          $nav_uid    = current_user_id();
          $nav_stmt   = db()->prepare("SELECT avatar_path, username FROM users WHERE id = ?");
          $nav_stmt->execute([$nav_uid]);
          $nav_user   = $nav_stmt->fetch();
          $nav_colors = ['#7c3aed','#0891b2','#db2777','#059669','#d97706','#dc2626'];
          $nav_color  = $nav_colors[$nav_uid % 6];
          $nav_letter = mb_strtoupper(mb_substr($nav_user['username'] ?? '', 0, 1));
      ?>

        <!-- Wishlist icon -->
        <a href="<?= APP_BASE ?>/wishlist.php"
           class="nav-wishlist"
           style="position: relative; display: inline-flex; align-items: center;
                  color: var(--muted); text-decoration: none; padding: 4px;"
           title="Любими">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
          <?php if ($wishlist_count > 0): ?>
            <span style="position: absolute; top: -4px; right: -4px;
                         background: #f43f5e; color: #fff; font-size: 10px;
                         font-weight: 700; border-radius: 50%;
                         width: 16px; height: 16px;
                         display: flex; align-items: center; justify-content: center;
                         line-height: 1;">
              <?= min($wishlist_count, 9) ?><?= $wishlist_count > 9 ? '+' : '' ?>
            </span>
          <?php endif; ?>
        </a>

        <!-- Bell / Notifications icon -->
        <a href="<?= APP_BASE ?>/notifications.php"
           class="nav-bell"
           style="position: relative; display: inline-flex; align-items: center;
                  color: var(--muted); text-decoration: none; padding: 4px;"
           title="Известия">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <?php if ($notif_count > 0): ?>
            <span style="position: absolute; top: -6px; right: -6px;
                         background: #ef4444; color: #fff; font-size: 10px;
                         font-weight: 700; border-radius: 50%;
                         width: 18px; height: 18px;
                         display: flex; align-items: center; justify-content: center;">
              <?= $notif_count ?>
            </span>
          <?php endif; ?>
        </a>

        <a href="<?= APP_BASE ?>/dashboard.php"
           class="nav-avatar-link"
           style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text); font-size: 14px;">
          <?php if (!empty($nav_user['avatar_path'])): ?>
            <img src="<?= APP_BASE . '/' . h($nav_user['avatar_path']) ?>"
                 style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
          <?php else: ?>
            <div style="width: 32px; height: 32px; border-radius: 50%; background: <?= $nav_color ?>;
                        display: flex; align-items: center; justify-content: center;
                        font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;">
              <?= $nav_letter ?>
            </div>
          <?php endif; ?>
          <span class="nav-username"><?= h($nav_user['username'] ?? '') ?></span>
        </a>
        <a class="btn-cta nav-sell" href="<?= APP_BASE ?>/product_create.php">Продай</a>
        <a href="<?= APP_BASE ?>/logout.php"
           class="nav-logout"
           style="display:inline-flex; align-items:center; gap:6px;
                  padding: 8px 16px;
                  background: rgba(239,68,68,0.1);
                  border: 1px solid rgba(239,68,68,0.25);
                  border-radius: 8px;
                  color: #f87171;
                  font-size: 13px;
                  font-weight: 500;
                  transition: all 0.2s;"
           onmouseover="this.style.background='rgba(239,68,68,0.2)'"
           onmouseout="this.style.background='rgba(239,68,68,0.1)'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          Изход
        </a>
      <?php else: ?>
        <a class="btn" href="<?= APP_BASE ?>/login.php">Вход</a>
        <a class="btn-cta" href="<?= APP_BASE ?>/register.php">Регистрация</a>
      <?php endif; ?>
      <button id="theme-toggle" aria-label="Смени тема"
              style="background:none;border:none;cursor:pointer;
                     color:var(--muted);padding:6px;font-size:18px;">
        <span id="theme-icon">☀️</span>
      </button>
    </div>

  </div>
</header>

<main>
