<?php
// inc/auth.php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in(): bool {
  return isset($_SESSION['user_id']);
}

function current_user_id(): ?int {
  return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

function require_login(): void {
  if (!is_logged_in()) {
    header('Location: ' . APP_BASE . '/login.php');
    exit;
  }
}

function login_user(int $id, string $username): void {
  $_SESSION['user_id'] = $id;
  $_SESSION['username'] = $username;
}

function logout_user(): void {
  $_SESSION = [];
  session_destroy();
}

function current_username(): string {
  return (string)($_SESSION['username'] ?? '');
}