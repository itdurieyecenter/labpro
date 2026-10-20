-- =========================================================================
-- MIGRATION: MODUL GUDANG & STOCK OPNAME REAGEN / BHP
-- UPT Laboratorium Kesehatan Provinsi Riau
--
-- Lingkup sesuai KAK:
--   - Mutasi masuk dan keluar reagen, bahan kimia, kontrol, kalibrator, BHP
--   - Kartu stok digital dengan kalkulasi FIFO berbasis batch
--   - Notifikasi masa kedaluwarsa
--   - Kontrol batas minimum persediaan (reorder point)
--   - Penyesuaian fisik persediaan berkala (stock opname)
--
-- Catatan desain: stok tidak disimpan sebagai satu angka di master barang,
-- melainkan dijumlahkan dari sisa tiap batch. Dengan begitu satu jenis barang
-- dapat memiliki beberapa nomor batch dengan masa kedaluwarsa berbeda, dan
-- pengeluaran dapat dirunut sampai ke batch asalnya.
-- =========================================================================

USE `lispro`;

-- 1. REFERENSI GUDANG (kategori barang, satuan, lokasi simpan, pemasok)
CREATE TABLE IF NOT EXISTS `gudang_referensi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kategori` VARCHAR(50) NOT NULL COMMENT 'kategori_barang | satuan | lokasi_simpan | pemasok',
  `kode` VARCHAR(50) DEFAULT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gd_ref_kategori` (`kategori`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MASTER BARANG / REAGEN / BHP
CREATE TABLE IF NOT EXISTS `gudang_barang` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_barang` VARCHAR(50) NOT NULL UNIQUE,
  `nama_barang` VARCHAR(200) NOT NULL,
  `kategori` VARCHAR(100) NOT NULL DEFAULT 'Bahan Habis Pakai (BHP)',
  `satuan` VARCHAR(30) NOT NULL DEFAULT 'Pcs',
  `merk` VARCHAR(150) DEFAULT NULL,
  `kemasan` VARCHAR(100) DEFAULT NULL COMMENT 'Contoh: Box isi 100 test, Botol 500 mL',
  `suhu_penyimpanan` VARCHAR(50) DEFAULT NULL COMMENT 'Contoh: 2-8 derajat C, Suhu ruang',
  `lokasi_simpan` VARCHAR(100) DEFAULT NULL,
  `stok_minimum` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Titik pemesanan ulang (reorder point)',
  `stok_maksimum` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `harga_satuan_terakhir` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `wajib_batch` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = penerimaan wajib mencantumkan nomor batch & kedaluwarsa',
  `keterangan` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_barang_kategori` (`kategori`),
  KEY `idx_barang_aktif` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BATCH / LOT PERSEDIAAN
--    Inti perhitungan FIFO: tiap penerimaan membentuk satu batch, dan
--    pengeluaran menggerus batch tertua lebih dulu.
CREATE TABLE IF NOT EXISTS `gudang_batch` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `barang_id` INT(11) NOT NULL,
  `nomor_batch` VARCHAR(100) DEFAULT NULL,
  `tanggal_terima` DATE NOT NULL,
  `tanggal_kedaluwarsa` DATE DEFAULT NULL,
  `jumlah_masuk` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `jumlah_sisa` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `harga_satuan` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `pemasok` VARCHAR(150) DEFAULT NULL,
  `nomor_dokumen` VARCHAR(100) DEFAULT NULL COMMENT 'Nomor faktur / BAST / SPK penerimaan',
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `is_terkunci` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = batch ditahan, tidak boleh dikeluarkan',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_batch_barang` (`barang_id`),
  KEY `idx_batch_fifo` (`barang_id`, `jumlah_sisa`, `tanggal_terima`),
  KEY `idx_batch_kedaluwarsa` (`tanggal_kedaluwarsa`),
  CONSTRAINT `fk_batch_barang` FOREIGN KEY (`barang_id`) REFERENCES `gudang_barang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. KARTU STOK / BUKU MUTASI
--    Setiap pergerakan dicatat satu baris, lengkap dengan saldo setelahnya
--    sehingga kartu stok dapat dibaca berurutan tanpa menghitung ulang.
CREATE TABLE IF NOT EXISTS `gudang_mutasi` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `nomor_transaksi` VARCHAR(50) NOT NULL,
  `barang_id` INT(11) NOT NULL,
  `batch_id` INT(11) DEFAULT NULL,
  `tipe` ENUM('masuk', 'keluar', 'opname_plus', 'opname_minus', 'rusak', 'kedaluwarsa', 'retur') NOT NULL,
  `tanggal` DATE NOT NULL,
  `jumlah` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Selalu positif, arah ditentukan kolom tipe',
  `saldo_sesudah` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Total stok barang setelah mutasi ini',
  `harga_satuan` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `tujuan` VARCHAR(150) DEFAULT NULL COMMENT 'Unit/instalasi peminta pada pengeluaran',
  `referensi_tabel` VARCHAR(64) DEFAULT NULL,
  `referensi_id` INT(11) DEFAULT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `user_nama` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mutasi_barang` (`barang_id`, `tanggal`),
  KEY `idx_mutasi_tipe` (`tipe`),
  KEY `idx_mutasi_nomor` (`nomor_transaksi`),
  CONSTRAINT `fk_mutasi_barang` FOREIGN KEY (`barang_id`) REFERENCES `gudang_barang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mutasi_batch` FOREIGN KEY (`batch_id`) REFERENCES `gudang_batch` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. STOCK OPNAME (HEADER)
