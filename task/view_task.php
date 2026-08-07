<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Detail Tugas';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID tugas tidak valid.'];
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare(
    "SELECT id, user_id, nama_karyawan, tugas, deskripsi, status, bukti_tugas, created_at, updated_at
     FROM tasks
     WHERE id = :id"
);
$stmt->execute(['id' => $id]);
$task = $stmt->fetch();

if (!$task) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tugas tidak ditemukan.'];
    header('Location: index.php');
    exit;
}

function tugas_status_badge_view(string $status): string
{
    $map = [
        'pending' => 'bg-gray-100 text-gray-600',
        'proses'  => 'bg-amber-50 text-amber-600',
        'selesai' => 'bg-green-50 text-green-700',
    ];

    $label = [
        'pending' => 'Pending',
        'proses'  => 'Proses',
        'selesai' => 'Selesai',
    ];

    $class = $map[$status] ?? 'bg-gray-100 text-gray-600';
    $text = $label[$status] ?? ucfirst($status);

    return '<span class="px-3 py-1 text-sm font-semibold rounded-full ' . $class . '">' . e($text) . '</span>';
}

require __DIR__ . '/../includes/header.php';

?>

<div class="min-h-screen bg-gray-50">
    <main class="max-w-3xl mx-auto p-8">

        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                 <a href="index.php"
           class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded-lg shadow-sm transition-all duration-200 hover:bg-gray-100 hover:border-gray-400 hover:shadow-md hover:-translate-y-0.5">
            Kembali
            </a>

            </div>

            <!-- <div class="flex items-center gap-2">
                <a href="edit_task.php?id=<?= (int) $task['id'] ?>"
                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-colors">
                    Edit
                </a>
                <form action="delete.php" method="POST"
                    onsubmit="return confirm('Yakin ingin menghapus tugas &quot;<?= e($task['tugas']) ?>&quot;?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                        Hapus
                    </button>
                </form>
            </div> -->
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold">
                    <?= strtoupper(substr($task['nama_karyawan'], 0, 1)) ?>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-800"><?= e($task['nama_karyawan']) ?></p>
                    <p class="text-sm text-gray-400">User ID: <?= (int) $task['user_id'] ?></p>
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nama Tugas</p>
                <p class="text-gray-800"><?= e($task['tugas']) ?></p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Deskripsi</p>
                <p class="text-gray-600 whitespace-pre-line"><?= $task['deskripsi'] !== null && $task['deskripsi'] !== '' ? e($task['deskripsi']) : '<span class="text-gray-400">Tidak ada deskripsi.</span>' ?></p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</p>
                <?= tugas_status_badge_view($task['status']) ?>
            </div>

            <hr class="border-gray-100">

            <!-- Bukti Tugas -->
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Bukti Tugas</p>

                <?php if (!empty($task['bukti_tugas'])): ?>
                    <?php
                        $buktiUrl = '../uploads/bukti_tugas/' . rawurlencode((string) $task['bukti_tugas']);
                        $buktiExt = strtolower(pathinfo((string) $task['bukti_tugas'], PATHINFO_EXTENSION));
                    ?>
                    <div class="border border-gray-100 rounded-xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <?php if (in_array($buktiExt, ['jpg', 'jpeg', 'png'], true)): ?>
                                <a href="<?= e($buktiUrl) ?>" target="_blank">
                                    <img src="<?= e($buktiUrl) ?>" alt="Bukti tugas" class="w-16 h-16 object-cover rounded-lg border border-gray-100">
                                </a>
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-xs font-bold">PDF</div>
                            <?php endif; ?>
                            <div>
                                <p class="text-sm font-medium text-gray-800">File bukti tersedia</p>
                                <p class="text-xs text-gray-400">
                                    Diperbarui: <?= $task['updated_at'] ? date('d M Y, H:i', strtotime((string) $task['updated_at'])) : '-' ?>
                                </p>
                            </div>
                        </div>
                        <a href="<?= e($buktiUrl) ?>" target="_blank"
                            class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                            Lihat File
                        </a>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-400">Karyawan belum mengunggah bukti tugas.</p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Dibuat</p>
                    <p class="text-sm text-gray-600"><?= $task['created_at'] ? date('d M Y, H:i', strtotime((string) $task['created_at'])) : '-' ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Diperbarui</p>
                    <p class="text-sm text-gray-600"><?= $task['updated_at'] ? date('d M Y, H:i', strtotime((string) $task['updated_at'])) : '-' ?></p>
                </div>
            </div>

        </div>

    </main>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>