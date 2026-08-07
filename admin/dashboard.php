<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Dashboard Admin';
require __DIR__ . '/../includes/header.php';

// Statistik
$totalKaryawan = (int) db()->query("SELECT COUNT(*) FROM users WHERE role='karyawan'")->fetchColumn();
$totalAktif = (int) db()->query("SELECT COUNT(*) FROM users WHERE role='karyawan' AND status='aktif'")->fetchColumn();
$totalNonaktif = (int) db()->query("SELECT COUNT(*) FROM users WHERE role='karyawan' AND status='nonaktif'")->fetchColumn();

$persenAktif = $totalKaryawan > 0 ? round(($totalAktif / $totalKaryawan) * 100) : 0;

// Ambil 5 karyawan terbaru
$stmt = db()->query("
    SELECT nama_lengkap,email,jabatan,departemen,status
    FROM users
    WHERE role='karyawan'
    ORDER BY id DESC
    LIMIT 5
");

$karyawan = $stmt->fetchAll();

?>

<div class="min-h-screen bg-transparent">

    

    <!-- Main -->
    <main class="max-w-7xl mx-auto p-6 md:p-8 space-y-6">

        <!-- Header sambutan -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 p-6 md:p-8 text-white shadow-sm">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute -right-4 bottom-0 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="relative">
                <h1 class="text-2xl md:text-3xl font-bold mt-0.5">Selamat Datang, <?= e($_SESSION['nama_lengkap']) ?> 👋</h1>
                <p class="text-indigo-100 text-sm mt-2">
                    Berikut ringkasan data karyawan hari ini &mdash; <?= $totalKaryawan ?> karyawan, <?= $persenAktif ?>% di antaranya aktif.
                </p>
            </div>
        </div>

        <!-- Statistik -->

        <div class="grid md:grid-cols-3 gap-6 mb-8">

            <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium">
                            Total Karyawan
                        </p>
                        <h2 class="text-4xl font-bold mt-3 text-slate-800">
                            <?= $totalKaryawan ?>
                        </h2>
                        <p class="text-xs text-slate-400 mt-2">Seluruh data karyawan</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center text-xl">
                        <svg class="w-6 h-6 text-slate-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3.05A2.5 2.5 0 1 1 9 5.5M19.5 17h.5a1 1 0 0 0 1-1 3 3 0 0 0-3-3h-1m0-3.05a2.5 2.5 0 1 0-2-4.45m.5 13.5h-7a1 1 0 0 1-1-1 3 3 0 0 1 3-3h3a3 3 0 0 1 3 3 1 1 0 0 1-1 1Zm-1-9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/>
                            </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium">
                            Karyawan Aktif
                        </p>
                        <h2 class="text-4xl font-bold mt-3 text-emerald-600">
                            <?= $totalAktif ?>
                        </h2>
                        <p class="text-xs text-slate-400 mt-2"><?= $persenAktif ?>% dari total karyawan</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center text-xl">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            fill="#198754"
                            viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0z"/>
                        <path fill="#fff"
                                d="M11.03 5.97a.75.75 0 0 1 0 1.06L7.53 10.53a.75.75 0 0 1-1.06 0L4.97 9.03a.75.75 0 1 1 1.06-1.06L7 8.94l2.97-2.97a.75.75 0 0 1 1.06 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium">
                            Karyawan Nonaktif
                        </p>
                        <h2 class="text-4xl font-bold mt-3 text-red-600">
                            <?= $totalNonaktif ?>
                        </h2>
                        <p class="text-xs text-slate-400 mt-2">Perlu ditinjau kembali</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center text-xl">
                        <svg xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#DC3545"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M4.93 4.93l14.14 14.14"/>
                    </svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- Table -->

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100">

            <div class="p-6 border-b flex items-center justify-between">

                <h2 class="text-lg font-bold text-slate-800">
                    Karyawan Terbaru
                </h2>

                <!-- <a href="../karyawan/index.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    Lihat semua &rarr;
                </a> -->

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide">

                        <tr>

                            <th class="text-left px-6 py-3 font-semibold">
                                Nama
                            </th>

                            <th class="text-left px-6 py-3 font-semibold">
                                Email
                            </th>

                            <th class="text-left px-6 py-3 font-semibold">
                                Jabatan
                            </th>

                            <th class="text-left px-6 py-3 font-semibold">
                                Departemen
                            </th>

                            <th class="text-left px-6 py-3 font-semibold">
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php if (empty($karyawan)): ?>

                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                Belum ada data karyawan.
                            </td>
                        </tr>

                        <?php else: ?>

                        <?php foreach($karyawan as $row): ?>

                        <tr class="hover:bg-slate-50 transition-colors">

                            <td class="px-6 py-4 font-medium text-slate-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                        <?= strtoupper(substr($row['nama_lengkap'],0,1)) ?>
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

                                <?php if($row['status']=='aktif'): ?>

                                    <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>

                                <?php else: ?>

                                    <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Nonaktif
                                    </span>

                                <?php endif; ?>

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