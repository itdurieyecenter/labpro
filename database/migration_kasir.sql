-- ============================================================
-- MIGRASI MODUL KASIR: Antrian Kasir & Closing Pendapatan Harian
-- ============================================================

-- 1. Tabel closing harian per kasir
CREATE TABLE IF NOT EXISTS `kasir_closing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_closing` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` date NOT NULL,
  `kasir_id` int(11) NOT NULL,
  `kasir_nama` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlah_transaksi` int(11) NOT NULL DEFAULT 0,
  `total_pendapatan` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_tunai` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_non_tunai` decimal(14,2) NOT NULL DEFAULT 0.00,
  `uang_fisik_tunai` decimal(14,2) NOT NULL DEFAULT 0.00,
  `selisih_tunai` decimal(14,2) NOT NULL DEFAULT 0.00,
  `rincian_akun` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'JSON rekap per akun bayar',
  `catatan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nomor_closing` (`nomor_closing`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_kasir` (`kasir_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Penanda transaksi yang sudah masuk closing (mencegah dihitung dua kali)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pendaftaran' AND COLUMN_NAME = 'closing_id');
SET @sql := IF(@col = 0,
    'ALTER TABLE `pendaftaran` ADD COLUMN `closing_id` int(11) DEFAULT NULL AFTER `nomor_kuitansi`, ADD KEY `idx_closing_id` (`closing_id`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Menu & hak akses
INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
(51, NULL, 'Kasir', '#kasir', 'receipt', 3, 1),
(52, 51, 'Antrian Kasir', 'kasir', 'wallet-cards', 1, 1),
(53, 51, 'Closing Harian', 'kasir/closing', 'calculator', 2, 1)
ON DUPLICATE KEY UPDATE `menu_name`=VALUES(`menu_name`), `menu_url`=VALUES(`menu_url`), `menu_icon`=VALUES(`menu_icon`), `parent_id`=VALUES(`parent_id`), `sort_order`=VALUES(`sort_order`);

-- Role: 1=Admin (penuh), 4=Registrasi/Kasir (view, create, edit)
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 51, 1, 1, 1, 1), (1, 52, 1, 1, 1, 1), (1, 53, 1, 1, 1, 1),
(4, 51, 1, 0, 0, 0), (4, 52, 1, 1, 1, 0), (4, 53, 1, 1, 1, 0)
ON DUPLICATE KEY UPDATE `can_view`=VALUES(`can_view`), `can_create`=VALUES(`can_create`), `can_edit`=VALUES(`can_edit`), `can_delete`=VALUES(`can_delete`);
