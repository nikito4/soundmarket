<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$pdo = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(404);
    exit('Невалиден продукт.');
}

$stmt = $pdo->prepare("
    SELECT p.*, u.username
    FROM products p
    JOIN users u ON u.id = p.owner_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    exit('Продуктът не е намерен.');
}

$title = h($product['title']) . ' — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>

<section class="landing-section">
    <div class="landing-container">
        
        <div class="feature-split">
            <div class="media-frame">
                <?php 
                  $userCover = !empty($product['cover_path']) ? APP_BASE . '/' . h($product['cover_path']) : null;
                  $defaultCover = APP_BASE . '/uploads/blog-vinyl-lp.800x0.jpg';
                  $finalCover = $userCover ?: $defaultCover;
                ?>
                <div class="media" style="background-image: url('<?= $finalCover ?>'); width:100%; height:100%; background-size:cover; background-position:center;">
                    
                    <?php if (!empty($product['file_path'])): ?>
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 20px;">
                            <audio controls controlsList="nodownload" style="width: 100%; height: 35px; filter: invert(100%) hue-rotate(180deg) brightness(1.5);">
                                <source src="<?= APP_BASE ?>/<?= h($product['file_path']) ?>" type="audio/mpeg">
                            </audio>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="feature-card">
                <span class="pill"><?= h($product['type']) ?></span>
                <h1 style="margin-top: 10px;"><?= h($product['title']) ?></h1>
                
                <p class="muted">
                    Продуцент: <strong><?= h($product['username']) ?></strong> 
                    <?= $product['bpm'] ? ' | ' . (int)$product['bpm'] . ' BPM' : '' ?>
                </p>

                <div style="margin: 20px 0; line-height: 1.6; color: var(--text); opacity: 0.9;">
                    <?= nl2br(h($product['description'] ?? 'Няма допълнително описание.')) ?>
                </div>

                <div class="purchase-box">
                    <span class="price-tag"><?= format_bgn((int)$product['price_bgn']) ?></span>
                    
                    <form method="post" action="<?= APP_BASE ?>/cart.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                        <button class="btn primary" type="submit" style="width: 100%; padding: 15px; font-weight: bold;">
                            🛒 Добави в количката
                        </button>
                    </form>
                </div>
            </div>
        </div> </div> </section>

<?php require __DIR__ . '/inc/footer.php'; ?>