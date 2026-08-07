<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Tambah Tugas';

$errors = [];
$old = [
    'user_id'   => '',
    'tugas'     => '',
    'deskripsi' => '',
    'deadline'  => '',
    'status'    => 'pending',
];

// Ambil daftar karyawan untuk dropdown
$karyawanStmt = db()->query("SELECT id, nama_lengkap FROM users WHERE role = 'karyawan' ORDER BY nama_lengkap ASC");
$karyawanList = $karyawanStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid, silakan coba lagi.';
    }

    $old['user_id']   = trim((string) ($_POST['user_id'] ?? ''));
    $old['tugas']     = trim((string) ($_POST['tugas'] ?? ''));
    $old['deskripsi'] = trim((string) ($_POST['deskripsi'] ?? ''));
    $old['deadline']  = trim((string) ($_POST['deadline'] ?? ''));
    $old['status']    = trim((string) ($_POST['status'] ?? 'pending'));

    $allowedStatus = ['pending', 'proses', 'selesai'];

    if ($old['user_id'] === '' || !ctype_digit($old['user_id'])) {
        $errors[] = 'Karyawan wajib dipilih.';
    }

    if ($old['tugas'] === '') {
        $errors[] = 'Nama tugas wajib diisi.';
    } elseif (mb_strlen($old['tugas']) > 255) {
        $errors[] = 'Nama tugas maksimal 255 karakter.';
    }

    if ($old['deadline'] !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $old['deadline']);
        if (!$d || $d->format('Y-m-d') !== $old['deadline']) {
            $errors[] = 'Format deadline tidak valid.';
        }
    }

    if (!in_array($old['status'], $allowedStatus, true)) {
        $errors[] = 'Status tidak valid.';
    }

    $namaKaryawan = null;

    if (empty($errors)) {
        $cekKaryawan = db()->prepare("SELECT nama_lengkap FROM users WHERE id = :id AND role = 'karyawan'");
        $cekKaryawan->execute(['id' => $old['user_id']]);
        $namaKaryawan = $cekKaryawan->fetchColumn();

        if ($namaKaryawan === false) {
            $errors[] = 'Karyawan yang dipilih tidak ditemukan.';
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare(
            "INSERT INTO tasks (user_id, nama_karyawan, tugas, deskripsi, deadline, status)
             VALUES (:user_id, :nama_karyawan, :tugas, :deskripsi, :deadline, :status)"
        );

        $stmt->execute([
            'user_id'       => $old['user_id'],
            'nama_karyawan' => $namaKaryawan,
            'tugas'         => $old['tugas'],
            'deskripsi'     => $old['deskripsi'] !== '' ? $old['deskripsi'] : null,
            'deadline'      => $old['deadline'] !== '' ? $old['deadline'] : null,
            'status'        => $old['status'],
        ]);

        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => 'Tugas berhasil ditambahkan.',
        ];

        header('Location: index.php');
        exit;
    }
}

require __DIR__ . '/../includes/header.php';

?>

<div class="min-h-screen bg-transparent">

    <main class="max-w-3xl mx-auto p-8">

        <div class="flex items-center gap-3 mb-8">
            <a href="index.php" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="text-2xl font-semibold text-slate-800">Tambah Tugas</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 rounded-lg px-4 py-3 border bg-red-50 border-red-200 text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">

            <form method="POST" class="space-y-5">

                <?= csrf_field() ?>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Karyawan</label>
                    <select name="user_id" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">-- Pilih Karyawan --</option>
                        <?php foreach ($karyawanList as $k): ?>
                            <option value="<?= (int) $k['id'] ?>" <?= $old['user_id'] === (string) $k['id'] ? 'selected' : '' ?>>
                                <?= e($k['nama_lengkap']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Tugas</label>
                    <input type="text" name="tugas" value="<?= e($old['tugas']) ?>" required maxlength="255"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="4"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= e($old['deskripsi']) ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Deadline</label>
                        <input type="date" name="deadline" value="<?= e($old['deadline']) ?>"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                        <select name="status"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="pending" <?= $old['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="proses" <?= $old['status'] === 'proses' ? 'selected' : '' ?>>Proses</option>
                            <option value="selesai" <?= $old['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="index.php"
                        class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-sm transition-colors">
                        Simpan Tugas
                    </button>
                </div>

            </form>

        </div>

    </main>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>