<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('karyawan');

$pageTitle = 'Profil Saya';
$userId = (int) ($_SESSION['user_id'] ?? 0);

$errors = [];
$activeTab = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $activeTab = 'email';
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email tidak valid.';
        }

        if (empty($errors)) {
            $upd = db()->prepare("UPDATE users SET email = :email WHERE id = :id");
            $upd->execute(['email' => $email, 'id' => $userId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Profil berhasil diperbarui.'];
            header('Location: profile_karyawan.php');
            exit;
        }
    }

    if ($action === 'update_password') {
        $activeTab = 'password';
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $stmtPw = db()->prepare("SELECT password FROM users WHERE id = :id");
        $stmtPw->execute(['id' => $userId]);
        $hash = $stmtPw->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            $errors[] = 'Password lama tidak sesuai.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'Password baru minimal 8 karakter.';
        } elseif ($new !== $confirm) {
            $errors[] = 'Konfirmasi password baru tidak cocok.';
        }

        if (empty($errors)) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $upd = db()->prepare("UPDATE users SET password = :password WHERE id = :id");
            $upd->execute(['password' => $newHash, 'id' => $userId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Password berhasil diubah.'];
            header('Location: profile_karyawan.php');
            exit;
        }
    }
}

$stmt = db()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$karyawan = $stmt->fetch();

if (!$karyawan) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data pengguna tidak ditemukan.'];
    header('Location: dashboard.php');
    exit;
}

/**
 * Cetak field CSRF tersembunyi. Menggunakan csrf_field() bila sudah didefinisikan
 * di includes/bootstrap.php, atau fallback membaca $_SESSION['csrf_token'].
 */
function render_csrf_field(): string
{
    if (function_exists('csrf_field')) {
        return csrf_field();
    }
    $token = e((string) ($_SESSION['csrf_token'] ?? ''));
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
}

require __DIR__ . '/../includes/header_karyawan.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    .font-display { font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; }
    .font-body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    .badge-punch {
        background-image: repeating-linear-gradient(180deg, #E3A73F 0px, #E3A73F 7px, transparent 7px, transparent 15px);
    }
    .nip-mono { font-variant-numeric: tabular-nums; letter-spacing: 0.14em; }
    .tab-label {
        cursor: pointer;
        user-select: none;
        transition: color .15s ease, border-color .15s ease;
    }
    input[type="radio"].tab-radio { position: absolute; opacity: 0; pointer-events: none; }
    .tab-panel { display: none; }
    #tab-info:checked ~ .tabs-bar label[for="tab-info"],
    #tab-email:checked ~ .tabs-bar label[for="tab-email"],
    #tab-password:checked ~ .tabs-bar label[for="tab-password"] {
        color: #1F3B5C;
        border-color: #E3A73F;
    }
    #tab-info:checked ~ #panel-info,
    #tab-email:checked ~ #panel-email,
    #tab-password:checked ~ #panel-password {
        display: block;
    }
</style>

