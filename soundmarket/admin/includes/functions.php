<?php
declare(strict_types=1);

/* ── Output ── */
function h(mixed $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_eur(int $cents): string {
    return '€' . number_format($cents / 100, 2, '.', ' ');
}


/* ── CSRF ── */
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* ── Activity log ── */
function log_action(PDO $pdo, int $admin_id, string $action, ?string $table = null, ?int $target_id = null): void {
    $ip = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: null;
    $pdo->prepare(
        "INSERT INTO activity_logs (admin_id, action, target_table, target_id, ip_address) VALUES (?, ?, ?, ?, ?)"
    )->execute([$admin_id, $action, $table, $target_id, $ip]);
}

/* ── Badges ── */
function status_badge(string $status): string {
    $map = [
        'created'   => ['Изчакване', 'badge-gray'],
        'paid'      => ['Платено',   'badge-green'],
        'delivered' => ['Доставено', 'badge-blue'],
        'cancelled' => ['Отказано',  'badge-red'],
        ''          => ['Изчакване', 'badge-gray'],
    ];
    [$label, $cls] = $map[$status] ?? [(string)$status, 'badge-gray'];
    return '<span class="badge ' . $cls . '">' . h($label) . '</span>';
}

function type_badge(string $type): string {
    $map = [
        'beat'    => ['Beat',    'badge-blue'],
        'music'   => ['Music',   'badge-green'],
        'service' => ['Service', 'badge-amber'],
        'digital' => ['Digital', 'badge-purple'],
    ];
    [$label, $cls] = $map[$type] ?? [(string)$type, 'badge-gray'];
    return '<span class="badge ' . $cls . '">' . h($label) . '</span>';
}

/* ── Notifications ── */
function notify(PDO $pdo, int $user_id, string $type,
                string $message, ?string $link = null): void {
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, message, link)
        VALUES (?, ?, ?, ?)
    ")->execute([$user_id, $type, $message, $link]);
}

/* ── Pagination ── */
function paginate(int $total, int $page, int $per_page = 20): array {
    $total_pages = max(1, (int)ceil($total / $per_page));
    $page        = max(1, min($page, $total_pages));
    $offset      = ($page - 1) * $per_page;
    return compact('total_pages', 'page', 'per_page', 'offset');
}

function pagination_links(int $total_pages, int $current_page, string $url_pattern): string {
    if ($total_pages <= 1) return '';
    $html = '<div class="pagination">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i === $current_page ? ' active' : '';
        $url    = sprintf($url_pattern, $i);
        $html  .= '<a href="' . h($url) . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    $html .= '</div>';
    return $html;
}
