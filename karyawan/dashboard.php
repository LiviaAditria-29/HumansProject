<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('karyawan');

$pageTitle = 'Dashboard Karyawan';
$userId = (int) ($_SESSION['user_id'] ?? 0);

// Ambil data profil karyawan yang login (tabel users, gabungan user + karyawan)
$stmt = db()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$karyawan = $stmt->fetch();

// Ambil tugas milik karyawan ini
$stmtTugas = db()->prepare(
    "SELECT id, tugas, deskripsi, status, deadline, created_at, updated_at
     FROM tasks
     WHERE user_id = :user_id
     ORDER BY created_at DESC"
);
$stmtTugas->execute(['user_id' => $userId]);
$tasks = $stmtTugas->fetchAll();

// Ringkasan status. Tugas dengan status 'approved' dihitung sebagai 'selesai'.
// Status dari database bisa bervariasi penulisannya (mis. "Approve", "Approved",
// "approved"), jadi disamakan dulu (lowercase + trim) sebelum dicocokkan.
function tugas_status_normal(string $status): string
{
    $status = strtolower(trim($status));
    $selesaiVariants = ['selesai', 'approve', 'approved', 'disetujui'];
    return in_array($status, $selesaiVariants, true) ? 'selesai' : $status;
}

function tugas_is_selesai(string $status): bool
{
    return tugas_status_normal($status) === 'selesai';
}

$summary = ['pending' => 0, 'proses' => 0, 'selesai' => 0];
foreach ($tasks as $t) {
    $status = tugas_status_normal((string) $t['status']);
    if (isset($summary[$status])) {
        $summary[$status]++;
    }
}

$totalTugas = count($tasks);
$progressPct = $totalTugas > 0 ? (int) round(($summary['selesai'] / $totalTugas) * 100) : 0;

// Hitung tugas yang lewat deadline (belum selesai/approved & tanggalnya sudah lampau)
$overdueCount = 0;
foreach ($tasks as $t) {
    if (!empty($t['deadline']) && !tugas_is_selesai((string) $t['status'])) {
        if (strtotime((string) $t['deadline']) < strtotime('today')) {
            $overdueCount++;
        }
    }
}

$nama_lengkap = $karyawan['nama_lengkap'] ?? ($_SESSION['nama_lengkap'] ?? 'Karyawan');
$jamSekarang  = (int) date('H');
if ($jamSekarang < 11) {
    $sapaan = 'Selamat pagi';
} elseif ($jamSekarang < 15) {
    $sapaan = 'Selamat siang';
} elseif ($jamSekarang < 18) {
    $sapaan = 'Selamat sore';
} else {
    $sapaan = 'Selamat malam';
}

function tugas_status_badge(string $status): string
{
    $normal = tugas_status_normal($status);
    $map = [
        'pending' => 'bg-slate-100 text-slate-600',
        'proses'  => 'bg-amber-50 text-amber-600',
        'selesai' => 'bg-emerald-50 text-emerald-600',
    ];
    $icon = $normal === 'selesai' ? '&#10003;' : '&#9679;';
    // Label tetap menampilkan teks asli dari database (mis. "Approve"),
    // hanya warna & ikon yang mengikuti kategori hasil normalisasi.
    $label = [
        'pending' => 'Pending',
        'proses'  => 'Proses',
        'selesai' => 'Selesai',
    ];
    $class = $map[$normal] ?? 'bg-slate-100 text-slate-600';
    $text  = $label[$normal] ?? ucfirst($status);
    return '<span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full ' . $class . '">'
         . '<span class="text-[10px]">' . $icon . '</span>' . e($text) . '</span>';
}

function tugas_status_bar(string $status): string
{
    $map = [
        'pending' => 'bg-slate-300',
        'proses'  => 'bg-amber-400',
        'selesai' => 'bg-emerald-500',
    ];
    return $map[tugas_status_normal($status)] ?? 'bg-slate-300';
}

function tugas_deadline_display(?string $deadline, string $status): string
{
    if (empty($deadline)) {
        return '<span class="text-slate-300">-</span>';
    }

    $ts      = strtotime($deadline);
    $isLewat = $ts < strtotime('today') && !tugas_is_selesai($status);
    $class   = $isLewat ? 'text-red-600 font-semibold' : 'text-slate-500';
    $label   = date('d M Y', $ts);

    return '<span class="' . $class . '">' . e($label) . '</span>' .
           ($isLewat ? ' <span class="block text-[10px] text-red-500 font-semibold uppercase">Lewat</span>' : '');
}

