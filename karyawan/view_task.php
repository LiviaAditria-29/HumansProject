<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('karyawan');

$pageTitle = 'Detail Tugas';
$userId = (int) ($_SESSION['user_id'] ?? 0);
$id = (int) ($_GET['id'] ?? 0);

const BUKTI_UPLOAD_DIR = __DIR__ . '/../uploads/bukti_tugas/';
const BUKTI_ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'pdf'];
const BUKTI_MAX_SIZE = 5 * 1024 * 1024; // 5 MB

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID tugas tidak valid.'];
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $formAction = $_POST['form_action'] ?? 'update_status';

    if ($formAction === 'update_status') {
        $newStatus = $_POST['status'] ?? '';
        if (in_array($newStatus, ['pending', 'proses', 'selesai'], true)) {
            $upd = db()->prepare(
                "UPDATE tasks SET status = :status, updated_at = NOW() WHERE id = :id AND user_id = :user_id"
            );
            $upd->execute(['status' => $newStatus, 'id' => $id, 'user_id' => $userId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status tugas berhasil diperbarui.'];
        }
        header('Location: view_task.php?id=' . $id);
        exit;
    }

    if ($formAction === 'upload_bukti') {
        $file = $_FILES['bukti'] ?? null;

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pilih file terlebih dahulu.'];
            header('Location: view_task.php?id=' . $id);
            exit;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Upload gagal, coba lagi.'];
            header('Location: view_task.php?id=' . $id);
            exit;
        }

        if ($file['size'] > BUKTI_MAX_SIZE) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ukuran file maksimal 5MB.'];
            header('Location: view_task.php?id=' . $id);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, BUKTI_ALLOWED_EXT, true)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Format file harus JPG, PNG, atau PDF.'];
            header('Location: view_task.php?id=' . $id);
            exit;
        }

        // Pastikan tugas ini memang milik karyawan yang login
        $check = db()->prepare("SELECT id, bukti_tugas FROM tasks WHERE id = :id AND user_id = :user_id");
        $check->execute(['id' => $id, 'user_id' => $userId]);
        $existing = $check->fetch();

        if (!$existing) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tugas tidak ditemukan.'];
            header('Location: dashboard.php');
            exit;
        }

        if (!is_dir(BUKTI_UPLOAD_DIR)) {
            mkdir(BUKTI_UPLOAD_DIR, 0755, true);
        }

        $filename = 'bukti_' . $id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = BUKTI_UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menyimpan file.'];
            header('Location: view_task.php?id=' . $id);
            exit;
        }

        // Hapus file bukti lama kalau ada, supaya tidak menumpuk
        if (!empty($existing['bukti_tugas'])) {
            $oldPath = BUKTI_UPLOAD_DIR . basename((string) $existing['bukti_tugas']);
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $upd = db()->prepare(
            "UPDATE tasks SET bukti_tugas = :bukti, updated_at = NOW() WHERE id = :id AND user_id = :user_id"
        );
        $upd->execute(['bukti' => $filename, 'id' => $id, 'user_id' => $userId]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Bukti tugas berhasil diunggah.'];
        header('Location: view_task.php?id=' . $id);
        exit;
    }
}

$stmt = db()->prepare(
    "SELECT id, user_id, nama_karyawan, tugas, deskripsi, status, bukti_tugas, created_at, updated_at
     FROM tasks
     WHERE id = :id AND user_id = :user_id"
);
$stmt->execute(['id' => $id, 'user_id' => $userId]);
$task = $stmt->fetch();

if (!$task) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tugas tidak ditemukan.'];
    header('Location: dashboard.php');
    exit;
}

function tugas_status_badge_view(string $status): string
{
    $map   = ['pending' => 'bg-gray-100 text-gray-600', 'proses' => 'bg-amber-50 text-amber-600', 'selesai' => 'bg-green-50 text-green-700'];
    $label = ['pending' => 'Pending', 'proses' => 'Proses', 'selesai' => 'Selesai'];
    $class = $map[$status] ?? 'bg-gray-100 text-gray-600';
    $text  = $label[$status] ?? ucfirst($status);
    return '<span class="px-3 py-1 text-sm font-semibold rounded-full ' . $class . '">' . e($text) . '</span>';
}

require __DIR__ . '/../includes/header_karyawan.php';
?>

<main class="max-w-3xl mx-auto p-8">

    <div class="flex items-center gap-3 mb-8">
        <a href="dashboard.php" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h2 class="text-2xl font-semibold text-gray-800">Detail Tugas</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nama Tugas</p>
            <p class="text-gray-800 text-lg font-medium"><?= e($task['tugas']) ?></p>
        </div>

        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Deskripsi</p>
            <p class="text-gray-600 whitespace-pre-line">
                <?= $task['deskripsi'] !== null && $task['deskripsi'] !== '' ? e($task['deskripsi']) : '<span class="text-gray-400">Tidak ada deskripsi.</span>' ?>
            </p>
        </div>

        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Status Saat Ini</p>
            <?= tugas_status_badge_view($task['status']) ?>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-2">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Diberikan</p>
                <p class="text-sm text-gray-600"><?= $task['created_at'] ? date('d M Y, H:i', strtotime((string) $task['created_at'])) : '-' ?></p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Diperbarui</p>
                <p class="text-sm text-gray-600"><?= $task['updated_at'] ? date('d M Y, H:i', strtotime((string) $task['updated_at'])) : '-' ?></p>
            </div>
        </div>

        <hr class="border-gray-100">

        <form action="view_task.php?id=<?= (int) $task['id'] ?>" method="POST" class="flex items-center gap-3">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="update_status">
            <label class="text-sm font-medium text-gray-600">Ubah Status:</label>
            <select name="status" class="border border-gray-200 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="proses" <?= $task['status'] === 'proses' ? 'selected' : '' ?>>Proses</option>
                <!-- <option value="selesai" <?= $task['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option> -->
            </select>
            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                Simpan
            </button>
        </form>

        <hr class="border-gray-100">

        <!-- Bukti Tugas -->
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Bukti Tugas</p>

            <?php if (!empty($task['bukti_tugas'])): ?>
                <?php
                    $buktiUrl = '../uploads/bukti_tugas/' . rawurlencode((string) $task['bukti_tugas']);
                    $buktiExt = strtolower(pathinfo((string) $task['bukti_tugas'], PATHINFO_EXTENSION));
                ?>
                <div class="mb-4 border border-gray-100 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <?php if (in_array($buktiExt, ['jpg', 'jpeg', 'png'], true)): ?>
                            <img src="<?= e($buktiUrl) ?>" alt="Bukti tugas" class="w-16 h-16 object-cover rounded-lg border border-gray-100">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-xs font-bold">PDF</div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm font-medium text-gray-800">File bukti sudah diunggah</p>
                            <p class="text-xs text-gray-400">
                                <?= $task['updated_at'] ? date('d M Y, H:i', strtotime((string) $task['updated_at'])) : '' ?>
                            </p>
                        </div>
                    </div>
                    <a href="<?= e($buktiUrl) ?>" target="_blank"
                        class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                        Lihat File
                    </a>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-400 mb-4">Belum ada bukti tugas yang diunggah.</p>
            <?php endif; ?>

            <form action="view_task.php?id=<?= (int) $task['id'] ?>" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="upload_bukti">
                <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" required
                    class="text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors whitespace-nowrap">
                    Unggah
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2">Format: JPG, PNG, PDF, Doc, atau xlsx. Maksimal 5MB.</p>
        </div>

    </div>

</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>