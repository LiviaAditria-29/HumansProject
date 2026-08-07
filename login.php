<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect_by_role();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $stmt = db()->prepare('SELECT id, nama_lengkap, email, password, role, status FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Email atau password salah.';
        } elseif ($user['status'] !== 'aktif') {
            $error = 'Akun Anda sedang tidak aktif. Hubungi admin.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            set_flash('success', 'Login berhasil. Selamat datang!');
            redirect_by_role();
        }
    }
}

$pageTitle = 'Login';
$hideNavbar = true;
require __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md">

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">

            <!-- Logo / Judul -->
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                    H
                </div>

                <h1 class="mt-4 text-3xl font-bold text-gray-800">
                    Login
                </h1>

                <p class="text-gray-500 mt-2">
                    Silakan login ke akun Anda
                </p>
            </div>

            <!-- Alert -->
            <?php if ($error !== '') : ?>
                <div class="mb-5 rounded-lg bg-red-100 border border-red-300 text-red-700 px-4 py-3">
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST">

                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- Email -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?= old('email') ?>"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="nama@email.com"
                        required>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Password
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Masukan Password"
                        required>
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200">
                    Login
                </button>

            </form>

        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-6">
            &copy; <?= date('Y') ?> Humans Project
        </p>

    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>