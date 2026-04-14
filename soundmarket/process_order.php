<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';

require_login();
$pdo = db();
$user_id = current_user_id();

// 1. Вземаме всичко от количката
$stmt = $pdo->prepare("SELECT product_id FROM cart_items WHERE user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

if ($items) {
    // 2. Записваме поръчката (симулираме плащане)
    foreach ($items as $item) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, status, created_at) VALUES (?, ?, 'paid', NOW())");
        $stmt->execute([$user_id, $item['product_id']]);
    }

    // 3. Изпразваме количката
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
}

header('Location: order_success.php');
exit;