require __DIR__ . '/../includes/header_karyawan.php';
?>

<main class="max-w-5xl mx-auto p-6 md:p-8 space-y-6">

    <!-- Header sambutan -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 p-6 md:p-8 text-white shadow-sm">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -right-4 bottom-0 h-24 w-24 rounded-full bg-white/10"></div>
        <div class="relative flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mt-0.5"><?= e($sapaan) ?>, <?= e($nama_lengkap) ?> 👋</h1>
                <p class="text-indigo-100 text-sm mt-2">
                    <?php if ($totalTugas === 0): ?>
                        Belum ada tugas untuk Anda saat ini.
                    <?php elseif ($progressPct === 100): ?>
                        Mantap, semua tugas sudah selesai!
                    <?php else: ?>
                        Anda telah menyelesaikan <?= $progressPct ?>% dari <?= $totalTugas ?> tugas.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($totalTugas > 0): ?>
                <div class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3 backdrop-blur-sm">
                    <div class="relative h-14 w-14 shrink-0">
                        <svg viewBox="0 0 36 36" class="h-14 w-14 -rotate-90">
                            <circle cx="18" cy="18" r="16" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="4"></circle>
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#fff" stroke-width="4"
                                    stroke-linecap="round"
                                    stroke-dasharray="<?= round($progressPct / 100 * 100.53, 2) ?> 100.53"></circle>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold"><?= $progressPct ?>%</span>
                    </div>
                    <div class="text-xs text-indigo-50 leading-snug">
                        <p class="font-semibold text-white">Progres tugas</p>
                        <p><?= $summary['selesai'] ?> dari <?= $totalTugas ?> selesai</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Peringatan tugas lewat deadline -->
    <?php if ($overdueCount > 0): ?>
        <div class="flex items-center gap-3 rounded-xl border border-red-100 bg-red-50 px-5 py-3">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 font-bold text-sm">!</span>
            <p class="text-sm text-red-700">
                <span class="font-semibold"><?= $overdueCount ?> tugas</span>
                sudah melewati deadline dan belum selesai. Yuk segera ditindaklanjuti.
            </p>
        </div>
    <?php endif; ?>

    <!-- Ringkasan status -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Pending</p>
                <p class="text-2xl font-bold text-slate-600"><?= $summary['pending'] ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9"></path><path d="M3 5v7h7"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Proses</p>
                <p class="text-2xl font-bold text-amber-600"><?= $summary['proses'] ?></p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Selesai</p>
                <p class="text-2xl font-bold text-emerald-600"><?= $summary['selesai'] ?></p>
            </div>
        </div>
    </div>

    <!-- Daftar Tugas -->
    <div id="tugas-saya" class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="p-6 border-b flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Tugas Saya</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar tugas yang diberikan kepada Anda</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 rounded-full px-3 py-1"><?= count($tasks) ?> tugas</span>
        </div>

        <?php if (empty($tasks)): ?>
            <div class="p-14 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                </div>
                <p class="text-slate-500 text-sm font-medium">Belum ada tugas yang diberikan untuk Anda.</p>
                <p class="text-slate-400 text-xs mt-1">Tugas baru akan muncul di sini begitu ditambahkan.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-left text-xs text-slate-500 uppercase tracking-wide">
                            <th class="px-6 py-3 font-semibold">Judul &amp; Deskripsi</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 font-semibold whitespace-nowrap">Deadline</th>
                            <th class="px-6 py-3 font-semibold text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($tasks as $task): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 align-top border-l-4 <?= tugas_status_bar($task['status']) ?>">
                                    <p class="font-medium text-slate-800"><?= e($task['tugas']) ?></p>
                                    <?php if (!empty($task['deskripsi'])): ?>
                                        <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">
                                            <?= e($task['deskripsi']) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 align-top whitespace-nowrap">
                                    <?= tugas_status_badge($task['status']) ?>
                                </td>
                                <td class="px-6 py-4 align-top whitespace-nowrap">
                                    <?= tugas_deadline_display($task['deadline'] ?? null, $task['status']) ?>
                                </td>
                                <td class="px-6 py-4 align-top text-right whitespace-nowrap">
                                    <a href="view_task.php?id=<?= (int) $task['id'] ?>"
                                       class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium text-xs">
                                        Detail/Update
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>