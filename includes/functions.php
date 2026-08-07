<?php

declare(strict_types=1);

function url(string $path = ' '): string 
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Token keamanan tidak valid. Silakan kembali dan coba lagi.');
    }
}

function old(string $key, string $default = ''): string
{
    return e((string) ($_POST[$key] ?? $default));
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d M Y', strtotime($date));
}

function status_badge(string $status): string
{
    $class = $status === 'aktif'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-red-200 bg-red-50 text-red-700';

    return '<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold ' . $class . '">'
        . e(ucfirst($status))
        . '</span>';    
}
