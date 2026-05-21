<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';

$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($profile_id <= 0) {
    http_response_code(404);
    exit('Потребителят не е намерен.');
}

$stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_id]);
$profile = $stmt->fetch();
if (!$profile) {
    http_response_code(404);
    $title = '404 — Потребителят не е намерен';
    require __DIR__ . '/inc/header.php';
    echo '<div class="container" style="padding:100px 20px;text-align:center;">
      <div style="font-size:8rem;font-weight:900;color:var(--brand);line-height:1;margin-bottom:20px;opacity:.8;">404</div>
      <h1 style="font-size:1.8rem;margin-bottom:12px;">Потребителят не е намерен</h1>
      <p class="muted">Този профил не съществува или е изтрит.</p>
      <div style="display:flex;gap:12px;justify-content:center;margin-top:32px;">
        <a href="' . APP_BASE . '/index.php" class="btn primary">Към началото</a>
      </div>
    </div>';
    require __DIR__ . '/inc/footer.php';
    exit;
}

// Product count
$stmt = db()->prepare("SELECT COUNT(*) FROM products WHERE owner_id = ?");
$stmt->execute([$profile_id]);
$product_count = (int)$stmt->fetchColumn();

// Sales count (how many order_items contain their products)
$stmt = db()->prepare("
    SELECT COUNT(oi.id)
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE p.owner_id = ?
");
$stmt->execute([$profile_id]);
$sales_count = (int)$stmt->fetchColumn();

// Products grid
$stmt = db()->prepare("
    SELECT p.*, u.username AS owner_username
    FROM products p
    JOIN users u ON u.id = p.owner_id
    WHERE p.owner_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$profile_id]);
$products = $stmt->fetchAll();

$display_name = $profile['display_name'] ?: $profile['username'];
$is_owner     = is_logged_in() && current_user_id() === $profile_id;

$avatar_colors = ['#7c3aed','#0891b2','#db2777','#059669','#d97706','#dc2626'];
$avatar_color  = $avatar_colors[$profile_id % 6];
$avatar_letter = mb_strtoupper(mb_substr($profile['username'], 0, 1));

$title = h($display_name) . ' — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>

