<?php
declare(strict_types=1);
$_page_title = $page_title ?? 'Admin';
$_breadcrumb = $breadcrumb ?? $_page_title;
$_notif      = (int)admin_db()->query(
    "SELECT COUNT(*) FROM orders WHERE status='created' OR status=''"
)->fetchColumn();
?><!DOCTYPE html>
<html lang="bg" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($_page_title) ?> — SoundMarket Admin</title>
  <link rel="stylesheet" href="/soundmarket/admin/assets/style.css">
</head>
<body class="admin-body">

<?php require __DIR__ . '/sidebar.php'; ?>

<div class="page-wrapper" id="pageWrapper">

  <header class="topbar">
    <div class="topbar-left">
      <button class="hamburger" id="hamburger" aria-label="Меню">
        <span></span><span></span><span></span>
      </button>
      <nav class="breadcrumb" aria-label="breadcrumb">
        <a href="/soundmarket/admin/dashboard.php">Admin</a>
        <?php if ($_breadcrumb !== 'Dashboard'): ?>
          <span class="bc-sep">›</span>
          <span><?= h($_breadcrumb) ?></span>
        <?php endif; ?>
      </nav>
    </div>

    <div class="topbar-right">
      <button class="icon-action dark-toggle" id="darkToggle" title="Dark mode">🌙</button>

      <a href="/soundmarket/admin/orders.php?status=created" class="icon-action notif-btn" title="Нови поръчки">
        🔔<?php if ($_notif > 0): ?>
          <span class="notif-dot"><?= $_notif ?></span>
        <?php endif; ?>
      </a>

      <div class="profile-wrap">
        <button class="profile-btn" id="profileBtn">
          <span class="profile-avatar"><?= h(strtoupper(substr(admin_username(), 0, 1))) ?></span>
          <span class="profile-name"><?= h(admin_username()) ?></span>
          <span class="profile-caret">▾</span>
        </button>
        <div class="profile-dropdown" id="profileDropdown">
          <a href="/soundmarket/admin/settings.php">⚙ Настройки</a>
          <hr>
          <a href="/soundmarket/admin/logout.php">⏻ Изход</a>
        </div>
      </div>
    </div>
  </header>

  <main class="main-content">
