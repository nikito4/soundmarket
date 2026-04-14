<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

// Задължително изискваме вход
require_login();

$pdo = db();
$user_id = current_user_id();

/* ======================================================
   ЛОГИКА ЗА ОБРАБОТКА НА ЗАЯВКИТЕ (POST)
   ====================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($product_id > 0) {
        // 1. ДОБАВЯНЕ НА ПРОДУКТ (от beats.php или services.php)
        if ($action === 'add') {
            $check = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $check->execute([$user_id, $product_id]);
            $existing = $check->fetch();

            if ($existing) {
                $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?")
                    ->execute([$existing['id']]);
            } else {
                $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)")
                    ->execute([$user_id, $product_id]);
            }
        }
        
        // 2. ОБНОВЯВАНЕ НА КОЛИЧЕСТВО (+ / -)
        elseif ($action === 'update') {
            $new_qty = (int)$_POST['quantity'];
            if ($new_qty > 0) {
                $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?")
                    ->execute([$new_qty, $user_id, $product_id]);
            } else {
                // Ако количеството стане 0, изтриваме продукта
                $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?")
                    ->execute([$user_id, $product_id]);
            }
        }
        
        // 3. ПРЕМАХВАНЕ НА ПРОДУКТ
        elseif ($action === 'remove') {
            $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?")
                ->execute([$user_id, $product_id]);
        }
    }

    // След всяко действие пренасочваме към същия файл, за да изчистим POST данните
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ======================================================
   ИЗВЛИЧАНЕ НА ДАННИ ЗА ПОКАЗВАНЕ
   ====================================================== */
$stmt = $pdo->prepare("
    SELECT ci.product_id, ci.quantity, p.title, p.price_bgn, p.cover_path, p.type, p.genre, p.bpm
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
    ORDER BY ci.created_at DESC
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['price_bgn'] * $item['quantity'];
}

$title = 'Количка — ' . APP_NAME;
require __DIR__ . '/inc/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; margin: 0; letter-spacing: -0.02em;">Твоята количка</h1>
        <p class="muted">Управлявай избраните продукти и завърши поръчката си.</p>
    </div>

    <?php if (!$items): ?>
        <div class="card" style="padding: 80px 20px; text-align: center; border: 2px dashed var(--border);">
            <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;">🛒</div>
            <h2 style="color: var(--muted);">Количката ти е празна</h2>
            <p class="muted" style="margin-bottom: 30px;">Изглежда още не си избрал нищо за своята следваща продукция.</p>
            <a href="beats.php" class="btn primary" style="padding: 15px 40px;">Към каталога</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px; align-items: start;">
            
            <div style="display: grid; gap: 15px;">
                <?php foreach ($items as $item): ?>
                    <div class="card" style="display: flex; align-items: center; padding: 20px; gap: 20px;">
                        
                        <?php 
                            $cover = !empty($item['cover_path']) ? APP_BASE.'/'.$item['cover_path'] : APP_BASE.'/uploads/blog-vinyl-lp.800x0.jpg';
                        ?>
                        <div style="width: 90px; height: 90px; border-radius: 12px; background-image: url('<?= h($cover) ?>'); background-size: cover; background-position: center; flex-shrink: 0;"></div>
                        
                        <div style="flex-grow: 1;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                <h3 style="margin: 0; font-size: 1.15rem;"><?= h($item['title']) ?></h3>
                                <span class="pill" style="font-size: 10px;"><?= strtoupper(h($item['type'])) ?></span>
                            </div>
                            <div class="muted" style="font-size: 0.85rem;">
                                <?= $item['genre'] ? '📁 '.h($item['genre']) : '' ?> 
                                <?= $item['bpm'] ? ' • 🥁 '.(int)$item['bpm'].' BPM' : '' ?>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; background: var(--surface-2); border-radius: 10px; border: 1px solid var(--border); overflow: hidden;">
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
                                <button type="submit" class="btn" style="border:none; border-radius:0; padding: 8px 15px; background:transparent;">−</button>
                            </form>
                            
                            <span style="min-width: 30px; text-align: center; font-weight: bold;"><?= $item['quantity'] ?></span>
                            
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                <button type="submit" class="btn" style="border:none; border-radius:0; padding: 8px 15px; background:transparent;">+</button>
                            </form>
                        </div>

                        <div style="text-align: right; min-width: 130px;">
                            <div style="font-size: 1.25rem; font-weight: 800; color: var(--brand);">
                                <?= format_bgn((int)$item['price_bgn'] * $item['quantity']) ?>
                            </div>
                            <form method="POST" style="margin-top: 5px;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" style="background:none; border:none; color:#ff4d4d; font-size: 11px; font-weight: 700; cursor:pointer; text-transform: uppercase;">Премахни</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="card" style="padding: 30px; position: sticky; top: 100px; border: 1px solid var(--border);">
                <h3 style="margin-top: 0; margin-bottom: 25px;">Поръчка</h3>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span class="muted">Междинна сума:</span>
                    <span><?= format_bgn((int)$total) ?></span>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span class="muted">Данъци:</span>
                    <span>0.00 лв.</span>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border); margin: 20px 0;">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <span style="font-weight: 700;">Общо:</span>
                    <span style="font-size: 1.8rem; font-weight: 900; color: var(--brand);"><?= format_bgn((int)$total) ?></span>
                </div>

                <a href="checkout.php" class="btn primary" style="width: 100%; padding: 18px; text-align: center; font-size: 1.1rem; justify-content: center; box-shadow: 0 10px 20px rgba(124,58,237,0.2);">
                    Завърши поръчката
                </a>
                
                <div style="margin-top: 20px; display: flex; align-items: center; gap: 10px; justify-content: center; font-size: 12px;" class="muted">
                    <span>🛡️</span> Сигурно плащане (Демо)
                </div>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>