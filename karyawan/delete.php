<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

// Hanya menerima request POST (dikirim dari form hapus di index.php / view.php)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('karyawan/index.php');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Data karyawan tidak ditemukan.');
    redirect('karyawan/index.php');
}

$stmt = db()->prepare("SELECT id, nama_lengkap FROM users WHERE id = :id AND role = 'karyawan' LIMIT 1");
$stmt->execute(['id' => $id]);
$karyawan = $stmt->fetch();

if (!$karyawan) {
    set_flash('danger', 'Data karyawan tidak ditemukan.');
    redirect('karyawan/index.php');
}

$stmt = db()->prepare("DELETE FROM users WHERE id = :id AND role = 'karyawan'");
$stmt->execute(['id' => $id]);

set_flash('success', 'Data karyawan "' . $karyawan['nama_lengkap'] . '" berhasil dihapus.');
redirect('karyawan/index.php');