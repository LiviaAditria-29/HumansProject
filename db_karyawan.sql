-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table db_karyawan.tasks
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nama_karyawan` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tugas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `deadline` date DEFAULT NULL,
  `bukti_tugas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','proses','approve','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tasks_user` (`user_id`),
  CONSTRAINT `fk_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table db_karyawan.tasks: ~4 rows (approximately)
INSERT INTO `tasks` (`id`, `user_id`, `nama_karyawan`, `tugas`, `deskripsi`, `deadline`, `bukti_tugas`, `status`, `created_at`, `updated_at`) VALUES
	(5, 5, 'Karyawan 3', 'Banner', 'Tema Hut RI yang menarik', '2026-08-08', 'bukti_5_2ba7d52f.png', 'approve', '2026-08-06 02:54:34', '2026-08-06 07:42:32'),
	(7, 5, 'Liviaaa 3', 'Membuat poster', 'Hut RI', '2026-08-15', NULL, 'approve', '2026-08-06 07:41:26', '2026-08-06 07:41:59'),
	(8, 5, 'Liviaaa 3', 'Tugas 3', 'Tugas', '2026-08-22', 'bukti_8_0b8f5073.pdf', 'proses', '2026-08-06 08:21:07', '2026-08-06 08:22:49'),
	(9, 5, 'Liviaaa 3', 'Tugas 4', 'Tugas', '2026-08-28', NULL, 'pending', '2026-08-06 08:21:26', '2026-08-06 08:21:26');

-- Dumping structure for table db_karyawan.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nip` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','karyawan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'karyawan',
  `jabatan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departemen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `tanggal_masuk` date NOT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT (now()) ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table db_karyawan.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `nip`, `nama_lengkap`, `email`, `password`, `role`, `jabatan`, `departemen`, `no_hp`, `alamat`, `tanggal_masuk`, `status`, `created_at`, `updated_at`) VALUES
	(1, '19920115202001', 'Livia Aditria', 'admin@perusahaan.com', '$2a$12$hZVR19BS41AtGW8Q/WPeUOnkPKa3vCqxy3Hz3x28ofjBHggB7wrnq', 'admin', 'IT System Administrator', 'Teknologi Informasi', '081234567890', 'Jl. Merdeka No. 45, Jakarta Selatan', '2020-01-15', 'aktif', '2026-08-04 09:08:04', '2026-08-04 09:08:04'),
	(3, '12345678909755', 'Karyawan Satu', 'karyawan@gmail.com', '$2a$12$hwdj.x5zTqDFcoKTutGySeROUS35lY1yb5NH8wDNbjIyjDwjqJoCe', 'karyawan', 'CS', 'front', '099876543212', 'jalan jalan', '2024-08-05', 'nonaktif', '2026-08-05 06:48:27', '2026-08-05 06:48:27'),
	(5, '1234567890', 'Liviaaa 3', 'karyawan3@gmail.com', '$2y$10$xT93ZXS54AMXtJWyH8oJuONhdsaStSQArKxoP5mz0TX9FIGn16jcG', 'karyawan', 'Keuangan', 'Keuangan', NULL, NULL, '2026-04-01', 'aktif', '2026-08-06 07:32:48', '2026-08-06 07:32:48');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
