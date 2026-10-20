-- =====================================================================
-- MIGRASI MODUL DASHBOARD EKSEKUTIF
-- LIS PRO - UPT Laboratorium Kesehatan Provinsi Riau
--
-- Modul ini tidak membuat tabel baru: seluruh metrik dihitung dari data
-- yang sudah ada (pendaftaran, pendaftaran_detail, gudang_*, aset_*).
-- Migrasi ini hanya menanam parameter ambang batas ke tabel `pengaturan`
-- agar angka aturan tidak di-hardcode di kode.
--
-- CATATAN PENTING: nilai target TAT di bawah adalah ANGKA AWAL yang belum
-- diverifikasi ke dokumen mutu UPT Labkesda. Semuanya dapat diubah lewat
-- menu Pengaturan Sistem tanpa mengubah kode maupun menjalankan SQL lagi.
--
-- Idempoten: aman dijalankan berulang kali.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. PARAMETER DASHBOARD
-- ---------------------------------------------------------------------
INSERT INTO `pengaturan`
  (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `label`, `description`, `sort_order`, `created_at`, `updated_at`)
VALUES
  ('dashboard', 'dashboard_periode_default_hari', '30', 'number', 'Periode Bawaan Dashboard (hari)', 'Rentang tanggal yang terpilih saat dashboard pertama dibuka', 90, NOW(), NOW()),
  ('dashboard', 'dashboard_target_tat_klinis_jam', '24', 'number', 'Target TAT Pemeriksaan Klinis (jam)', 'Batas waktu dari registrasi sampai hasil selesai untuk sampel klinis. BELUM diverifikasi ke dokumen mutu.', 91, NOW(), NOW()),
  ('dashboard', 'dashboard_target_tat_lingkungan_jam', '120', 'number', 'Target TAT Pemeriksaan Lingkungan (jam)', 'Batas waktu dari registrasi sampai hasil selesai untuk sampel lingkungan. BELUM diverifikasi ke dokumen mutu.', 92, NOW(), NOW()),
  ('dashboard', 'dashboard_target_tat_mcu_jam', '48', 'number', 'Target TAT Pemeriksaan MCU (jam)', 'Batas waktu dari registrasi sampai hasil selesai untuk paket MCU. BELUM diverifikasi ke dokumen mutu.', 93, NOW(), NOW()),
  ('dashboard', 'dashboard_target_tat_lainnya_jam', '48', 'number', 'Target TAT Pemeriksaan Lainnya (jam)', 'Batas waktu untuk kategori pemeriksaan selain klinis, lingkungan, dan MCU.', 94, NOW(), NOW()),
  ('dashboard', 'dashboard_top_pemeriksaan_limit', '10', 'number', 'Jumlah Pemeriksaan Terpopuler', 'Banyaknya baris pada grafik pemeriksaan paling sering diminta', 95, NOW(), NOW()),
  ('dashboard', 'dashboard_order_tertunda_limit', '8', 'number', 'Jumlah Order Tertunda Ditampilkan', 'Banyaknya order yang paling lama menunggu pada tabel pantauan', 96, NOW(), NOW()),
  ('dashboard', 'dashboard_tren_pendapatan_bulan', '12', 'number', 'Rentang Grafik Pendapatan (bulan)', 'Banyaknya bulan terakhir pada grafik performa pendapatan', 97, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `description` = VALUES(`description`),
  `setting_type` = VALUES(`setting_type`),
  `sort_order` = VALUES(`sort_order`),
  `updated_at` = NOW();

-- ---------------------------------------------------------------------
-- 2. INDEKS PENDUKUNG
-- ---------------------------------------------------------------------
-- Dashboard melakukan agregasi per tanggal dan per status. Tanpa indeks,
-- query tren 12 bulan memaksa full table scan pada `pendaftaran`.
-- Dibuat dengan prosedur bersyarat supaya migrasi tetap idempoten
-- (MySQL/MariaDB tidak mendukung CREATE INDEX IF NOT EXISTS).

DROP PROCEDURE IF EXISTS `lispro_tambah_indeks_dashboard`;
DELIMITER $$
CREATE PROCEDURE `lispro_tambah_indeks_dashboard`()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'pendaftaran'
      AND index_name = 'idx_pendaftaran_tanggal_kategori'
  ) THEN
    ALTER TABLE `pendaftaran`
      ADD INDEX `idx_pendaftaran_tanggal_kategori` (`tanggal_pendaftaran`, `kategori_pemeriksaan`);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'pendaftaran'
      AND index_name = 'idx_pendaftaran_status_order'
  ) THEN
    ALTER TABLE `pendaftaran`
      ADD INDEX `idx_pendaftaran_status_order` (`status_order`, `tanggal_pendaftaran`);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'pendaftaran_detail'
      AND index_name = 'idx_detail_kode_item'
  ) THEN
    ALTER TABLE `pendaftaran_detail`
      ADD INDEX `idx_detail_kode_item` (`kode_item`);
  END IF;
END$$
DELIMITER ;

CALL `lispro_tambah_indeks_dashboard`();
DROP PROCEDURE IF EXISTS `lispro_tambah_indeks_dashboard`;

-- ---------------------------------------------------------------------
-- 3. HAK AKSES
-- ---------------------------------------------------------------------
-- Menu `dashboard` sudah ada sejak migrasi awal dan dapat diakses semua
-- role. Dashboard eksekutif menempel pada menu itu, jadi tidak ada entri
-- menu baru maupun perubahan role_permissions di sini.
