<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$pdo = db();

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

$stmt = $pdo->prepare("
  SELECT p.id, p.title, p.type, p.price_bgn, u.username AS owner_username
  FROM products p
  JOIN users u ON u.id = p.owner_id
  ORDER BY p.created_at DESC
  LIMIT 10
");
$stmt->execute();
$latest = $stmt->fetchAll();

$title = 'Начало — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>

<section class="hero-bs">
  <div class="hero-wrap">
    <div class="hero-media" style="background-image:url('<?= APP_BASE ?>/uploads/hero.jpg');"></div>
    <div class="hero-overlay"></div>

    <div class="landing-container">
      <div class="hero-content">
        <h1>YOUR FIRST HIT STARTS HERE</h1>

        <form class="hero-search" method="get" action="<?= APP_BASE ?>/beats.php" role="search">
          <span class="s-icon" aria-hidden="true">⌕</span>
          <input name="q" value="<?= h($q) ?>" placeholder="Explore new sounds — search for anything" />
          <button type="submit">Search</button>
        </form>
      </div>
    </div>
  </div>
</section>

<section style="padding: 60px 0;">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
      <div>
        <h2 style="font-size: 2rem; margin: 0;">Trending tracks</h2>
        <p class="muted">Най-популярните предложения в момента.</p>
      </div>
      <a href="<?= APP_BASE ?>/beats.php" class="btn">See more</a>
    </div>

    <div class="grid">
      <?php if (!$latest): ?>
        <div class="muted">Няма качени продукти още.</div>
      <?php else: ?>
        <?php foreach ($latest as $p): ?>
          <article class="card">
            <?php 
              $userCover = !empty($p['cover_path']) ? APP_BASE . '/' . h($p['cover_path']) : null;
              $defaultCover = APP_BASE . '/uploads/blog-vinyl-lp.800x0.jpg';
              $finalCover = $userCover ?: $defaultCover;
            ?>
            <div class="card-img-container" style="background-image: url('<?= $finalCover ?>');">
              <?php if (!empty($p['file_path'])): ?>
                <div class="card-media-overlay">
                  <audio controls controlsList="nodownload">
                    <source src="<?= APP_BASE ?>/<?= h($p['file_path']) ?>" type="audio/mpeg">
                  </audio>
                </div>
              <?php endif; ?>
            </div>

            <div class="card-body">
              <div class="card-title">
                <h3 style="margin: 0; font-size: 1.1rem;"><?= h($p['title']) ?></h3>
                <small class="muted">от <?= h($p['owner_username']) ?></small>
              </div>
              <div class="price-row">
                <strong style="color: var(--brand); font-size: 1.2rem;"><?= format_bgn((int)$p['price_bgn']) ?></strong>
                <span class="pill"><?= h($p['type']) ?></span>
              </div>
              <a href="<?= APP_BASE ?>/product.php?id=<?= (int)$p['id'] ?>" class="btn" style="width: 100%; margin-top: 10px;">Детайли</a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 25px 0;">
  <div class="container">
    <div style="display: flex; align-items: center; justify-content: center; gap: 30px; flex-wrap: wrap; opacity: 0.7;">
      <div class="muted" style="font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Trusted by:</div>
      <div class="pill">Studio Partners</div>
      <div class="pill">Indie Labels</div>
      <div class="pill">Creators</div>
      <div class="pill">Audio Engineers</div>
      <div class="pill">Music Community</div>
    </div>
  </div>
</section>

<section style="padding: 100px 0;">
  <div class="container feature-split">
    <div class="feature-card">
      <span class="pill" style="background: var(--brand); color: #fff; margin-bottom: 15px; display: inline-block;">За артисти и клиенти</span>
      <h2 style="font-size: 2.5rem; line-height: 1.1; margin-bottom: 20px;">Развий музиката си с по-бърз процес</h2>
      <p class="muted" style="font-size: 1.1rem; margin-bottom: 30px;">
        Публикувай бийтове и песни или поръчай микс/мастъринг – всичко в една платформа.
      </p>

      <div style="display: grid; gap: 15px; margin-bottom: 40px;">
        <div style="display: flex; gap: 12px;">
          <span style="color: var(--brand); font-weight: bold;">✓</span>
          <div><strong>Профили и качване</strong><br><small class="muted">Артисти публикуват продукти и услуги директно.</small></div>
        </div>
        <div style="display: flex; gap: 12px;">
          <span style="color: var(--brand); font-weight: bold;">✓</span>
          <div><strong>Функционална количка</strong><br><small class="muted">Удобно добавяне и управление на поръчките.</small></div>
        </div>
      </div>

      <div style="display: flex; gap: 12px;">
        <?php if (!is_logged_in()): ?>
          <a class="btn primary" href="<?= APP_BASE ?>/register.php">Get started</a>
          <a class="btn" href="<?= APP_BASE ?>/beats.php">Browse</a>
        <?php else: ?>
          <a class="btn primary" href="<?= APP_BASE ?>/product_create.php">Качи оферта</a>
          <a class="btn" href="<?= APP_BASE ?>/dashboard.php">Профил</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="media-frame">
      <div style="width:100%; height:100%; background-image:url('<?= APP_BASE ?>/uploads/studio.webp'); background-size:cover; background-position:center;"></div>
    </div>
  </div>
</section>

<section style="padding: 80px 0; background: var(--surface);">
  <div class="container grid" style="grid-template-columns: 1fr 1fr; align-items: center; gap: 60px;">
    <div>
      <div class="pill" style="margin-bottom: 15px;">#MADEON<?= strtoupper(APP_NAME) ?></div>
      <h2 style="font-size: 2.2rem; margin-bottom: 20px;">Да, този бийт може да бъде купен от <?= APP_NAME ?>.</h2>
      <p class="muted" style="margin-bottom: 30px;">
        Пазар за дигитални музикални продукти и студийни услуги. Дизайнът е съобразен със стила на нашия проект и дипломната разработка.
      </p>
      <a class="btn" href="<?= APP_BASE ?>/beats.php">Разгледай каталога →</a>
    </div>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
      <?php 
        $mosaic = [
          ['m1.jpg', 'New drop', 'Beat'],
          ['m2.jpg', 'Featured', 'Music'],
          ['m3.jpg', 'Mix & Master', 'Service'],
          ['m4.jpg', 'Top creators', 'Community']
        ];
        foreach ($mosaic as [$img, $cap, $sub]):
      ?>
      <div class="card" style="border: none; position: relative; aspect-ratio: 1/1;">
        <div style="position: absolute; inset:0; background-image:url('<?= APP_BASE ?>/assets/img/<?= $img ?>'); background-size:cover; background-position:center; opacity: 0.6;"></div>
        <div style="position: absolute; bottom: 10px; left: 10px; z-index: 2; font-size: 12px; font-weight: bold;">
          <?= $cap ?> <span style="color: var(--brand);"><?= $sub ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="padding: 80px 0;">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
      <h2 style="font-size: 2rem; margin: 0;">Popular Genres</h2>
      <a href="<?= APP_BASE ?>/beats.php" class="btn">See more</a>
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));">
      <?php
        $genres = [
          ['Hip-Hop','hiphop.jpg'], ['Pop','pop.jpg'], ['R&B','RNB.webp'],
          ['Rock','rock.jpg'], ['Electronic','electronic.jpg'], ['Reggae','reggae.jpg']
        ];
        foreach ($genres as [$name, $img]):
      ?>
        <a href="<?= APP_BASE ?>/beats.php" class="card" style="text-decoration: none; padding: 20px; text-align: center;">
          <div style="width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 15px; background-image:url('<?= APP_BASE ?>/uploads/<?= h($img) ?>'); background-size: cover; background-position: center; border: 2px solid var(--border);"></div>
          <strong style="color: var(--text);"><?= h($name) ?></strong>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 25px 0;">
  <div class="container">
    <div style="display: flex; align-items: center; justify-content: center; gap: 30px; flex-wrap: wrap; opacity: 0.6;">
      <span class="muted" style="font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;">Trusted by:</span>
      <span class="pill">Studio Partners</span>
      <span class="pill">Indie Labels</span>
      <span class="pill">Creators</span>
      <span class="pill">Audio Engineers</span>
    </div>
  </div>
