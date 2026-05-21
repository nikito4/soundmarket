<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    log_action(admin_db(), admin_id(), 'logout');
    admin_logout();
}

header('Location: /soundmarket/admin/login.php');
exit;
