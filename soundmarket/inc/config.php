<?php
// inc/config.php
declare(strict_types=1);

require_once __DIR__ . '/env.php';
load_env(__DIR__ . '/../.env');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'soundmarket');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

define('APP_NAME', 'SoundMarket');
define('APP_BASE', '/soundmarket');
define('BASE_URL', 'http://localhost/soundmarket');

// ── Stripe API Keys ──────────────────────────────────────────────────────────
define('STRIPE_PUBLIC_KEY',     $_ENV['STRIPE_PUBLIC_KEY']     ?? '');
define('STRIPE_SECRET_KEY',     $_ENV['STRIPE_SECRET_KEY']     ?? '');
define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');

// ── Security headers ─────────────────────────────────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
