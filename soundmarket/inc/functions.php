<?php
// inc/functions.php
declare(strict_types=1);

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function format_eur(int $cents): string {
  return '€' . number_format($cents / 100, 2, '.', ' ');
}


function redirect(string $path): never {
  header('Location: ' . $path);
  exit;
}

function post(string $key, string $default = ''): string {
  return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function get_int(string $key, int $default = 0): int {
  return isset($_GET[$key]) ? (int)$_GET[$key] : $default;
}

function notify(PDO $pdo, int $user_id, string $type,
                string $message, ?string $link = null): void {
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, message, link)
        VALUES (?, ?, ?, ?)
    ")->execute([$user_id, $type, $message, $link]);
}

function render_pagination(int $total, int $per_page,
                           int $current, string $base_url): string {
    $pages = (int)ceil($total / $per_page);
    if ($pages <= 1) return '';
    $sep = str_contains($base_url, '?') ? '&' : '?';
    $out = '<div class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $url    = $base_url . $sep . 'page=' . $i;
        $active = $i === $current ? ' active' : '';
        $out   .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    $out .= '</div>';
    return $out;
}