<main class="font-body max-w-3xl mx-auto p-6 md:p-8 space-y-6">

    <!-- <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-[#E3A73F] mb-1">Kartu Karyawan</p>
        <h2 class="font-display text-2xl font-semibold text-slate-800">Profil Saya</h2>
    </div> -->

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-100 text-red-600 text-sm rounded-xl p-4 space-y-1">
            <?php foreach ($errors as $err): ?>
                <p><?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Kartu ID Karyawan -->
    <div class="relative overflow-hidden rounded-2xl shadow-sm border border-slate-100 bg-white">
        <div class="absolute inset-y-0 left-0 w-3 bg-gradient-to-b from-[#1F3B5C] via-[#26497A] to-[#1F3B5C]"></div>
        <div class="absolute inset-y-0 left-3 w-2 badge-punch opacity-80"></div>

        <div class="pl-10 pr-6 py-6 flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="w-16 h-16 shrink-0 rounded-full bg-gradient-to-br from-[#1F3B5C] to-[#26497A] flex items-center justify-center text-white font-display font-bold text-2xl shadow-md ring-4 ring-white">
                <?= strtoupper(substr((string) ($karyawan['nama_lengkap'] ?? '?'), 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-display text-lg font-semibold text-slate-800 truncate"><?= e($karyawan['nama_lengkap'] ?? '-') ?></p>
                <span class="inline-block mt-1 text-xs font-medium text-[#1F3B5C] bg-[#1F3B5C]/10 rounded-full px-2.5 py-0.5">
                    <?= e($karyawan['jabatan'] ?? 'Karyawan') ?>
                </span>
            </div>
            <div class="sm:text-right">
                <p class="text-[11px] uppercase tracking-widest text-slate-400">NIP</p>
                <p class="nip-mono text-sm font-semibold text-slate-700"><?= e($karyawan['nip'] ?? '-') ?></p>
            </div>
        </div>

        <dl class="pl-10 pr-6 pb-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 border-t border-slate-100 pt-4">
            <div>
                <dt class="text-[11px] uppercase tracking-widest text-slate-400">Jabatan</dt>
                <dd class="text-sm text-slate-800"><?= e($karyawan['jabatan'] ?? '-') ?></dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-widest text-slate-400">Departemen</dt>
                <dd class="text-sm text-slate-800"><?= e($karyawan['departemen'] ?? '-') ?></dd>
            </div>
        </dl>
    </div>

    <!-- Pengaturan Akun -->
    <div class="rounded-2xl shadow-sm border border-slate-100 bg-white overflow-hidden">

        <input type="radio" name="tab" id="tab-info" class="tab-radio" <?= $activeTab === 'info' ? 'checked' : '' ?>>
        <input type="radio" name="tab" id="tab-email" class="tab-radio" <?= $activeTab === 'email' ? 'checked' : '' ?>>
        <input type="radio" name="tab" id="tab-password" class="tab-radio" <?= $activeTab === 'password' ? 'checked' : '' ?>>

        <div class="tabs-bar flex gap-6 px-6 pt-5 border-b border-slate-100">
            <label for="tab-info" class="tab-label text-sm font-medium text-slate-400 border-b-2 border-transparent pb-3">Ringkasan</label>
            <label for="tab-email" class="tab-label text-sm font-medium text-slate-400 border-b-2 border-transparent pb-3">Ubah Email</label>
            <label for="tab-password" class="tab-label text-sm font-medium text-slate-400 border-b-2 border-transparent pb-3">Ubah Kata Sandi</label>
        </div>

        <div id="panel-info" class="tab-panel p-6 space-y-3">
            <p class="text-sm text-slate-500">Email dan kata sandi digunakan untuk masuk ke akun Anda. Perbarui melalui tab di atas bila diperlukan.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 pt-1">
                <div>
                    <dt class="text-[11px] uppercase tracking-widest text-slate-400">Email</dt>
                    <dd class="text-sm text-slate-800"><?= e($karyawan['email'] ?? '-') ?></dd>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-widest text-slate-400">Status Akun</dt>
                    <dd class="text-sm text-slate-800 inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                    </dd>
                </div>
            </div>
        </div>

        <div id="panel-email" class="tab-panel p-6">
            <form method="post" class="space-y-4">
                <?= render_csrf_field() ?>
                <input type="hidden" name="action" value="update_profile">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Alamat email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= e($karyawan['email'] ?? '') ?>"
                        required
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#E3A73F] focus:border-transparent"
                    >
                    <p class="mt-1.5 text-xs text-slate-400">Digunakan untuk notifikasi dan masuk ke sistem.</p>
                </div>
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#1F3B5C] hover:bg-[#26497A] text-white text-sm font-medium px-5 py-2.5 transition-colors">
                    Simpan email
                </button>
            </form>
        </div>

        <div id="panel-password" class="tab-panel p-6">
            <form method="post" class="space-y-4">
                <?= render_csrf_field() ?>
                <input type="hidden" name="action" value="update_password">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1.5">Password saat ini</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#E3A73F] focus:border-transparent"
                    >
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1.5">Password baru</label>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#E3A73F] focus:border-transparent"
                        >
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi password baru</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#E3A73F] focus:border-transparent"
                        >
                    </div>
                </div>
                <p class="text-xs text-slate-400">Minimal 8 karakter.</p>
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#1F3B5C] hover:bg-[#26497A] text-white text-sm font-medium px-5 py-2.5 transition-colors">
                    Simpan password
                </button>
            </form>
        </div>

    </div>

</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>