<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Token keamanan tidak valid.'];
    header('Location: index.php');
    exit;
}

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID tugas tidak valid.'];
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare("DELETE FROM tasks WHERE id = :id");
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Tugas berhasil dihapus.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tugas tidak ditemukan atau sudah dihapus.'];
}

header('Location: index.php');
exit;