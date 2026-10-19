-- Skema Database LIS PRO (Laboratory Information System)
-- Kompatibel dengan MariaDB / MySQL dan PHP 7.4+

CREATE DATABASE IF NOT EXISTS `lispro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lispro`;

-- 1. Tabel Roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL UNIQUE,
  `display_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_id` INT(11) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Menus (Untuk RBAC Level Menu & Submenu)
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) DEFAULT NULL,
  `menu_name` VARCHAR(100) NOT NULL,
  `menu_url` VARCHAR(100) NOT NULL,
  `menu_icon` VARCHAR(50) NOT NULL DEFAULT 'circle',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_menus_parent` (`parent_id`),
  CONSTRAINT `fk_menus_parent` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Role Permissions (Hak Akses CRUD per Menu)
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_id` INT(11) NOT NULL,
  `menu_id` INT(11) NOT NULL,
  `can_view` TINYINT(1) NOT NULL DEFAULT 0,
  `can_create` TINYINT(1) NOT NULL DEFAULT 0,
  `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_menu` (`role_id`, `menu_id`),
  KEY `fk_perm_menu` (`menu_id`),
  CONSTRAINT `fk_perm_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_perm_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Awal Roles
INSERT INTO `roles` (`id`, `role_name`, `display_name`, `description`) VALUES
(1, 'admin', 'Administrator Sistem', 'Akses penuh seluruh konfigurasi dan data LIS'),
(2, 'dokter', 'Dokter Penanggung Jawab', 'Validasi & otorisasi hasil lab, ekspertise klinis'),
(3, 'analis', 'Analis Laboratorium', 'Pengerjaan sampel lab, input hasil, kontrol mutu'),
(4, 'registrasi', 'Petugas Registrasi', 'Pendaftaran pasien, billing & order pemeriksaan')
ON DUPLICATE KEY UPDATE `display_name`=VALUES(`display_name`);

-- Data Awal Users (Password default semua user: password123)
INSERT INTO `users` (`id`, `role_id`, `username`, `password`, `full_name`, `email`, `phone`, `is_active`) VALUES
(1, 1, 'admin', '$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO', 'Administrator LIS', 'admin@lispro.local', '081234567890', 1),
(2, 2, 'dokter', '$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO', 'dr. Budi Santoso, Sp.PK', 'dokter.budi@lispro.local', '081234567891', 1),
(3, 3, 'analis', '$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO', 'Siti Rahmawati, A.Md.AK', 'siti.analis@lispro.local', '081234567892', 1),
(4, 4, 'petugas', '$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO', 'Dewi Kartika', 'dewi.reg@lispro.local', '081234567893', 1)
ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`), `password`=VALUES(`password`);

-- Data Awal Menus
INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
(1, NULL, 'Dashboard', 'dashboard', 'layout-dashboard', 1, 1),
(2, NULL, 'Registrasi Pasien', 'pasien', 'user-plus', 2, 1),
(3, NULL, 'Order Pemeriksaan', 'order_lab', 'clipboard-list', 3, 1),
(4, NULL, 'Input Hasil Lab', 'hasil_lab', 'flask-conical', 4, 1),
(5, NULL, 'Validasi & Otorisasi', 'validasi', 'check-circle-2', 5, 1),
(6, NULL, 'Laporan & Rekap', 'laporan', 'file-spreadsheet', 6, 1),
(7, NULL, 'Master Data', '#master', 'database', 7, 1),
(8, 7, 'Tarif & Parameter Lab', 'master/tarif', 'list-plus', 1, 1),
(9, 7, 'Dokter Pengirim', 'master/dokter', 'stethoscope', 2, 1),
(10, 7, 'Nilai Rujukan Kritis', 'master/nilai_rujukan', 'activity', 3, 1),
(11, NULL, 'Pengaturan Sistem', '#settings', 'settings', 8, 1),
(12, 11, 'Manajemen Pengguna', 'users', 'users', 1, 1),
(13, 11, 'Hak Akses (RBAC)', 'roles', 'shield-check', 2, 1),
(14, 11, 'Profil Laboratorium', 'pengaturan/profil', 'building-2', 3, 1)
ON DUPLICATE KEY UPDATE `menu_name`=VALUES(`menu_name`);

-- Data Awal Role Permissions
-- 1. Admin (Akses semua menu dan CRUD penuh)
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 1, 1, 1, 1, 1),
(1, 2, 1, 1, 1, 1),
(1, 3, 1, 1, 1, 1),
(1, 4, 1, 1, 1, 1),
(1, 5, 1, 1, 1, 1),
(1, 6, 1, 1, 1, 1),
(1, 7, 1, 1, 1, 1),
(1, 8, 1, 1, 1, 1),
(1, 9, 1, 1, 1, 1),
(1, 10, 1, 1, 1, 1),
(1, 11, 1, 1, 1, 1),
(1, 12, 1, 1, 1, 1),
(1, 13, 1, 1, 1, 1),
(1, 14, 1, 1, 1, 1),

-- 2. Dokter (Dashboard, Validasi, Hasil Lab view/edit, Laporan)
(2, 1, 1, 0, 0, 0),
(2, 4, 1, 0, 1, 0),
(2, 5, 1, 1, 1, 0),
(2, 6, 1, 0, 0, 0),

-- 3. Analis Lab (Dashboard, Order Lab view, Input Hasil CRUD, Validasi view)
(3, 1, 1, 0, 0, 0),
(3, 3, 1, 0, 0, 0),
(3, 4, 1, 1, 1, 0),
(3, 5, 1, 0, 0, 0),
(3, 6, 1, 1, 0, 0),

-- 4. Petugas Registrasi (Dashboard, Registrasi Pasien CRUD, Order Lab CRUD)
(4, 1, 1, 0, 0, 0),
(4, 2, 1, 1, 1, 0),
(4, 3, 1, 1, 1, 0),
(4, 6, 1, 1, 0, 0)
ON DUPLICATE KEY UPDATE `can_view`=VALUES(`can_view`);
