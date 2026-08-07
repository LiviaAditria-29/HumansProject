<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session di browser (jika ada)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hancurkan session di server
session_destroy();

// Mulai session baru hanya untuk menyimpan pesan flash
session_start();
set_flash('success', 'Anda telah berhasil logout.');

redirect('login.php');