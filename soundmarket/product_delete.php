<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare("DELETE FROM products WHERE id=? AND owner_id=?");
$stmt->execute([$id, current_user_id()]);

header('Location: ' . APP_BASE . '/my_products.php');
exit;