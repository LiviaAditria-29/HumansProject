<?php
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
$flash = get_flash();
$currentPath = str_replace('\\', '/', (string) ($_SERVER['PHP_SELF'] ?? ''));

$isactive = static function (string $path) use ($currentPath): bool {
    return str_ends_with($currentPath, $path);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <!-- Navbar -->
    <nav class="bg-gradient-to-br from-indigo-600 via-indigo-800 to-blue-900 text-white shadow-lg shadow-slate-900/10 sticky top-0 z-10">

        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            <div class="flex items-center justify-between h-16">

                <!-- Brand + Menu -->
                <div class="flex items-center gap-8">

                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white text-sm font-extrabold shadow-md shadow-indigo-900/40">
                            H
                        </div>
                        <h2 class="text-xl font-bold tracking-tight whitespace-nowrap">
                            Humans<span class="text-indigo-400">Project</span>
                        </h2>
                    </div>

                    <div class="hidden md:flex items-center gap-1">

                        <a href="dashboard.php"
                            class="flex items-center px-4 py-2 rounded-lg <?= $isactive('dashboard.php') ? 'bg-indigo-600/90 shadow-md shadow-indigo-900/30 font-medium' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white transition-colors' ?> text-sm">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 16H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v1M9 12H4m8 8V9h8v11h-8Zm0 0H9m8-4a1 1 0 1 0-2 0 1 1 0 0 0 2 0Z"/>
                            </svg>
                            <span class="ml-2">Dashboard</span>
                        </a>
                        <a href="profile_karyawan.php"
                            class="flex items-center px-4 py-2 rounded-lg <?= $isactive('profile_karyawan.php') ? 'bg-indigo-600/90 shadow-md shadow-indigo-900/30 font-medium' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white transition-colors' ?> text-sm">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/>
                            </svg>
                            <span class="ml-2">Profile Saya</span>
                        </a>

                    </div>

                </div>

                <!-- User -->
                <div class="flex items-center gap-3">

                    <div class="text-right hidden sm:block">
                        <p class="font-semibold text-sm leading-tight">
                            <?= e($_SESSION['nama_lengkap']) ?>
                        </p>
                        <p class="text-xs text-slate-400"><?= e($karyawan['jabatan'] ?? 'Karyawan') ?></p>
                    </div>

                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0">
                        <?= strtoupper(substr($_SESSION['nama_lengkap'],0,1)) ?>
                    </div>

                    <a href="../logout.php"
                    title="Logout"
                    class="group relative hidden sm:inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-slate-300 hover:text-white hover:bg-indigo-600/90 active:scale-95 transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500/50">
                        <!-- Icon Logout SVG -->
                        <svg xmlns="http://www.w3.org/2000/svg" 
                            class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors duration-200" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor" 
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        
                        <span class="hidden sm:inline">Logout</span>
                    </a>

                    <!-- Tombol menu mobile -->
                    <button type="button"
                        onclick="document.getElementById('mobileNavKaryawan').classList.toggle('hidden'); document.getElementById('mobileNavIconOpenK').classList.toggle('hidden'); document.getElementById('mobileNavIconCloseK').classList.toggle('hidden');"
                        class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-200 hover:bg-slate-700/60 hover:text-white transition-colors shrink-0"
                        aria-label="Buka menu"
                        aria-controls="mobileNavKaryawan"
                        aria-expanded="false">
                        <svg id="mobileNavIconOpenK" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg id="mobileNavIconCloseK" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                    </button>

                </div>

            </div>

            <!-- Menu mobile -->
            <div id="mobileNavKaryawan" class="hidden md:hidden pb-4 space-y-1">

                <a href="dashboard.php"
                    class="flex items-center px-4 py-2.5 rounded-lg <?= $isactive('dashboard.php') ? 'bg-indigo-600/90 shadow-md shadow-indigo-900/30 font-medium' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white transition-colors' ?> text-sm">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 16H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v1M9 12H4m8 8V9h8v11h-8Zm0 0H9m8-4a1 1 0 1 0-2 0 1 1 0 0 0 2 0Z"/>
                    </svg>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="profile_karyawan.php"
                    class="flex items-center px-4 py-2.5 rounded-lg <?= $isactive('profile_karyawan.php') ? 'bg-indigo-600/90 shadow-md shadow-indigo-900/30 font-medium' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white transition-colors' ?> text-sm">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/>
                    </svg>
                    <span class="ml-2">Profile Saya</span>
                </a>

                <div class="flex items-center gap-3 px-4 pt-3 mt-2 border-t border-slate-700/60 sm:hidden">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-bold text-xs shadow-md">
                        <?= strtoupper(substr($_SESSION['nama_lengkap'],0,1)) ?>
                    </div>
                    <div>
                        <p class="font-semibold text-sm leading-tight"><?= e($_SESSION['nama_lengkap']) ?></p>
                        <p class="text-xs text-slate-400"><?= e($karyawan['jabatan'] ?? 'Karyawan') ?></p>
                    </div>
                </div>

                <a href="../logout.php"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-red-300 hover:bg-red-500/10 hover:text-red-200 transition-colors text-sm sm:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    <span class="ml-1">Logout</span>
                </a>

            </div>

        </div>

    </nav>