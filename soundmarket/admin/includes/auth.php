<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function admin_require_auth(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: /soundmarket/admin/login.php');
        exit;
    }
}

function admin_login(int $id, string $username, string $role): void {
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $id;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_role']     = $role;
}

function admin_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function admin_id(): int       { return (int)($_SESSION['admin_id']       ?? 0); }
function admin_username(): string { return (string)($_SESSION['admin_username'] ?? ''); }
function admin_role(): string  { return (string)($_SESSION['admin_role']   ?? ''); }
function is_superadmin(): bool { return admin_role() === 'superadmin'; }