CREATE TABLE IF NOT EXISTS `gudang_opname` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nomor_opname` VARCHAR(50) NOT NULL UNIQUE,
  `tanggal` DATE NOT NULL,
  `status` ENUM('draft', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'draft',
  `petugas_id` INT(11) DEFAULT NULL,
  `petugas_nama` VARCHAR(150) DEFAULT NULL,
  `catatan` TEXT DEFAULT NULL,
  `total_item` INT(11) NOT NULL DEFAULT 0,
  `total_selisih_lebih` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_selisih_kurang` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `waktu_selesai` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_opname_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. STOCK OPNAME (RINCIAN PER BARANG)
CREATE TABLE IF NOT EXISTS `gudang_opname_detail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `opname_id` INT(11) NOT NULL,
  `barang_id` INT(11) NOT NULL,
  `kode_barang` VARCHAR(50) DEFAULT NULL,
  `nama_barang` VARCHAR(200) DEFAULT NULL,
  `satuan` VARCHAR(30) DEFAULT NULL,
  `stok_sistem` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `stok_fisik` DECIMAL(12,2) DEFAULT NULL COMMENT 'NULL = belum dihitung petugas',
  `selisih` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_opname_barang` (`opname_id`, `barang_id`),
  KEY `idx_opname_detail` (`opname_id`),
  CONSTRAINT `fk_opname_detail_header` FOREIGN KEY (`opname_id`) REFERENCES `gudang_opname` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_opname_detail_barang` FOREIGN KEY (`barang_id`) REFERENCES `gudang_barang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SEED DATA
-- =========================================================================

