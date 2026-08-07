<?php

declare(strict_types=1);

function is_logged_in(): bool 
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'nama_lengkap' => (string) ($_SESSION['nama_lengkap'] ?? ''),
        'role' => (string) $_SESSION['role'],
    ];
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('danger', 'Silahkan login terlebih dahulu.');
        redirect('login.php');
    }
}

function require_role(string $role): void 
{
    require_login();

    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Akses ditolak. Anda tidak memiliki izin untuk membuka halaman ini.');
    }
}

function redirect_by_role(): never
{
    if (($_SESSION['role'] ?? '') === 'admin') {
        redirect('admin/dashboard.php');
    }

    redirect('karyawan/dashboard.php');
}