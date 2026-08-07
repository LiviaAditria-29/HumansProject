<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Edit Karyawan';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Data karyawan tidak ditemukan.');
    redirect('karyawan/index.php');
}

$stmt = db()->prepare("SELECT * FROM users WHERE id = :id AND role = 'karyawan' LIMIT 1");
$stmt->execute(['id' => $id]);
$karyawan = $stmt->fetch();

if (!$karyawan) {
    set_flash('danger', 'Data karyawan tidak ditemukan.');
    redirect('karyawan/index.php');
}

$errors = [];

// Nilai default untuk form (dari database, ditimpa oleh input POST kalau ada validasi gagal)
$form = [
    'nip'           => $karyawan['nip'],
    'nama_lengkap'  => $karyawan['nama_lengkap'],
    'email'         => $karyawan['email'],
    'jabatan'       => $karyawan['jabatan'],
    'departemen'    => $karyawan['departemen'],
    'status'        => $karyawan['status'],
    'tanggal_masuk' => $karyawan['tanggal_masuk'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['nip']           = trim((string) ($_POST['nip'] ?? ''));
    $form['nama_lengkap']  = trim((string) ($_POST['nama_lengkap'] ?? ''));
    $form['email']         = trim((string) ($_POST['email'] ?? ''));
    $form['jabatan']       = trim((string) ($_POST['jabatan'] ?? ''));
    $form['departemen']    = trim((string) ($_POST['departemen'] ?? ''));
    $form['status']        = (string) ($_POST['status'] ?? 'aktif');
    $form['tanggal_masuk'] = trim((string) ($_POST['tanggal_masuk'] ?? ''));

    $password = (string) ($_POST['password'] ?? '');

    // Validasi
    if ($form['nip'] === '') {
        $errors['nip'] = 'NIP wajib diisi.';
    }

    if ($form['nama_lengkap'] === '') {
        $errors['nama_lengkap'] = 'Nama lengkap wajib diisi.';
    }

    if ($form['email'] === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    if ($password !== '' && strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    if ($form['jabatan'] === '') {
        $errors['jabatan'] = 'Jabatan wajib diisi.';
    }

    if ($form['departemen'] === '') {
        $errors['departemen'] = 'Departemen wajib diisi.';
    }

    if ($form['tanggal_masuk'] === '') {
        $errors['tanggal_masuk'] = 'Tanggal masuk wajib diisi.';
    } elseif (!DateTime::createFromFormat('Y-m-d', $form['tanggal_masuk'])) {
        $errors['tanggal_masuk'] = 'Format tanggal tidak valid.';
    }

    if (!in_array($form['status'], ['aktif', 'nonaktif'], true)) {
        $form['status'] = 'aktif';
    }

    // Cek NIP & email dipakai oleh karyawan lain
    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE nip = :nip AND id != :id LIMIT 1');
        $stmt->execute(['nip' => $form['nip'], 'id' => $id]);
        if ($stmt->fetch()) {
            $errors['nip'] = 'NIP sudah digunakan karyawan lain.';
        }

        $stmt = db()->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
        $stmt->execute(['email' => $form['email'], 'id' => $id]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email sudah digunakan karyawan lain.';
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET
                    nip = :nip,
                    nama_lengkap = :nama_lengkap,
                    email = :email,
                    jabatan = :jabatan,
                    departemen = :departemen,
                    status = :status,
                    tanggal_masuk = :tanggal_masuk";

        $params = [
            'nip'           => $form['nip'],
            'nama_lengkap'  => $form['nama_lengkap'],
            'email'         => $form['email'],
            'jabatan'       => $form['jabatan'],
            'departemen'    => $form['departemen'],
            'status'        => $form['status'],
            'tanggal_masuk' => $form['tanggal_masuk'],
            'id'            => $id,
        ];

        if ($password !== '') {
            $sql .= ', password = :password';
            $params['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        set_flash('success', 'Data karyawan "' . $form['nama_lengkap'] . '" berhasil diperbarui.');
        redirect('karyawan/index.php');
    }
}

require __DIR__ . '/../includes/header.php';

?>

<div class="min-h-screen bg-gray-50">
    <!-- Main -->
    <main class="max-w-3xl mx-auto p-8">

        <div class="mb-8">
            <a href="index.php"
           class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded-lg shadow-sm transition-all duration-200 hover:bg-gray-100 hover:border-gray-400 hover:shadow-md hover:-translate-y-0.5">
            Kembali
            </a>

            <br><br>
            <h2 class="text-2xl font-semibold text-gray-800 mt-3">
                Edit Karyawan
            </h2>
            <p class="text-gray-500 mt-1">
                Perbarui data <?= e($karyawan['nama_lengkap']) ?>.
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

            <form method="POST" novalidate>

                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">

                <div class="grid md:grid-cols-2 gap-5">

                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            NIP
                        </label>
                        <input
                            type="text"
                            name="nip"
                            value="<?= e($form['nip']) ?>"
                            class="w-full rounded-lg border <?= isset($errors['nip']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                            value="<?= e($form['nama_lengkap']) ?>"
                            class="w-full rounded-lg border <?= isset($errors['nama_lengkap']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                            value="<?= e($form['email']) ?>"
                            class="w-full rounded-lg border <?= isset($errors['email']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <?php if (isset($errors['email'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Password Baru
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-lg border <?= isset($errors['password']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Kosongkan jika tidak diubah">
                        <?php if (isset($errors['password'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['password']) ?></p>
                        <?php else: ?>
                            <p class="text-gray-400 text-xs mt-1">Kosongkan jika tidak ingin mengganti password.</p>
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
                            value="<?= e($form['jabatan']) ?>"
                            class="w-full rounded-lg border <?= isset($errors['jabatan']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                            value="<?= e($form['departemen']) ?>"
                            class="w-full rounded-lg border <?= isset($errors['departemen']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <?php if (isset($errors['departemen'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['departemen']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal Masuk -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Masuk
                        </label>
                        <input
                            type="date"
                            name="tanggal_masuk"
                            value="<?= e($form['tanggal_masuk']) ?>"
                            class="w-full rounded-lg border <?= isset($errors['tanggal_masuk']) ? 'border-red-400' : 'border-gray-300' ?> px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <?php if (isset($errors['tanggal_masuk'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= e($errors['tanggal_masuk']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Status
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="aktif"
                                    <?= $form['status'] === 'aktif' ? 'checked' : '' ?>
                                    class="text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Aktif</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="nonaktif"
                                    <?= $form['status'] === 'nonaktif' ? 'checked' : '' ?>
                                    class="text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Nonaktif</span>
                            </label>
                        </div>
                    </div>

                </div>

                <div class="flex items-center gap-3 mt-8 pt-6 border-t">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>

                    <a href="index.php"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                </div>

            </form>

        </div>

    </main>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>