-- 7. REFERENSI
INSERT INTO `gudang_referensi` (`kategori`, `kode`, `nama`, `keterangan`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
  ('kategori_barang', 'RGN', 'Reagen Diagnostik', 'Reagen siap pakai untuk pemeriksaan klinis dan lingkungan', 1, 1, NOW(), NOW()),
  ('kategori_barang', 'KIM', 'Bahan Kimia', 'Bahan kimia analitik, larutan baku, pelarut', 2, 1, NOW(), NOW()),
  ('kategori_barang', 'KTR', 'Bahan Kontrol', 'Serum/bahan kontrol mutu internal', 3, 1, NOW(), NOW()),
  ('kategori_barang', 'KAL', 'Kalibrator', 'Bahan kalibrasi instrumen laboratorium', 4, 1, NOW(), NOW()),
  ('kategori_barang', 'BHP', 'Bahan Habis Pakai (BHP)', 'Tabung, spuit, lancet, tip, sarung tangan, masker', 5, 1, NOW(), NOW()),
  ('kategori_barang', 'MED', 'Media Mikrobiologi', 'Media kultur, agar, dan pendukung biakan', 6, 1, NOW(), NOW()),

  ('satuan', NULL, 'Pcs', NULL, 1, 1, NOW(), NOW()),
  ('satuan', NULL, 'Box', NULL, 2, 1, NOW(), NOW()),
  ('satuan', NULL, 'Vial', NULL, 3, 1, NOW(), NOW()),
  ('satuan', NULL, 'Botol', NULL, 4, 1, NOW(), NOW()),
  ('satuan', NULL, 'Kit', NULL, 5, 1, NOW(), NOW()),
  ('satuan', NULL, 'Test', NULL, 6, 1, NOW(), NOW()),
  ('satuan', NULL, 'mL', NULL, 7, 1, NOW(), NOW()),
  ('satuan', NULL, 'Gram', NULL, 8, 1, NOW(), NOW()),
  ('satuan', NULL, 'Pack', NULL, 9, 1, NOW(), NOW()),
  ('satuan', NULL, 'Lembar', NULL, 10, 1, NOW(), NOW()),

  ('lokasi_simpan', NULL, 'Gudang Utama', 'Rak penyimpanan suhu ruang', 1, 1, NOW(), NOW()),
  ('lokasi_simpan', NULL, 'Lemari Pendingin 2-8 C', 'Reagen dan bahan kontrol rantai dingin', 2, 1, NOW(), NOW()),
  ('lokasi_simpan', NULL, 'Freezer -20 C', 'Reagen beku dan bahan biomolekuler', 3, 1, NOW(), NOW()),
  ('lokasi_simpan', NULL, 'Lemari Bahan Kimia (B3)', 'Bahan berbahaya dan beracun, berventilasi', 4, 1, NOW(), NOW()),
  ('lokasi_simpan', NULL, 'Ruang Mikrobiologi', 'Media kultur siap pakai', 5, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `keterangan` = VALUES(`keterangan`), `updated_at` = NOW();

-- 8. MENU MODUL GUDANG
--    Pengaturan Sistem digeser ke urutan terakhir agar menu operasional
--    tetap berada di atas.
UPDATE `menus` SET `sort_order` = 12 WHERE `id` = 11;

INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
  (35, NULL, 'Gudang & Logistik', '#gudang', 'package', 11, 1),
  (36, 35, 'Monitoring Stok', 'gudang', 'layout-dashboard', 1, 1),
  (37, 35, 'Master Barang & BHP', 'gudang/barang', 'boxes', 2, 1),
  (38, 35, 'Penerimaan Barang', 'gudang/penerimaan', 'package-plus', 3, 1),
  (39, 35, 'Pemakaian Barang', 'gudang/pemakaian', 'package-minus', 4, 1),
  (40, 35, 'Kartu Stok', 'gudang/kartu_stok', 'scroll-text', 5, 1),
  (41, 35, 'Stock Opname', 'gudang/opname', 'clipboard-check', 6, 1)
ON DUPLICATE KEY UPDATE
  `menu_name` = VALUES(`menu_name`),
  `menu_url` = VALUES(`menu_url`),
  `menu_icon` = VALUES(`menu_icon`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

-- 9. HAK AKSES ADMIN
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
  (1, 35, 1, 1, 1, 1),
  (1, 36, 1, 1, 1, 1),
  (1, 37, 1, 1, 1, 1),
  (1, 38, 1, 1, 1, 1),
  (1, 39, 1, 1, 1, 1),
  (1, 40, 1, 1, 1, 1),
  (1, 41, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE
  `can_view` = VALUES(`can_view`),
  `can_create` = VALUES(`can_create`),
  `can_edit` = VALUES(`can_edit`),
  `can_delete` = VALUES(`can_delete`);

-- 10. PARAMETER MODUL GUDANG
INSERT INTO `pengaturan`
  (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `label`, `description`, `sort_order`, `created_at`, `updated_at`)
VALUES
  ('gudang', 'gudang_metode_keluar', 'FIFO', 'select', 'Metode Pengeluaran Persediaan', 'FIFO mengeluarkan batch yang paling dahulu diterima; FEFO mengeluarkan batch yang paling dahulu kedaluwarsa', 70, NOW(), NOW()),
  ('gudang', 'gudang_alert_kedaluwarsa_hari', '90', 'number', 'Ambang Peringatan Kedaluwarsa (hari)', 'Batch yang kedaluwarsa dalam rentang hari ini akan diperingatkan', 71, NOW(), NOW()),
  ('gudang', 'gudang_alert_kritis_hari', '30', 'number', 'Ambang Kritis Kedaluwarsa (hari)', 'Di bawah jumlah hari ini batch ditandai kritis', 72, NOW(), NOW()),
  ('gudang', 'gudang_prefix_masuk', 'GM', 'text', 'Prefix Nomor Penerimaan', 'Format: PREFIX/0001/ROMAWI/TAHUN', 73, NOW(), NOW()),
  ('gudang', 'gudang_prefix_keluar', 'GK', 'text', 'Prefix Nomor Pengeluaran', 'Format: PREFIX/0001/ROMAWI/TAHUN', 74, NOW(), NOW()),
  ('gudang', 'gudang_prefix_opname', 'SO', 'text', 'Prefix Nomor Stock Opname', 'Format: PREFIX/0001/ROMAWI/TAHUN', 75, NOW(), NOW()),
  ('gudang', 'gudang_izinkan_stok_minus', '0', 'boolean', 'Izinkan Stok Menjadi Minus', 'Jika nonaktif, pengeluaran melebihi stok tersedia akan ditolak', 76, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();
