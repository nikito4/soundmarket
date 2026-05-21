<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

require_login();

$uid    = current_user_id();
$errors = [];

// Load current user data
$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Text fields ---
    $display_name = trim(post('display_name'));
    $bio          = trim(post('bio'));
    $location     = trim(post('location'));
    $website      = trim(post('website'));
    $instagram    = ltrim(trim(post('instagram')), '@');
    $soundcloud   = trim(post('soundcloud'));
    $youtube      = trim(post('youtube'));
    $genre_tags   = trim(post('genre_tags'));

    if (mb_strlen($display_name) > 100) $errors[] = 'Показваното име е твърде дълго (макс. 100 знака).';
    if (mb_strlen($bio) > 300)          $errors[] = 'Биографията е твърде дълга (макс. 300 знака).';

    // --- File upload helper ---
    $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];

    $avatar_path = $user['avatar_path'] ?? null;
    $banner_path = $user['banner_path'] ?? null;

    // Avatar upload
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $fi   = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fi, $_FILES['avatar']['tmp_name']);
        finfo_close($fi);

        if (!in_array($mime, $allowed_mime, true)) {
            $errors[] = 'Аватарът трябва да е JPG, PNG или WebP.';
        } elseif ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Аватарът не може да е по-голям от 5 MB.';
        } else {
            $dir = __DIR__ . '/uploads/avatars';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $dest = $dir . '/' . $uid . '.jpg';
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $errors[] = 'Грешка при запазване на аватара.';
            } else {
                $avatar_path = 'uploads/avatars/' . $uid . '.jpg';
            }
        }
    }

    // Banner upload
    if (!empty($_FILES['banner']['tmp_name'])) {
        $fi   = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fi, $_FILES['banner']['tmp_name']);
        finfo_close($fi);

        if (!in_array($mime, $allowed_mime, true)) {
            $errors[] = 'Банерът трябва да е JPG, PNG или WebP.';
        } elseif ($_FILES['banner']['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Банерът не може да е по-голям от 10 MB.';
        } else {
            $dir = __DIR__ . '/uploads/banners';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $dest = $dir . '/' . $uid . '.jpg';
            if (!move_uploaded_file($_FILES['banner']['tmp_name'], $dest)) {
                $errors[] = 'Грешка при запазване на банера.';
            } else {
                $banner_path = 'uploads/banners/' . $uid . '.jpg';
            }
        }
    }

    if (empty($errors)) {
        db()->prepare("
            UPDATE users SET
              display_name = ?,
              bio          = ?,
              location     = ?,
              website      = ?,
              instagram    = ?,
              soundcloud   = ?,
              youtube      = ?,
              genre_tags   = ?,
              avatar_path  = ?,
              banner_path  = ?
            WHERE id = ?
        ")->execute([
            $display_name ?: null,
            $bio          ?: null,
            $location     ?: null,
            $website      ?: null,
            $instagram    ?: null,
            $soundcloud   ?: null,
            $youtube      ?: null,
            $genre_tags   ?: null,
            $avatar_path,
            $banner_path,
            $uid,
        ]);

        redirect(APP_BASE . '/dashboard.php?saved=1');
    }

    // Keep form values on error
    $user = array_merge($user, compact(
        'display_name','bio','location','website',
        'instagram','soundcloud','youtube','genre_tags'
    ));
}

$title = 'Редактирай профила — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>

