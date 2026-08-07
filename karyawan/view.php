<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$pageTitle = 'Detail Karyawan';

$id = (int) ($_GET['id'] ?? 0);

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

require __DIR__ . '/../includes/header.php';

?>

<div class="min-h-screen bg-gray-50">
    <!-- Main -->
    <main class="max-w-3xl mx-auto p-8">
        <div class="flex gap-3 mb-6">
        <a href="index.php"
           class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded-lg shadow-sm transition-all duration-200 hover:bg-gray-100 hover:border-gray-400 hover:shadow-md hover:-translate-y-0.5">
            Kembali
        </a>

        <!-- <a href="edit.php?id=<?= (int) $karyawan['id'] ?>"
           class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded-lg shadow-sm transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5">
            Edit
        </a> -->
    </div>
        <!-- <div class="mb-8">
            <a href="index.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                &larr; Kembali ke Data Karyawan
            </a>
        </div> -->

        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
            

            <div class="flex items-center justify-between flex-wrap gap-4">

                <div class="flex items-center gap-4">

                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                        <?= strtoupper(substr($karyawan['nama_lengkap'], 0, 1)) ?>
                    </div>

                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">
                            <?= e($karyawan['nama_lengkap']) ?>
                        </h2>
                        <p class="text-gray-500">
                            <?= e($karyawan['jabatan']) ?> &middot; <?= e($karyawan['departemen']) ?>
                        </p>
                    </div>

                </div>

                <?= status_badge($karyawan['status']) ?>

            </div>

        </div>

        <!-- Detail Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

            <div class="p-6 border-b">
                <h3 class="text-lg font-bold text-gray-800">
                    Informasi Karyawan
                </h3>
            </div>

            <dl class="divide-y divide-gray-100">

                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-gray-500">NIP</dt>
                    <dd class="col-span-2 text-sm text-gray-800"><?= e($karyawan['nip']) ?></dd>
                </div>

                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="col-span-2 text-sm text-gray-800"><?= e($karyawan['email']) ?></dd>
                </div>

                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-gray-500">Jabatan</dt>
                    <dd class="col-span-2 text-sm text-gray-800"><?= e($karyawan['jabatan']) ?></dd>
                </div>

                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-gray-500">Departemen</dt>
                    <dd class="col-span-2 text-sm text-gray-800"><?= e($karyawan['departemen']) ?></dd>
                </div>

                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-gray-500">Tanggal Masuk</dt>
                    <dd class="col-span-2 text-sm text-gray-800"><?= format_date($karyawan['tanggal_masuk']) ?></dd>
                </div>

                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="col-span-2 text-sm"><?= status_badge($karyawan['status']) ?></dd>
                </div>

            </dl>

        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 mt-6">


            <!-- <form action="delete.php" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus data <?= e($karyawan['nama_lengkap']) ?>?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $karyawan['id'] ?>">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-white border border-blue-200 hover:bg-red-50 text-blue-600 font-medium px-5 py-2.5 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="currentColor">
                        <path d="M9 3V4H4V6H5V20C5 21.1 5.9 22 7 22H17C18.1 22 19 21.1 19 20V6H20V4H15V3H9ZM8 8H10V18H8V8ZM14 8H16V18H14V8Z"/>
                    </svg>
                    Hapus
                </button>
            </form> -->

        </div>

    </main>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>