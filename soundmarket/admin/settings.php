<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

admin_require_auth();
$pdo = admin_db();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF token.'); }

    $current  = (string)($_POST['current_password']  ?? '');
    $new_pass = (string)($_POST['new_password']       ?? '');
    $confirm  = (string)($_POST['confirm_password']   ?? '');

    if ($current === '' || $new_pass === '' || $confirm === '') {
        $error = 'Моля, попълнете всички полета.';
    } elseif (strlen($new_pass) < 8) {
        $error = 'Новата парола трябва да е поне 8 символа.';
    } elseif ($new_pass !== $confirm) {
        $error = 'Новата парола и потвърждението не съвпадат.';
    } else {
        $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([admin_id()]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $error = 'Текущата парола е грешна.';
        } else {
            $hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?")->execute([$hash, admin_id()]);
            log_action($pdo, admin_id(), 'Смяна на парола', 'admin_users', admin_id());
            $success = 'Паролата е успешно сменена.';
        }
    }
}

/* ── Admin users list (superadmin only) ── */
$admin_users = [];
if (is_superadmin()) {
    $admin_users = $pdo->query("SELECT id, username, role, created_at FROM admin_users ORDER BY id")->fetchAll();
}

$page_title = 'Настройки';
$breadcrumb = 'Настройки';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <h1>Настройки</h1>
</div>

<div class="two-panels">

  <!-- Change password -->
  <div>
    <div class="card" style="margin-bottom:20px;">
      <div class="card-header"><h3>🔐 Смяна на парола</h3></div>
      <div class="card-body">
        <?php if ($error):   ?><div class="alert alert-error">⚠ <?= h($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success">✓ <?= h($success) ?></div><?php endif; ?>

        <form method="post" style="max-width:420px;">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="current_password">Текуща парола</label>
            <input id="current_password" name="current_password" type="password"
                   placeholder="••••••••" autocomplete="current-password" required>
          </div>
          <div class="form-group">
            <label for="new_password">Нова парола</label>
            <input id="new_password" name="new_password" type="password"
                   placeholder="Минимум 8 символа" autocomplete="new-password" required>
          </div>
          <div class="form-group" style="margin-bottom:20px;">
            <label for="confirm_password">Потвърди новата парола</label>
            <input id="confirm_password" name="confirm_password" type="password"
                   placeholder="••••••••" autocomplete="new-password" required>
          </div>
          <button type="submit" class="btn btn-primary">Запази паролата</button>
        </form>
      </div>
    </div>

    <?php if (is_superadmin() && $admin_users): ?>
    <div class="card">
      <div class="card-header"><h3>👑 Администратори</h3></div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>ID</th><th>Потребител</th><th>Роля</th><th>Създаден</th></tr>
          </thead>
          <tbody>
            <?php foreach ($admin_users as $au): ?>
              <tr>
                <td class="text-muted"><?= (int)$au['id'] ?></td>
                <td class="fw-700"><?= h($au['username']) ?></td>
                <td>
                  <span class="badge <?= $au['role'] === 'superadmin' ? 'badge-purple' : 'badge-blue' ?>">
                    <?= h(ucfirst($au['role'])) ?>
                  </span>
                </td>
                <td class="text-muted"><?= h(date('d.m.Y', strtotime($au['created_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Info panel -->
  <div>
    <div class="card">
      <div class="card-header"><h3>ℹ Информация</h3></div>
      <div class="card-body">
        <div class="form-group">
          <label>Потребителско име</label>
          <input type="text" value="<?= h(admin_username()) ?>" disabled>
        </div>
        <div class="form-group">
          <label>Роля</label>
          <input type="text" value="<?= h(ucfirst(admin_role())) ?>" disabled>
        </div>
        <div class="form-group">
          <label>PHP версия</label>
          <input type="text" value="<?= h(PHP_VERSION) ?>" disabled>
        </div>
        <div class="form-group">
          <label>Сървър</label>
          <input type="text" value="<?= h($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') ?>" disabled>
        </div>
        <p class="form-hint mt-8">
          ⚠ Сменяйте паролата си редовно. Не споделяйте достъп до admin панела.
        </p>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
