<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Tambah Karyawan';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nip          = trim((string) ($_POST['nip'] ?? ''));
    $namaLengkap  = trim((string) ($_POST['nama_lengkap'] ?? ''));
    $email        = trim((string) ($_POST['email'] ?? ''));
    $password     = (string) ($_POST['password'] ?? '');
    $jabatan      = trim((string) ($_POST['jabatan'] ?? ''));
    $departemen   = trim((string) ($_POST['departemen'] ?? ''));
    $status       = (string) ($_POST['status'] ?? 'aktif');
    $tanggalMasuk = trim((string) ($_POST['tanggal_masuk'] ?? ''));

    // Validasi
    if ($nip === '') {
        $errors['nip'] = 'NIP wajib diisi.';
    }

    if ($namaLengkap === '') {
        $errors['nama_lengkap'] = 'Nama lengkap wajib diisi.';
    }

    if ($email === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    if ($password === '') {
        $errors['password'] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    if ($jabatan === '') {
        $errors['jabatan'] = 'Jabatan wajib diisi.';
    }

    if ($departemen === '') {
        $errors['departemen'] = 'Departemen wajib diisi.';
    }

    if ($tanggalMasuk === '') {
        $errors['tanggal_masuk'] = 'Tanggal masuk wajib diisi.';
    } elseif (!DateTime::createFromFormat('Y-m-d', $tanggalMasuk)) {
        $errors['tanggal_masuk'] = 'Format tanggal tidak valid.';
    }

    if (!in_array($status, ['aktif', 'nonaktif'], true)) {
        $status = 'aktif';
    }

    // Cek NIP & email sudah dipakai atau belum
    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE nip = :nip LIMIT 1');
        $stmt->execute(['nip' => $nip]);
        if ($stmt->fetch()) {
            $errors['nip'] = 'NIP sudah digunakan.';
        }

        $stmt = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email sudah digunakan.';
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare("
            INSERT INTO users (nip, nama_lengkap, email, password, role, jabatan, departemen, status, tanggal_masuk)
            VALUES (:nip, :nama_lengkap, :email, :password, 'karyawan', :jabatan, :departemen, :status, :tanggal_masuk)
        ");

        $stmt->execute([
            'nip'           => $nip,
            'nama_lengkap'  => $namaLengkap,
            'email'         => $email,
            'password'      => password_hash($password, PASSWORD_DEFAULT),
            'jabatan'       => $jabatan,
            'departemen'    => $departemen,
            'status'        => $status,
            'tanggal_masuk' => $tanggalMasuk,
        ]);

        set_flash('success', 'Data karyawan "' . $namaLengkap . '" berhasil ditambahkan.');
        redirect('karyawan/index.php');
    }
}

require __DIR__ . '/../includes/header.php';

?>

<div class="min-h-screen bg-transparent">

    <!-- Main -->
    <main class="max-w-3xl mx-auto p-8">

        <div class="mb-8">
            <a href="index.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                &larr; Kembali ke Data Karyawan
            </a>
            <h2 class="text-2xl font-semibold text-slate-800 mt-3">
                Tambah Karyawan
            </h2>
            <p class="text-slate-500 mt-1">
                Lengkapi form berikut untuk menambahkan data karyawan baru.
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

            <form method="POST" novalidate>

                <?= csrf_field() ?>

                <div class="grid md:grid-cols-2 gap-5">

                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            NIP
                        </label>
                        <input
                            type="text"
                            name="nip"
                            value="<?= old('nip') ?>"
                            class="w-full rounded-lg border <?= isset($errors['nip']) ? 'border-red-400' : 'border-slate-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: KAR006">
                        <?php if (isset($errors['nip'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['nip']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            name="nama_lengkap"
                            value="<?= old('nama_lengkap') ?>"
                            class="w-full rounded-lg border <?= isset($errors['nama_lengkap']) ? 'border-red-400' : 'border-slate-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Nama lengkap karyawan">
                        <?php if (isset($errors['nama_lengkap'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['nama_lengkap']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="<?= old('email') ?>"
                            class="w-full rounded-lg border <?= isset($errors['email']) ? 'border-red-400' : 'border-slate-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="nama@email.com">
                        <?php if (isset($errors['email'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-lg border <?= isset($errors['password']) ? 'border-red-400' : 'border-slate-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Minimal 6 karakter">
                        <?php if (isset($errors['password'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Jabatan
                        </label>
                        <input
                            type="text"
                            name="jabatan"
                            value="<?= old('jabatan') ?>"
                            class="w-full rounded-lg border <?= isset($errors['jabatan']) ? 'border-red-400' : 'border-slate-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: Staff HR">
                        <?php if (isset($errors['jabatan'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['jabatan']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Departemen -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Departemen
                        </label>
                        <input
                            type="text"
                            name="departemen"
                            value="<?= old('departemen') ?>"
                            class="w-full rounded-lg border <?= isset($errors['departemen']) ? 'border-red-400' : 'border-slate-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: Human Resources">
                        <?php if (isset($errors['departemen'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['departemen']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal Masuk -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tanggal Masuk
                        </label>
                        <input
                            type="date"
                            name="tanggal_masuk"
                            value="<?= old('tanggal_masuk', date('Y-m-d')) ?>"
                            class="w-full rounded-lg border <?= isset($errors['tanggal_masuk']) ? 'border-red-400' : 'border-slate-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <?php if (isset($errors['tanggal_masuk'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['tanggal_masuk']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Status
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="aktif"
                                    <?= (($_POST['status'] ?? 'aktif') === 'aktif') ? 'checked' : '' ?>
                                    class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">Aktif</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="nonaktif"
                                    <?= (($_POST['status'] ?? '') === 'nonaktif') ? 'checked' : '' ?>
                                    class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">Nonaktif</span>
                            </label>
                        </div>
                    </div>

                </div>

                <div class="flex items-center gap-3 mt-8 pt-6 border-t">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                        Simpan Karyawan
                    </button>

                    <a href="index.php"
                        class="px-6 py-2.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                </div>

            </form>

        </div>

    </main>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>