</section>

<div class="container" style="padding: 100px 0;">
  <div class="feature-split">
    <div class="feature-card">
      <span class="pill" style="background: var(--brand); color: white; margin-bottom: 15px; display: inline-block;">За артисти и клиенти</span>
      <h2 style="font-size: 2.5rem; line-height: 1.1; margin-bottom: 20px;">Развий музиката си с по-бърз процес</h2>
      <p class="muted" style="font-size: 1.1rem; margin-bottom: 30px;">
        Публикувай бийтове и песни или поръчай микс/мастъринг – всичко в една платформа с профили, каталог и количка.
      </p>

      <div style="display: grid; gap: 15px; margin-bottom: 40px;">
        <div style="display: flex; gap: 10px; align-items: start;">
          <span style="color: var(--brand);">✓</span>
          <div><strong>Профили и качване</strong><br><small class="muted">Публикувай продукти директно в сайта.</small></div>
        </div>
        <div style="display: flex; gap: 10px; align-items: start;">
          <span style="color: var(--brand);">✓</span>
          <div><strong>Удобно търсене</strong><br><small class="muted">Филтрирай по бийтове, музика и услуги.</small></div>
        </div>
      </div>

      <div style="display: flex; gap: 15px;">
        <?php if (!is_logged_in()): ?>
          <a class="btn primary" href="<?= APP_BASE ?>/register.php">Get started</a>
          <a class="btn" href="<?= APP_BASE ?>/beats.php">Browse</a>
        <?php else: ?>
          <a class="btn primary" href="<?= APP_BASE ?>/product_create.php">Качи оферта</a>
          <a class="btn" href="<?= APP_BASE ?>/dashboard.php">Моят профил</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="media-frame">
      <div style="width:100%; height:100%; background-image:url('<?= APP_BASE ?>/uploads/studio.webp'); background-size:cover; background-position:center;"></div>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom: 100px;">
  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
    <h2 style="font-size: 2rem; margin: 0;">Popular Genres</h2>
    <a href="<?= APP_BASE ?>/beats.php" class="btn">Всички жанрове</a>
  </div>

  <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
    <?php
      $genres = [
        ['Hip-Hop','hiphop.jpg'], ['Pop','pop.jpg'], ['R&B','RNB.webp'],
        ['Rock','rock.jpg'], ['Electronic','electronic.jpg'], ['Reggae','reggae.jpg'],
      ];
      foreach ($genres as [$name,$img]): 
    ?>
      <a href="<?= APP_BASE ?>/beats.php" class="card" style="text-decoration: none; text-align: center; padding: 15px;">
        <div style="width: 80px; height: 80px; margin: 0 auto 10px; border-radius: 50%; background-image:url('<?= APP_BASE ?>/uploads/<?= h($img) ?>'); background-size: cover;"></div>
        <strong style="color: var(--text);"><?= h($name) ?></strong>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<section class="testimonials">
  <div class="landing-container">
    <div style="text-align:center; padding: 10px 0 14px;">
      <div class="badge" style="background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.12);">Какво казват потребителите</div>
      <h2 style="margin: 12px 0 6px; font-size: 34px; letter-spacing:-.03em; text-transform:uppercase;">защо <?= APP_NAME ?> работи?</h2>
    </div>

    <div class="test-grid">
      <article class="quote-card">
        <div class="quote-media" style="background-image:url('<?= APP_BASE ?>/uploads/alex.jpg');">
          <div class="play"></div>
        </div>
        <div class="quote-body">
          <div class="quote-text">„Намерих бийт за минути, а поръчката мина гладко. Интерфейсът е много удобен.“</div>
          <div class="quote-person"><span>Alex</span><span>Artist</span></div>
        </div>
      </article>

      <article class="quote-card">
        <div class="quote-media" style="background-image:url('<?= APP_BASE ?>/uploads/mila.jpg');">
          <div class="play"></div>
        </div>
        <div class="quote-body">
          <div class="quote-text">„Секцията за услуги е супер – микс/мастъринг офертите са ясни и подредени.“</div>
          <div class="quote-person"><span>Mila</span><span>Client</span></div>
        </div>
      </article>

      <article class="quote-card">
        <div class="quote-media" style="background-image:url('<?= APP_BASE ?>/uploads/daniel.jpg');">
          <div class="play"></div>
        </div>
        <div class="quote-body">
          <div class="quote-text">„Като прототип за дипломна е много добре – има профили, качване и количка.“</div>
          <div class="quote-person"><span>Daniel</span><span>Producer</span></div>
        </div>
      </article>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>