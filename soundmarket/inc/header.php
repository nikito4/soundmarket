<?php
// inc/header.php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$title = $title ?? APP_NAME;

$cartCount = 0;
if (is_logged_in()) {
  $stmt = db()->prepare("SELECT COALESCE(SUM(quantity),0) AS c FROM cart_items WHERE user_id=?");
  $stmt->execute([current_user_id()]);
  $cartCount = (int)$stmt->fetch()['c'];
}
?>
<!doctype html>
<html lang="bg">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a href="<?= APP_BASE ?>/index.php" class="logo" style="font-weight: 900; font-size: 22px; text-decoration: none; color: var(--brand);">
      SOUND<span style="color: var(--text);">MARKET</span>
    </a>

    <nav class="nav">
      <a href="<?= APP_BASE ?>/beats.php">Бийтове</a>
      <a href="<?= APP_BASE ?>/music.php">Музика</a>
      <a href="<?= APP_BASE ?>/services.php">Услуги</a>
    </nav>

    <div class="header-actions">
      <a class="icon-btn" href="<?= APP_BASE ?>/cart.php">
        🛒 <span class="cart-count"><?= (int)$cartCount ?></span>
      </a>

      <?php if (is_logged_in()): ?>
        <a class="btn primary" href="<?= APP_BASE ?>/dashboard.php">Профил</a>
        <a class="btn-cta" href="<?= APP_BASE ?>/product_create.php">Продай</a>
      <?php else: ?>
        <a class="btn" href="<?= APP_BASE ?>/login.php">Вход</a>
        <a class="btn-cta" href="<?= APP_BASE ?>/register.php">Регистрация</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main>