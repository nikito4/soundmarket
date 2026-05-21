<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: /soundmarket/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Невалидна заявка. Опитайте отново.';
    } else {
        $_SESSION['admin_login_attempts']      = (int)($_SESSION['admin_login_attempts']      ?? 0);
        $_SESSION['admin_login_blocked_until'] = (int)($_SESSION['admin_login_blocked_until'] ?? 0);

        if ($_SESSION['admin_login_blocked_until'] > time()) {
            $minutes_left = (int)ceil(($_SESSION['admin_login_blocked_until'] - time()) / 60);
            $error = "Твърде много неуспешни опити. Опитайте след $minutes_left мин.";
        } else {
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                $error = 'Моля, попълнете потребителско име и парола.';
            } else {
                $stmt = admin_db()->prepare(
                    "SELECT id, username, password_hash, role FROM admin_users WHERE username = ? LIMIT 1"
                );
                $stmt->execute([$username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    unset($_SESSION['admin_login_attempts'], $_SESSION['admin_login_blocked_until']);
                    admin_login((int)$admin['id'], $admin['username'], $admin['role']);
                    log_action(admin_db(), (int)$admin['id'], 'login');
                    header('Location: /soundmarket/admin/dashboard.php');
                    exit;
                } else {
                    $_SESSION['admin_login_attempts']++;
                    if ($_SESSION['admin_login_attempts'] >= 5) {
                        $_SESSION['admin_login_blocked_until'] = time() + 900;
                        $_SESSION['admin_login_attempts']      = 0;
                        $error = 'Твърде много неуспешни опити. Опитайте след 15 мин.';
                    } else {
                        $error = 'Грешно потребителско име или парола.';
                        if ($_SESSION['admin_login_attempts'] >= 3) {
                            $error .= ' (Опит ' . $_SESSION['admin_login_attempts'] . '/5)';
                        }
                    }
                }
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="bg" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Вход — SoundMarket Admin</title>
  <link rel="stylesheet" href="/soundmarket/admin/assets/style.css">
</head>
<body class="login-page">

<div class="login-card">
  <div class="login-logo">
    <span class="logo-mark">SM</span>
    <div class="logo-text">SoundMarket<br><em>Admin Panel</em></div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= h($error) ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
      <label for="username">Потребителско ime</label>
      <input id="username" name="username" type="text"
             value="<?= h((string)($_POST['username'] ?? '')) ?>"
             placeholder="admin" autocomplete="username" required autofocus>
    </div>

    <div class="form-group" style="margin-bottom:20px;">
      <label for="password">Парола</label>
      <input id="password" name="password" type="password"
             placeholder="••••••••" autocomplete="current-password" required>
    </div>

    <button type="submit" class="btn btn-primary">Влез в панела</button>
  </form>

  <div class="login-footer">SoundMarket © <?= date('Y') ?></div>
</div>

<script src="/soundmarket/admin/assets/script.js"></script>
</body>
</html>
