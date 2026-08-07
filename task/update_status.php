<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id     = (int) ($_POST['id'] ?? 0);
$status = trim((string) ($_POST['status'] ?? ''));

$allowedStatus = ['pending', 'proses', 'selesai', 'approve'];

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Token keamanan tidak valid.'];
    header('Location: index.php');
    exit;
}

if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak valid.'];
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("UPDATE tasks SET status = :status WHERE id = :id");
$stmt->execute([
    'status' => $status,
    'id'     => $id,
]);

if ($stmt->rowCount() > 0) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status tugas berhasil diperbarui.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tugas tidak ditemukan atau status tidak berubah.'];
}

header('Location: index.php');
exit;