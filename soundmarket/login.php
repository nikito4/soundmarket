<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = post('email');
  $password = (string)($_POST['password'] ?? '');

  $stmt = db()->prepare("SELECT id, username, password_hash FROM users WHERE email=?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if (!$u || !password_verify($password, $u['password_hash'])) {
    $error = 'Грешен имейл или парола.';
  } else {
    login_user((int)$u['id'], (string)$u['username']);
    redirect(APP_BASE . '/dashboard.php');
  }
}

$title = 'Вход — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>
<div class="auth-wrap">
  <div class="container">
    <div class="auth-card">
      <div class="page-head" style="padding: 0 0 10px;">
        <h2>Вход</h2>
        <p>Влез, за да ползваш количката и профила си.</p>
      </div>

      <form class="form" method="post">
        <div>
          <label>Имейл</label>
          <input type="email" name="email" required value="<?= h(post('email')) ?>" placeholder="name@example.com">
        </div>

        <div>
          <label>Парола</label>
          <input type="password" name="password" required placeholder="••••••••">
        </div>

        <?php if ($error): ?>
          <p style="margin:0; color:#b42318; font-weight:800;"><?= h($error) ?></p>
        <?php endif; ?>

        <button class="btn primary" type="submit">Вход</button>

        <div class="auth-links">
          Нямаш акаунт? <a href="<?= APP_BASE ?>/register.php">Регистрация</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>