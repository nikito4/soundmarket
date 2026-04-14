<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/db.php';

require_login();
$user_id = current_user_id();
$product_id = (int)($_GET['id'] ?? 0);

$pdo = db();

// Проверка: Потребителят наистина ли е платил за този продукт?
$stmt = $pdo->prepare("
    SELECT p.file_path, p.title 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.user_id = ? AND o.product_id = ? AND o.status = 'paid'
");
$stmt->execute([$user_id, $product_id]);
$file = $stmt->fetch();

if ($file && !empty($file['file_path'])) {
    $fullPath = __DIR__ . '/' . $file['file_path'];

    if (file_exists($fullPath)) {
        // Форсираме свалянето
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}

exit("Нямате достъп до този файл или той не съществува.");