<div class="container" style="padding: 40px 0; max-width: 680px;">

  <div class="page-head" style="margin-bottom: 28px;">
    <h2>Редактирай профила</h2>
    <p class="muted">Попълни информацията за твоя публичен профил.</p>
  </div>

  <?php if (!empty($errors)): ?>
    <div style="background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.35);
                border-radius: 10px; padding: 14px 18px; margin-bottom: 24px;">
      <?php foreach ($errors as $e): ?>
        <p style="color: #f87171; margin: 4px 0;"><?= h($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card" style="padding: 28px; display: flex; flex-direction: column; gap: 22px;">

    <!-- Avatar + Banner uploads -->
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
      <div style="flex: 1; min-width: 200px;">
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Аватар (JPG/PNG/WebP, макс. 5 MB)</label>
        <?php if (!empty($user['avatar_path'])): ?>
          <img src="<?= APP_BASE . '/' . h($user['avatar_path']) ?>?<?= time() ?>"
               style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; margin-bottom: 8px; display: block;">
        <?php endif; ?>
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" style="font-size: 13px;">
      </div>
      <div style="flex: 1; min-width: 200px;">
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Банер (JPG/PNG/WebP, макс. 10 MB)</label>
        <?php if (!empty($user['banner_path'])): ?>
          <img src="<?= APP_BASE . '/' . h($user['banner_path']) ?>?<?= time() ?>"
               style="width: 100%; max-width: 260px; height: 60px; object-fit: cover; border-radius: 8px; margin-bottom: 8px; display: block;">
        <?php endif; ?>
        <input type="file" name="banner" accept="image/jpeg,image/png,image/webp" style="font-size: 13px;">
      </div>
    </div>

    <!-- Display name -->
    <div>
      <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Показвано ime</label>
      <input type="text" name="display_name" maxlength="100"
             value="<?= h((string)($user['display_name'] ?? '')) ?>"
             placeholder="<?= h($user['username']) ?>">
    </div>

    <!-- Bio -->
    <div>
      <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">
        Биография
        <span id="bio-count" style="float: right; color: var(--muted);">
          <?= mb_strlen((string)($user['bio'] ?? '')) ?>/300
        </span>
      </label>
      <textarea name="bio" maxlength="300" rows="4"
                id="bio-textarea"
                placeholder="Разкажи за себе си…"
                style="resize: vertical;"><?= h((string)($user['bio'] ?? '')) ?></textarea>
    </div>

    <!-- Location -->
    <div>
      <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Местоположение</label>
      <input type="text" name="location" maxlength="100"
             value="<?= h((string)($user['location'] ?? '')) ?>"
             placeholder="Варна, България">
    </div>

    <!-- Website -->
    <div>
      <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Уебсайт</label>
      <input type="url" name="website" maxlength="255"
             value="<?= h((string)($user['website'] ?? '')) ?>"
             placeholder="https://yoursite.com">
    </div>

    <!-- Social handles -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
      <div>
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Instagram</label>
        <input type="text" name="instagram" maxlength="100"
               value="<?= h((string)($user['instagram'] ?? '')) ?>"
               placeholder="@yourname">
      </div>
      <div>
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">SoundCloud</label>
        <input type="text" name="soundcloud" maxlength="100"
               value="<?= h((string)($user['soundcloud'] ?? '')) ?>"
               placeholder="username или URL">
      </div>
      <div>
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">YouTube</label>
        <input type="text" name="youtube" maxlength="255"
               value="<?= h((string)($user['youtube'] ?? '')) ?>"
               placeholder="URL или канал">
      </div>
    </div>

    <!-- Genre tags -->
    <div>
      <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Жанрове (разделени със запетая)</label>
      <input type="text" name="genre_tags" maxlength="255"
             value="<?= h((string)($user['genre_tags'] ?? '')) ?>"
             placeholder="Hip-Hop, Trap, R&B">
    </div>

    <div style="display: flex; gap: 12px; margin-top: 4px;">
      <button type="submit" class="btn primary" style="flex: 1;">Запази промените</button>
      <a href="<?= APP_BASE ?>/dashboard.php" class="btn" style="flex: 1; text-align: center;">Отказ</a>
    </div>

  </form>
</div>

<script>
(function () {
  var ta    = document.getElementById('bio-textarea');
  var count = document.getElementById('bio-count');
  if (!ta || !count) return;
  ta.addEventListener('input', function () {
    var len = ta.value.length;
    count.textContent = len + '/300';
    count.style.color = len >= 280 ? '#f87171' : '';
  });
})();
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
