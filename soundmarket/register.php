<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = post('username');
  $email = post('email');
  $password = (string)($_POST['password'] ?? '');

  if ($username === '' || $email === '' || $password === '') {
    $error = 'Попълни всички полета.';
  } elseif (strlen($password) < 6) {
    $error = 'Паролата трябва да е поне 6 символа.';
  } else {
    try {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = db()->prepare("INSERT INTO users(username,email,password_hash) VALUES(?,?,?)");
      $stmt->execute([$username, $email, $hash]);
      $id = (int)db()->lastInsertId();
      login_user($id, $username);
      redirect(APP_BASE . '/dashboard.php');
    } catch (Throwable $e) {
      $error = 'Потребителското име или имейлът вече съществуват.';
    }
  }
}

$title = 'Регистрация — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>
<div class="auth-wrap">
  <div class="container">
    <div class="auth-card">
      <div class="page-head" style="padding: 0 0 10px;">
        <h2>Регистрация</h2>
        <p>Създай профил и започни да продаваш музика и услуги.</p>
      </div>

      <form class="form" method="post">

        <div>
          <label>Потребителско име</label>
          <input 
            name="username" 
            required 
            value="<?= h(post('username')) ?>" 
            placeholder="artist_name">
        </div>

        <div>
          <label>Имейл</label>
          <input 
            type="email" 
            name="email" 
            required 
            value="<?= h(post('email')) ?>" 
            placeholder="name@example.com">
        </div>

        <div>
          <label>Парола</label>
          <input 
            type="password" 
            name="password" 
            required 
            minlength="6"
            placeholder="Минимум 6 символа">
        </div>

        <?php if ($error): ?>
          <p style="margin:0; color:#b42318; font-weight:800;">
            <?= h($error) ?>
          </p>
        <?php endif; ?>

        <button class="btn primary" type="submit">
          Регистрация
        </button>

        <div class="auth-links">
          Имаш акаунт? 
          <a href="<?= APP_BASE ?>/login.php">Вход</a>
        </div>

      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>