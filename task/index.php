<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Data Tugas Karyawan';

// Ambil pesan flash (misal setelah tambah/edit/hapus data)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$keyword = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$allowedStatus = ['pending', 'proses', 'selesai', 'approve'];

$sql = "SELECT id, user_id, nama_karyawan, tugas, deskripsi, deadline, status, created_at, updated_at
        FROM tasks
        WHERE 1=1";
$params = [];

if ($keyword !== '') {
    $sql .= " AND (nama_karyawan LIKE :k1 OR tugas LIKE :k2 OR deskripsi LIKE :k3)";
    $like = '%' . $keyword . '%';
    $params['k1'] = $like;
    $params['k2'] = $like;
    $params['k3'] = $like;
}

if ($statusFilter !== '' && in_array($statusFilter, $allowedStatus, true)) {
    $sql .= " AND status = :status";
    $params['status'] = $statusFilter;
}

$sql .= ' ORDER BY created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$tugasList = $stmt->fetchAll();

$totalTugas = count($tugasList);

/**
 * Badge status untuk tugas.
 */
function tugas_status_badge(string $status): string
{
    $map = [
        'pending' => 'bg-slate-100 text-slate-600',
        'proses'  => 'bg-amber-50 text-amber-600',
        'selesai' => 'bg-emerald-50 text-emerald-700',
    ];

    $label = [
        'pending' => 'Pending',
        'proses'  => 'Proses',
        'selesai' => 'Selesai',
        'approve' => 'Approve',
    ];

    $class = $map[$status] ?? 'bg-slate-100 text-slate-600';
    $text = $label[$status] ?? ucfirst($status);

    return '<span class="px-2.5 py-1 text-xs font-semibold rounded-full ' . $class . '">' . e($text) . '</span>';
}

/**
 * Tampilan deadline: highlight merah kalau sudah lewat dan tugas belum selesai.
 */
function tugas_deadline_display(?string $deadline, string $status): string
{
    if (empty($deadline)) {
        return '<span class="text-slate-300">-</span>';
    }

    $ts        = strtotime($deadline);
    $isLewat   = $ts < strtotime('today') && $status !== 'selesai';
    $class     = $isLewat ? 'text-red-600 font-semibold' : 'text-slate-500';
    $label     = date('d M Y', $ts);

    return '<span class="' . $class . '">' . e($label) . '</span>' .
           ($isLewat ? ' <span class="text-[10px] text-red-500 font-semibold uppercase">Lewat</span>' : '');
}

require __DIR__ . '/../includes/header.php';

?>

<div class="min-h-screen bg-transparent">
    <!-- Main -->
    <main class="max-w-7xl mx-auto p-8">

        <?php if ($flash): ?>
            <div class="mb-6 rounded-lg px-4 py-3 border <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700' ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-slate-800">
                    Data Tugas Karyawan
                </h2>
                <p class="text-slate-500 mt-1">
                    Total <?= $totalTugas ?> tugas<?= $keyword !== '' ? ' ditemukan untuk pencarian "' . e($keyword) . '"' : '' ?>.
                </p>
            </div>

            <a href="create_task.php"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-sm transition-colors">
                    <svg class="w-6 h-6 text-slate-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M9 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H7Zm8-1a1 1 0 0 1 1-1h1v-1a1 1 0 1 1 2 0v1h1a1 1 0 1 1 0 2h-1v1a1 1 0 1 1-2 0v-1h-1a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
                    </svg>Tambah Tugas
            </a>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">

                <div class="relative flex-1">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/>
                    </svg>

                    <input
                        type="text"
                        name="q"
                        value="<?= e($keyword) ?>"
                        placeholder="Cari nama karyawan, tugas, atau deskripsi..."
                        class="w-full rounded-lg border border-slate-300 pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <select name="status"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:w-48">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="proses" <?= $statusFilter === 'proses' ? 'selected' : '' ?>>Proses</option>
                    <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>

                <button type="submit"
                    class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-5 py-2.5 rounded-lg transition-colors">
                    Cari
                </button>

                <?php if ($keyword !== '' || $statusFilter !== ''): ?>
                    <a href="index.php"
                        class="flex items-center justify-center px-5 py-2.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                        Reset
                    </a>
                <?php endif; ?>

            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100">

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="text-left px-6 py-3 font-semibold">Karyawan</th>
                            <th class="text-left px-6 py-3 font-semibold">Tugas</th>
                            <th class="text-left px-6 py-3 font-semibold">Deskripsi</th>
                            <th class="text-left px-6 py-3 font-semibold">Status</th>
                            <th class="text-left px-6 py-3 font-semibold whitespace-nowrap">Deadline</th>
                            <th class="text-left px-6 py-3 font-semibold">Dibuat</th>
                            <th class="text-right px-6 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php if (empty($tugasList)): ?>

                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                    <?= ($keyword !== '' || $statusFilter !== '') ? 'Tidak ada tugas yang cocok dengan pencarian.' : 'Belum ada data tugas.' ?>
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($tugasList as $row): ?>

                                <tr class="hover:bg-slate-50 transition-colors">

                                    <td class="px-6 py-4 font-medium text-slate-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                                <?= strtoupper(substr($row['nama_karyawan'], 0, 1)) ?>
                                            </div>
                                            <?= e($row['nama_karyawan']) ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        <?= e($row['tugas']) ?>
                                    </td>

                                    <td class="px-6 py-4 text-slate-500 max-w-xs truncate">
                                        <?= e((string) ($row['deskripsi'] ?? '-')) ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?= tugas_status_badge($row['status']) ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= tugas_deadline_display($row['deadline'] ?? null, $row['status']) ?>
                                    </td>

                                    <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                        <?= $row['created_at'] ? date('d M Y', strtotime((string) $row['created_at'])) : '-' ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="view_task.php?id=<?= (int) $row['id'] ?>"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-150">
                                                Detail
                                            </a>

                                            <a href="edit_task.php?id=<?= (int) $row['id'] ?>"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-md bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all duration-150">
                                                Revisi
                                            </a>

                                            <form action="update_status.php" method="POST" class="inline-flex"
                                                onsubmit="return confirm('Approve tugas ini?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="status" value="approve">
                                                <button type="submit"
                                                    class="px-2.5 py-1 text-xs font-semibold rounded-md bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all duration-150">
                                                    Approve
                                                </button>
                                            </form>

                                            <form action="delete_task.php" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus tugas &quot;<?= e($row['tugas']) ?>&quot; milik <?= e($row['nama_karyawan']) ?>?');"
                                                class="inline-flex">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <button type="submit"
                                                    class="px-2.5 py-1 text-xs font-semibold rounded-md bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all duration-150">
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>