<!-- Banner -->
<div style="width: 100%; height: 220px; overflow: hidden;
  <?php if (!empty($profile['banner_path'])): ?>
    background-image: url('<?= APP_BASE . '/' . h($profile['banner_path']) ?>');
    background-size: cover; background-position: center;
  <?php else: ?>
    background: linear-gradient(135deg, #2d1b69 0%, #11093a 50%, #0f0f1a 100%);
  <?php endif; ?>">
</div>

<div class="container" style="max-width: 960px;">

  <!-- Avatar -->
  <div style="margin-left: 40px; margin-top: -55px; display: inline-block;">
    <?php if (!empty($profile['avatar_path'])): ?>
      <img src="<?= APP_BASE . '/' . h($profile['avatar_path']) ?>"
           style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
                  border: 4px solid #0f0f1a; display: block;">
    <?php else: ?>
      <div style="width: 110px; height: 110px; border-radius: 50%;
                  background: <?= $avatar_color ?>; border: 4px solid #0f0f1a;
                  display: flex; align-items: center; justify-content: center;
                  font-size: 44px; font-weight: 700; color: #fff;">
        <?= $avatar_letter ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Profile header -->
  <div style="display: flex; justify-content: space-between; align-items: flex-start;
              padding: 16px 40px 30px; flex-wrap: wrap; gap: 20px;">

    <!-- Left column -->
    <div style="flex: 1; min-width: 260px;">
      <h1 style="font-size: 1.8rem; font-weight: 700; margin: 0 0 4px;">
        <?= h($display_name) ?>
      </h1>
      <p style="font-size: 0.95rem; color: var(--muted); margin: 0 0 8px;">
        @<?= h($profile['username']) ?>
      </p>

      <?php if (!empty($profile['location'])): ?>
        <p style="font-size: 14px; color: var(--muted); margin: 0 0 8px; display: flex; align-items: center; gap: 5px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          <?= h($profile['location']) ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($profile['bio'])): ?>
        <p style="max-width: 500px; line-height: 1.6; margin: 10px 0 12px; font-size: 15px;">
          <?= nl2br(h($profile['bio'])) ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($profile['genre_tags'])): ?>
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
          <?php foreach (array_map('trim', explode(',', $profile['genre_tags'])) as $tag): ?>
            <?php if ($tag !== ''): ?>
              <span style="background: rgba(124,58,237,0.15); color: #a78bfa;
                           border: 1px solid rgba(124,58,237,0.3); border-radius: 20px;
                           padding: 4px 12px; font-size: 13px;">
                <?= h($tag) ?>
              </span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Social links -->
      <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <?php if (!empty($profile['instagram'])): ?>
          <a href="https://instagram.com/<?= h($profile['instagram']) ?>" target="_blank" rel="noopener"
             style="color: var(--muted);" title="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
              <circle cx="12" cy="12" r="4"/>
              <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
            </svg>
          </a>
        <?php endif; ?>

        <?php if (!empty($profile['soundcloud'])): ?>
          <?php $sc_url = str_starts_with($profile['soundcloud'], 'http') ? $profile['soundcloud'] : 'https://soundcloud.com/' . $profile['soundcloud']; ?>
          <a href="<?= h($sc_url) ?>" target="_blank" rel="noopener"
             style="color: var(--muted);" title="SoundCloud">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M1.175 12.225c-.015.13 0 .26.014.39l1.18 7.635c.046.29.32.505.61.505.29 0 .563-.216.608-.505l1.18-7.636c.014-.13.014-.26 0-.39-.05-.26-.29-.462-.608-.462-.32 0-.56.202-.608.462zM0 14.373c0 .202.174.376.376.376.203 0 .376-.174.376-.376v-2.148c0-.203-.173-.376-.376-.376-.202 0-.376.173-.376.376v2.148zm4.74-3.67c-.202 0-.376.173-.376.376v5.217c0 .203.174.376.376.376.203 0 .376-.173.376-.376v-5.217c0-.203-.173-.376-.376-.376zm1.564-.505c-.202 0-.376.174-.376.376v6.227c0 .203.174.376.376.376.202 0 .376-.173.376-.376v-6.227c0-.202-.174-.376-.376-.376zm1.563.505c-.202 0-.376.173-.376.376v5.217c0 .203.174.376.376.376.203 0 .376-.173.376-.376v-5.217c0-.203-.173-.376-.376-.376zm1.566-.91c-.203 0-.377.174-.377.376v6.632c0 .202.174.376.377.376.202 0 .375-.174.375-.376v-6.632c0-.202-.173-.376-.375-.376zm1.562.505c-.202 0-.376.173-.376.376v5.62c0 .202.174.376.376.376.203 0 .376-.174.376-.376v-5.62c0-.203-.173-.376-.376-.376zm3.132-3.497c-.26-.087-.534-.13-.81-.13-1.26 0-2.35.84-2.7 2.003-.362-.173-.766-.26-1.17-.26-1.55 0-2.81 1.26-2.81 2.81 0 .072.003.145.01.217H17.3c1.505 0 2.7-1.195 2.7-2.7 0-1.504-1.195-2.7-2.7-2.7-.26 0-.52.043-.77.13z"/>
            </svg>
          </a>
        <?php endif; ?>

        <?php if (!empty($profile['youtube'])): ?>
          <?php $yt_url = str_starts_with($profile['youtube'], 'http') ? $profile['youtube'] : 'https://youtube.com/' . $profile['youtube']; ?>
          <a href="<?= h($yt_url) ?>" target="_blank" rel="noopener"
             style="color: var(--muted);" title="YouTube">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.54 3.5 12 3.5 12 3.5s-7.54 0-9.38.55A3.02 3.02 0 0 0 .5 6.19C0 8.03 0 12 0 12s0 3.97.5 5.81a3.02 3.02 0 0 0 2.12 2.14C4.46 20.5 12 20.5 12 20.5s7.54 0 9.38-.55a3.02 3.02 0 0 0 2.12-2.14C24 15.97 24 12 24 12s0-3.97-.5-5.81zM9.75 15.5v-7l6.25 3.5-6.25 3.5z"/>
            </svg>
          </a>
        <?php endif; ?>

        <?php if (!empty($profile['website'])): ?>
          <a href="<?= h($profile['website']) ?>" target="_blank" rel="noopener"
             style="color: var(--muted);" title="Уебсайт">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="2" y1="12" x2="22" y2="12"/>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right column -->
    <div style="text-align: right; flex-shrink: 0;">
      <div style="display: flex; gap: 28px; justify-content: flex-end;">
        <div style="text-align: center;">
          <div style="font-size: 1.8rem; font-weight: 700; color: var(--brand);"><?= $product_count ?></div>
          <div style="font-size: 12px; color: var(--muted);">Продукти</div>
        </div>
        <div style="text-align: center;">
          <div style="font-size: 1.8rem; font-weight: 700; color: var(--brand);"><?= $sales_count ?></div>
          <div style="font-size: 12px; color: var(--muted);">Продажби</div>
        </div>
      </div>

      <?php if ($is_owner): ?>
        <div style="margin-top: 16px;">
          <a href="<?= APP_BASE ?>/profile_edit.php" class="btn">Редактирай профила</a>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- Divider -->
  <div style="border-top: 1px solid var(--border); margin: 0 40px 32px;"></div>

  <!-- Products grid -->
  <div style="padding: 0 40px 60px;">
    <h2 class="section-title" style="margin-bottom: 24px;">Оферти</h2>

    <?php if (empty($products)): ?>
      <p class="muted">Този потребител все още няма качени оферти.</p>
    <?php else: ?>
      <section class="grid">
        <?php foreach ($products as $p): ?>
          <article class="card">
            <?php
              $userCover  = !empty($p['cover_path']) ? APP_BASE . '/' . h($p['cover_path']) : null;
              $defaultCover = APP_BASE . '/uploads/blog-vinyl-lp.800x0.jpg';
              $finalCover = $userCover ?: $defaultCover;
            ?>
            <div class="card-img-container" style="background-image: url('<?= $finalCover ?>');">
              <div class="card-media-overlay">
                <?php if (!empty($p['file_path'])): ?>
                  <audio controls controlsList="nodownload">
                    <source src="<?= APP_BASE . '/' . h($p['file_path']) ?>" type="audio/mpeg">
                  </audio>
                <?php endif; ?>
              </div>
            </div>

            <div class="card-body">
              <div class="card-title">
                <h3><?= h($p['title']) ?></h3>
                <small class="muted">от <?= h($p['owner_username']) ?></small>
              </div>

              <div class="meta" style="font-size: 14px; color: var(--muted);">
                <span>#<?= h((string)($p['genre'] ?? 'General')) ?></span>
                <?php if (!empty($p['bpm'])): ?><span>• <?= (int)$p['bpm'] ?> BPM</span><?php endif; ?>
              </div>

              <div class="price-row">
                <span class="price-pill"><?= format_eur((int)$p['price_eur']) ?></span>
                <span class="pill"><?= h($p['type']) ?></span>
              </div>

              <div class="actions">
                <a class="btn" href="<?= APP_BASE ?>/product.php?id=<?= (int)$p['id'] ?>">Детайли</a>
                <form method="post" action="<?= APP_BASE ?>/cart.php" style="margin: 0;">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                  <button class="btn primary" type="submit" style="width: 100%;">Купи</button>
                </form>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </div>

</div>

<style>
  a[title="Instagram"]:hover,
  a[title="SoundCloud"]:hover,
  a[title="YouTube"]:hover,
  a[title="Уебсайт"]:hover { color: var(--brand) !important; }
</style>

<?php require __DIR__ . '/inc/footer.php'; ?>
