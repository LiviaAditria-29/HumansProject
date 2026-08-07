<div align="center">

# 👥 HumansProject

**Sistem Informasi Manajemen Data Karyawan & Tugas**

Aplikasi web berbasis PHP native untuk mengelola data karyawan, penugasan kerja, dan pelaporan — dengan dua peran pengguna: **Admin** dan **Karyawan**.

---

## 📖 Tentang Project

**HumansProject** adalah sistem HR sederhana yang dibangun untuk membantu perusahaan/instansi mengelola data karyawan beserta penugasan pekerjaannya secara terpusat. Admin dapat mengelola seluruh data karyawan dan memberikan tugas, sementara Karyawan dapat memantau profil dan menyelesaikan tugas yang diberikan langsung dari dashboard masing-masing.

Dibangun menggunakan **PHP native (tanpa framework)** dengan pola arsitektur sederhana (`config` → `includes` → `pages`), serta tampilan modern menggunakan **Tailwind CSS**.

---

## ✨ Fitur Utama

### 🔑 Autentikasi & Otorisasi
- Login berbasis sesi (session-based) dengan proteksi **CSRF token**
- Role-based access control — **Admin** dan **Karyawan** memiliki hak akses & tampilan berbeda
- Logout dengan konfirmasi & pembersihan sesi yang aman

### 🛠️ Panel Admin
- **Dashboard** — ringkasan statistik karyawan (total, aktif, nonaktif) secara real-time
- **Data Karyawan (CRUD)** — tambah, lihat, edit, dan hapus data karyawan
- **Pencarian & Filter** — cari karyawan berdasarkan nama, NIP, email, atau departemen
- **Manajemen Tugas** — buat, edit, hapus, dan pantau progres tugas yang diberikan ke karyawan
- **Approval Tugas** — meninjau dan menyetujui tugas yang telah diselesaikan karyawan

### 👤 Panel Karyawan
- **Dashboard pribadi** — ringkasan profil & progres tugas
- **Profil** — melihat data diri (NIP, jabatan, departemen, status, dsb.)
- **Daftar Tugas** — melihat tugas yang diberikan beserta tenggat waktu (deadline)
- **Update Status Tugas** — mengubah status pengerjaan tugas (pending → proses → selesai) & unggah bukti tugas

### 🎨 Antarmuka
- Desain modern & konsisten (tema warna **indigo/slate**) di seluruh halaman admin maupun karyawan
- **Responsive** — navbar dengan menu hamburger untuk perangkat mobile
- Animasi & transisi halus (hover, fade-in) untuk pengalaman yang lebih hidup namun tetap profesional

---

## 🧰 Teknologi yang Digunakan

| Komponen         | Teknologi                              |
|-------------------|-----------------------------------------|
| Bahasa Pemrograman| PHP 8.x (native, tanpa framework)       |
| Database          | MySQL / MariaDB (melalui PDO)           |
| Styling           | Tailwind CSS (via CDN)                  |
| Font              | Plus Jakarta Sans (Google Fonts)        |
| Arsitektur        | Modular procedural (config/includes/pages) |

---

## 📁 Struktur Folder

```
humansproject/
├── admin/                  # Halaman khusus admin
│   └── dashboard.php
├── karyawan/                # Halaman khusus karyawan & manajemen data karyawan
│   ├── create.php           # Tambah karyawan
│   ├── edit.php              # Edit karyawan
│   ├── index.php             # Daftar & pencarian karyawan
│   ├── view.php               # Detail karyawan
│   ├── delete.php
│   ├── dashboard.php         # Dashboard karyawan
│   ├── profile_karyawan.php  # Profil karyawan
│   └── view_task.php         # Tugas milik karyawan
├── task/                    # Manajemen tugas (sisi admin)
│   ├── create_task.php
│   ├── edit_task.php
│   ├── delete_task.php
│   ├── index.php
│   ├── update_status.php
│   └── view_task.php
├── includes/                # Komponen bersama
│   ├── bootstrap.php         # Entry point pemuatan config & helper
│   ├── auth.php               # Fungsi autentikasi & otorisasi
│   ├── functions.php          # Fungsi bantu (helper) umum
│   ├── header.php             # Layout navbar admin
│   ├── header_karyawan.php    # Layout navbar karyawan
│   └── footer.php
├── config/                  # Konfigurasi aplikasi
│   ├── app.php                # Nama aplikasi, base URL, timezone, session
│   └── database.php           # Koneksi database (PDO)
├── assets/
│   └── css/style.css         # Tema tampilan bersama (shared theme)
├── uploads/
│   └── bukti_tugas/           # File bukti penyelesaian tugas
├── index.php                # Entry point — redirect sesuai status login
├── login.php                 # Halaman login
├── logout.php                # Proses logout
└── README.md
```

---

## ⚙️ Instalasi & Menjalankan Project

### Prasyarat
- PHP >= 8.0
- MySQL / MariaDB
- Web server lokal (XAMPP / Laragon / sejenisnya)

### Langkah-langkah

1. **Clone / salin project** ke folder `htdocs` (XAMPP) atau `www` (Laragon):
   ```
   htdocs/humansproject/
   ```

2. **Buat database** baru bernama `db_karyawan`, lalu import struktur tabel yang dibutuhkan (`users` dan `tasks`).

3. **Sesuaikan koneksi database** bila perlu di `config/database.php`:
   ```php
   const DB_HOST = 'localhost';
   const DB_NAME = 'db_karyawan';
   const DB_USER = 'root';
   const DB_PASS = '';
   ```

4. **Sesuaikan Base URL** bila lokasi project berbeda, di `config/app.php`:
   ```php
   const BASE_URL = '/humansproject';
   ```

5. **Jalankan** melalui browser:
   ```
   http://localhost/humansproject
   ```

6. **Login** menggunakan akun admin/karyawan yang sudah terdaftar di database.

---

## 🗂️ Gambaran Skema Database

| Tabel   | Keterangan                                                                 |
|---------|------------------------------------------------------------------------------|
| `users` | Menyimpan data akun & profil — NIP, nama, email, password (hashed), role (`admin`/`karyawan`), jabatan, departemen, status, tanggal masuk |
| `tasks` | Menyimpan data penugasan — nama karyawan, judul tugas, deskripsi, deadline, status (`pending`/`proses`/`selesai`/`approve`) |

> Password disimpan dalam bentuk **hash** menggunakan `password_hash()` — tidak pernah disimpan dalam bentuk teks biasa.

---

## 🔒 Keamanan

- Query database menggunakan **PDO Prepared Statement** untuk mencegah SQL Injection
- **CSRF token** pada setiap form pengiriman data
- Password di-hash dengan algoritma bawaan PHP (`PASSWORD_DEFAULT`)
- Validasi akses per halaman berdasarkan role (`require_role()`, `require_login()`)

---

## 👨‍💻 Peran Pengguna

| Role         | Hak Akses                                                                 |
|--------------|------------------------------------------------------------------------------|
| **Admin**    | Kelola seluruh data karyawan, kelola & pantau tugas, akses dashboard admin |
| **Karyawan** | Lihat profil sendiri, lihat & perbarui status tugas yang diberikan         |

---

<div align="center">

Dibuat dengan ❤️ untuk memudahkan pengelolaan data karyawan.

</div>