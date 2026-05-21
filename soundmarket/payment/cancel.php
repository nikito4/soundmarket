<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/config.php';
require_once dirname(__DIR__) . '/inc/auth.php';
require_once dirname(__DIR__) . '/inc/functions.php';

$order_id = (int)($_GET['order'] ?? 0);

$title = 'Плащането е отказано — ' . APP_NAME;
require dirname(__DIR__) . '/inc/header.php';
?>

<div class="container" style="padding:80px 0; text-align:center;">
    <div style="font-size:4rem; margin-bottom:20px; opacity:0.6;">❌</div>
    <h1>Плащането е отказано</h1>
    <p class="muted" style="max-width:480px; margin:0 auto 20px;">
        Не беше извършено плащане. Вашата поръчка е запазена и можете да опитате отново.
    </p>

    <?php if ($order_id > 0): ?>
        <p class="muted" style="font-size:14px;">
            Поръчка <strong>#<?= $order_id ?></strong> е запазена със статус „Изчакване".
        </p>
    <?php endif; ?>

    <div style="display:flex; gap:15px; justify-content:center; margin-top:30px;">
        <a href="<?= APP_BASE ?>/checkout.php" class="btn primary">Опитай отново</a>
        <a href="<?= APP_BASE ?>/cart.php" class="btn">Обратно към количката</a>
    </div>
</div>

<?php require dirname(__DIR__) . '/inc/footer.php'; ?>
