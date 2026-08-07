<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Data Karyawan';

// Ambil pesan flash (misal setelah tambah/edit/hapus data)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$keyword = trim((string) ($_GET['q'] ?? ''));

$sql = "SELECT id, nip, nama_lengkap, email, jabatan, departemen, status
        FROM users
        WHERE role = 'karyawan'";
$params = [];

if ($keyword !== '') {
    $sql .= " AND (nip LIKE :k1 OR nama_lengkap LIKE :k2 OR email LIKE :k3 OR departemen LIKE :k4)";
    $like = '%' . $keyword . '%';
    $params['k1'] = $like;
    $params['k2'] = $like;
    $params['k3'] = $like;
    $params['k4'] = $like;
}

$sql .= ' ORDER BY nama_lengkap ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$karyawan = $stmt->fetchAll();

$totalKaryawan = count($karyawan);

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
                    Data Karyawan
                </h2>
                <p class="text-slate-500 mt-1">
                    Total <?= $totalKaryawan ?> karyawan<?= $keyword !== '' ? ' ditemukan untuk pencarian "' . e($keyword) . '"' : '' ?>.
                </p>
            </div>

            <a href="create.php"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-sm transition-colors">
                    <svg class="w-6 h-6 text-slate-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M9 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H7Zm8-1a1 1 0 0 1 1-1h1v-1a1 1 0 1 1 2 0v1h1a1 1 0 1 1 0 2h-1v1a1 1 0 1 1-2 0v-1h-1a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
                    </svg>Tambah Karyawan
            </a>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
            <form method="GET" class="flex gap-3">

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
                        placeholder="Cari nama, NIP, email, atau departemen..."
                        class="w-full rounded-lg border border-slate-300 pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <button type="submit"
                    class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-5 py-2.5 rounded-lg transition-colors">
                    Cari
                </button>

                <?php if ($keyword !== ''): ?>
                    <a href="index.php"
                        class="flex items-center px-5 py-2.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
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
                            <th class="text-left px-6 py-3 font-semibold">NIP</th>
                            <th class="text-left px-6 py-3 font-semibold">Nama</th>
                            <th class="text-left px-6 py-3 font-semibold">Email</th>
                            <th class="text-left px-6 py-3 font-semibold">Jabatan</th>
                            <th class="text-left px-6 py-3 font-semibold">Departemen</th>
                            <th class="text-left px-6 py-3 font-semibold">Status</th>
                            <th class="text-right px-6 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php if (empty($karyawan)): ?>

                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                    <?= $keyword !== '' ? 'Tidak ada karyawan yang cocok dengan pencarian.' : 'Belum ada data karyawan.' ?>
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($karyawan as $row): ?>

                                <tr class="hover:bg-slate-50 transition-colors">

                                    <td class="px-6 py-4 text-slate-600">
                                        <?= e($row['nip']) ?>
                                    </td>

                                    <td class="px-6 py-4 font-medium text-slate-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                                <?= strtoupper(substr($row['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                            <?= e($row['nama_lengkap']) ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        <?= e($row['email']) ?>
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        <?= e($row['jabatan']) ?>
                                    </td>

                                    <td class="px-6 py-4 text-slate-600">
                                        <?= e($row['departemen']) ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?= status_badge($row['status']) ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="view.php?id=<?= (int) $row['id'] ?>"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-150">
                                                Detail
                                            </a>

                                            <a href="edit.php?id=<?= (int) $row['id'] ?>"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-md bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all duration-150">
                                                Edit
                                            </a>

                                            <form action="delete.php" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus data <?= e($row['nama_lengkap']) ?>?');"
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