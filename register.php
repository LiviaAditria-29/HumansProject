<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect_by_role();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nip          = trim((string) ($_POST['nip'] ?? ''));
    $namaLengkap  = trim((string) ($_POST['nama_lengkap'] ?? ''));
    $email        = trim((string) ($_POST['email'] ?? ''));
    $password     = (string) ($_POST['password'] ?? '');
    $passwordConf = (string) ($_POST['password_confirmation'] ?? '');
    $jabatan      = trim((string) ($_POST['jabatan'] ?? ''));
    $departemen   = trim((string) ($_POST['departemen'] ?? ''));

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
    } elseif ($password !== $passwordConf) {
        $errors['password_confirmation'] = 'Konfirmasi password tidak cocok.';
    }

    if ($jabatan === '') {
        $errors['jabatan'] = 'Jabatan wajib diisi.';
    }

    if ($departemen === '') {
        $errors['departemen'] = 'Departemen wajib diisi.';
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
            VALUES (:nip, :nama_lengkap, :email, :password, 'karyawan', :jabatan, :departemen, 'aktif', :tanggal_masuk)
        ");

        $stmt->execute([
            'nip'           => $nip,
            'nama_lengkap'  => $namaLengkap,
            'email'         => $email,
            'password'      => password_hash($password, PASSWORD_DEFAULT),
            'jabatan'       => $jabatan,
            'departemen'    => $departemen,
            'tanggal_masuk' => date('Y-m-d'),
        ]);

        set_flash('success', 'Registrasi berhasil. Silakan login dengan akun Anda.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
$hideNavbar = true;
require __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">

            <!-- Logo / Judul -->
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                    H
                </div>

                <h1 class="mt-4 text-3xl font-bold text-gray-800">
                    Daftar Akun
                </h1>

                <p class="text-gray-500 mt-2">
                    Buat akun karyawan baru
                </p>
            </div>

            <!-- Form -->
            <form method="POST" novalidate>

                <?= csrf_field() ?>

                <div class="grid gap-5">

                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            NIP
                        </label>
                        <input
                            type="text"
                            name="nip"
                            value="<?= old('nip') ?>"
                            class="w-full rounded-lg border <?= isset($errors['nip']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Contoh: KAR006">
                        <?php if (isset($errors['nip'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['nip']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            name="nama_lengkap"
                            value="<?= old('nama_lengkap') ?>"
                            class="w-full rounded-lg border <?= isset($errors['nama_lengkap']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Nama lengkap Anda">
                        <?php if (isset($errors['nama_lengkap'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['nama_lengkap']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="<?= old('email') ?>"
                            class="w-full rounded-lg border <?= isset($errors['email']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="nama@email.com">
                        <?php if (isset($errors['email'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Jabatan
                        </label>
                        <input
                            type="text"
                            name="jabatan"
                            value="<?= old('jabatan') ?>"
                            class="w-full rounded-lg border <?= isset($errors['jabatan']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Contoh: Staff HR">
                        <?php if (isset($errors['jabatan'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['jabatan']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Departemen -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Departemen
                        </label>
                        <input
                            type="text"
                            name="departemen"
                            value="<?= old('departemen') ?>"
                            class="w-full rounded-lg border <?= isset($errors['departemen']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Contoh: Human Resources">
                        <?php if (isset($errors['departemen'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['departemen']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-lg border <?= isset($errors['password']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Minimal 6 karakter">
                        <?php if (isset($errors['password'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Konfirmasi Password
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="w-full rounded-lg border <?= isset($errors['password_confirmation']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Ulangi password">
                        <?php if (isset($errors['password_confirmation'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['password_confirmation']) ?></p>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200 mt-6">
                    Daftar
                </button>

            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Sudah punya akun?
                <a href="login.php" class="text-blue-600 font-semibold hover:underline">Login di sini</a>
            </p>

        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-6">
            &copy; <?= date('Y') ?> Humans Project
        </p>

    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>