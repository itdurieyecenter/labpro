-- =========================================================================
-- MIGRATION: MODUL PENDAFTARAN & ORDER LIS TERPADU
-- UPT Laboratorium Kesehatan Provinsi Riau
-- =========================================================================

USE `lispro`;

-- 1. TABEL PENDAFTARAN & ORDER LIS UTAMA
CREATE TABLE IF NOT EXISTS `pendaftaran` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nomor_registrasi` VARCHAR(50) NOT NULL UNIQUE,
  `nomor_billing` VARCHAR(50) NOT NULL UNIQUE,
  `barcode_sampel` VARCHAR(50) NOT NULL UNIQUE,
  `client_id` INT(11) DEFAULT NULL,
  `tipe_pasien` ENUM('mandiri', 'rujukan_faskes', 'perusahaan_lingkungan') NOT NULL DEFAULT 'mandiri',
  `tanggal_pendaftaran` DATE NOT NULL,
  `jam_pendaftaran` TIME NOT NULL,
  `nama_pasien` VARCHAR(150) NOT NULL,
  `nik_pasien` VARCHAR(50) DEFAULT NULL,
  `nomor_rm` VARCHAR(50) DEFAULT NULL,
  `jenis_kelamin` ENUM('L', 'P') DEFAULT NULL,
  `tanggal_lahir` DATE DEFAULT NULL,
  `usia_tahun` INT(3) DEFAULT NULL,
  `usia_bulan` INT(2) DEFAULT NULL,
  `telepon` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `kota_kabupaten` VARCHAR(100) DEFAULT 'Kota Pekanbaru',
  
  -- Info Rujukan & Sampel
  `instansi_pengirim` VARCHAR(150) DEFAULT NULL,
  `dokter_pengirim` VARCHAR(150) DEFAULT NULL,
  `nomor_surat_pengantar` VARCHAR(100) DEFAULT NULL,
  `kategori_pemeriksaan` ENUM('klinis', 'lingkungan', 'mcu', 'lainnya') NOT NULL DEFAULT 'klinis',
  `jenis_sampel` VARCHAR(100) DEFAULT 'Darah Vena / Serum',
  `diagnosa_klinis` TEXT DEFAULT NULL,
  
  -- Alur & Rincian Keuangan BLUD
  `alur_pembayaran` ENUM('mandiri_langsung', 'piutang_pks') NOT NULL DEFAULT 'mandiri_langsung',
  `kelompok_tarif` ENUM('umum', 'perusahaan', 'bpjs', 'asuransi') NOT NULL DEFAULT 'umum',
  `subtotal_tarif` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `diskon_persen` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `diskon_nominal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_biaya` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_jasa_manajemen` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_jasa_dokter` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_jasa_petugas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_kso` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_bhp` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  
  -- Status Kasir / Pembayaran
  `status_pembayaran` ENUM('UNPAID', 'PAID', 'GUARANTEED', 'CANCELLED') NOT NULL DEFAULT 'UNPAID',
  `metode_pembayaran` VARCHAR(50) DEFAULT NULL,
  `waktu_bayar` DATETIME DEFAULT NULL,
  `nominal_bayar` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `nominal_kembali` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `kasir_id` INT(11) DEFAULT NULL,
  `nomor_kuitansi` VARCHAR(50) DEFAULT NULL,
  
  -- Verifikasi Piutang / PKS Mitra
  `nomor_pks_spk` VARCHAR(100) DEFAULT NULL,
  `instansi_penjamin` VARCHAR(150) DEFAULT NULL,
  `pic_penjamin` VARCHAR(100) DEFAULT NULL,
  `catatan_penjamin` TEXT DEFAULT NULL,
  `status_penagihan` ENUM('belum_ditagih', 'invoiced', 'lunas') NOT NULL DEFAULT 'belum_ditagih',
  `nomor_invoice` VARCHAR(50) DEFAULT NULL,
  
  -- Status Operasional LIS & Sampling
  `status_order` ENUM('registered', 'sampling', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'registered',
  `waktu_sampling` DATETIME DEFAULT NULL,
  `petugas_sampling` VARCHAR(100) DEFAULT NULL,
  `catatan` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_nomor_registrasi` (`nomor_registrasi`),
  KEY `idx_nomor_billing` (`nomor_billing`),
  KEY `idx_barcode_sampel` (`barcode_sampel`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_tanggal` (`tanggal_pendaftaran`),
  KEY `idx_status_pembayaran` (`status_pembayaran`),
  KEY `idx_status_order` (`status_order`),
  KEY `idx_alur_pembayaran` (`alur_pembayaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. TABEL RINCIAN ITEM PEMERIKSAAN LAB
CREATE TABLE IF NOT EXISTS `pendaftaran_detail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` INT(11) NOT NULL,
  `tipe_item` ENUM('parameter', 'template') NOT NULL DEFAULT 'parameter',
  `item_id` INT(11) NOT NULL,
  `kode_item` VARCHAR(50) NOT NULL,
  `nama_item` VARCHAR(150) NOT NULL,
  `kategori_lab` VARCHAR(100) DEFAULT NULL,
  `metode_uji` VARCHAR(100) DEFAULT NULL,
  `satuan` VARCHAR(50) DEFAULT NULL,
  `nilai_rujukan` VARCHAR(150) DEFAULT NULL,
  `jasa_manajemen` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `jasa_dokter` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `jasa_petugas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `kso` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `bhp` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tarif` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status_pengujian` ENUM('pending', 'sampling', 'processing', 'completed') NOT NULL DEFAULT 'pending',
  `hasil_nilai` VARCHAR(255) DEFAULT NULL,
  `hasil_flag` ENUM('normal', 'low', 'high', 'critical') DEFAULT NULL,
  `catatan_hasil` TEXT DEFAULT NULL,
  `analis_id` INT(11) DEFAULT NULL,
  `waktu_input_hasil` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_pendaftaran_id` (`pendaftaran_id`),
  KEY `idx_item` (`tipe_item`, `item_id`),
  CONSTRAINT `fk_pendaftaran_detail_reg` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. TABEL AUDIT TRAIL LOG PENDAFTARAN
CREATE TABLE IF NOT EXISTS `pendaftaran_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` INT(11) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `user_name` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_pendaftaran_id` (`pendaftaran_id`),
  CONSTRAINT `fk_pendaftaran_log_reg` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 4. UPDATE MENU SYSTEM UNTUK PENDAFTARAN & ORDER LIS
-- Mengaktifkan menu Pendaftaran pada posisi kedua (setelah Dashboard)
UPDATE `menus` 
SET `menu_name` = 'Pendaftaran & Order LIS', 
    `menu_url` = 'pendaftaran', 
    `menu_icon` = 'clipboard-signature',
    `sort_order` = 2,
    `is_active` = 1
WHERE `id` = 2;

-- Pastikan menu Order Pemeriksaan (id 3) terarah ke antrian sampling/pengujian
UPDATE `menus`
SET `menu_name` = 'Sampling & Antrian Lab',
    `menu_url` = 'pendaftaran/antrian_sampling',
    `menu_icon` = 'test-tubes',
    `sort_order` = 3,
    `is_active` = 1
WHERE `id` = 3;

-- 5. SEED DATA DUMMY CONTOH UNTUK TESTING ALUR MANDIRI & PKS MITRA
INSERT INTO `pendaftaran` (
  `nomor_registrasi`, `nomor_billing`, `barcode_sampel`, `client_id`, `tipe_pasien`,
  `tanggal_pendaftaran`, `jam_pendaftaran`, `nama_pasien`, `nik_pasien`, `nomor_rm`,
  `jenis_kelamin`, `tanggal_lahir`, `usia_tahun`, `usia_bulan`, `telepon`, `email`,
  `alamat`, `kota_kabupaten`, `instansi_pengirim`, `dokter_pengirim`, `nomor_surat_pengantar`,
  `kategori_pemeriksaan`, `jenis_sampel`, `diagnosa_klinis`, `alur_pembayaran`, `kelompok_tarif`,
  `subtotal_tarif`, `diskon_persen`, `diskon_nominal`, `total_biaya`,
  `total_jasa_manajemen`, `total_jasa_dokter`, `total_jasa_petugas`, `total_kso`, `total_bhp`,
  `status_pembayaran`, `metode_pembayaran`, `waktu_bayar`, `nominal_bayar`, `nominal_kembali`,
  `kasir_id`, `nomor_kuitansi`, `nomor_pks_spk`, `instansi_penjamin`, `pic_penjamin`,
  `status_penagihan`, `status_order`, `catatan`, `created_by`
) VALUES
-- Sample 1: Pasien Mandiri Walk-in (Sudah bayar di Kasir -> PAID -> Barcode Sampel Terbit)
(
  'REG-20261017-0001', 'BIL-20261017-0001', 'SMP-261017-0001', 1, 'mandiri',
  CURRENT_DATE(), '08:30:00', 'Ahmad Fadillah', '3201011504920002', 'PSN-2026-0001',
  'L', '1992-04-15', 34, 6, '081298765432', 'ahmad.fadillah@example.com',
  'Jl. Sudirman No. 45, Pekanbaru', 'Kota Pekanbaru', 'Datang Sendiri (Walk-in)', 'dr. Anita Sp.PD', NULL,
  'klinis', 'Darah Vena / Serum', 'Pemeriksaan Rutin Gula Darah & Profil Lipid', 'mandiri_langsung', 'umum',
  125000.00, 0.00, 0.00, 125000.00,
  25000.00, 35000.00, 35000.00, 10000.00, 20000.00,
  'PAID', 'QRIS Dinamis', NOW(), 125000.00, 0.00,
  1, 'KWT-20261017-0001', NULL, NULL, NULL,
  'belum_ditagih', 'sampling', 'Pasien puasa 10 jam. Siap pengambilan sampel darah.', 1
),
-- Sample 2: Rujukan Faskes Puskesmas (Menunggu Pembayaran Kasir -> UNPAID)
(
  'REG-20261017-0002', 'BIL-20261017-0002', 'SMP-261017-0002', 2, 'rujukan_faskes',
  CURRENT_DATE(), '09:15:00', 'Nurul Hidayati', '3201015608950001', 'PSN-2026-0002',
  'P', '1995-08-16', 31, 2, '081387654321', 'nurul.h@example.com',
  'Jl. Merak No. 12, Pekanbaru', 'Kota Pekanbaru', 'Puskesmas Rumbai', 'dr. Syarifah', '440/PKM-RB/X/2026/089',
  'klinis', 'Darah EDTA', 'Suspek DBD / Demam Berdarah Dengue', 'mandiri_langsung', 'umum',
  85000.00, 0.00, 0.00, 85000.00,
  15000.00, 25000.00, 25000.00, 5000.00, 15000.00,
  'UNPAID', NULL, NULL, 0.00, 0.00,
  NULL, NULL, NULL, NULL, NULL,
  'belum_ditagih', 'registered', 'Surat rujukan puskesmas terlampir. Menunggu di kasir.', 1
),
-- Sample 3: Alur Piutang PKS Mitra Perusahaan (PKS / SPK Terverifikasi -> GUARANTEED -> Barcode Terbit)
(
  'REG-20261017-0003', 'BIL-20261017-0003', 'SMP-261017-0003', NULL, 'perusahaan_lingkungan',
  CURRENT_DATE(), '10:00:00', 'Sampel Air Limbah Outlet PT. Riau Pulp', '01.234.567.8-012.000', 'CORP-2026-0001',
  NULL, NULL, NULL, NULL, '08119871234', 'env@riaupulp.co.id',
  'Komp. Pabrik Industri Pangkalan Kerinci', 'Kab. Pelalawan', 'PT. Riau Andalan Pulp & Paper', 'Bambang Kusuma, SE', '045/SPK-ENV/RAPP/X/2026',
  'lingkungan', 'Air Limbah Industri (2 Liter)', 'Uji Baku Mutu Lingkungan Triwulan IV Pemprov Riau', 'piutang_pks', 'perusahaan',
  450000.00, 10.00, 45000.00, 405000.00,
  80000.00, 120000.00, 120000.00, 40000.00, 45000.00,
  'GUARANTEED', 'SPK/PKS Korporat', NOW(), 0.00, 0.00,
  1, NULL, 'PKS/LABKES/2026/012', 'PT. Riau Andalan Pulp & Paper', 'Bambang Kusuma (Manager Lingkungan)',
  'belum_ditagih', 'sampling', 'Jalur SPK Resmi. Invoice ditagihkan akhir bulan sesuai PKS BLUD.', 1
)
ON DUPLICATE KEY UPDATE `nama_pasien`=VALUES(`nama_pasien`);

-- Seed Rincian Detail Pemeriksaan Sample 1 (Ahmad Fadillah)
INSERT INTO `pendaftaran_detail` (
  `pendaftaran_id`, `tipe_item`, `item_id`, `kode_item`, `nama_item`, `kategori_lab`, `metode_uji`, `satuan`, `nilai_rujukan`,
  `jasa_manajemen`, `jasa_dokter`, `jasa_petugas`, `kso`, `bhp`, `tarif`, `status_pengujian`
) VALUES
(1, 'parameter', 1, 'GLU', 'Glukosa Puasa', 'Kimia Klinik', 'GOD-PAP', 'mg/dL', '70 - 100', 10000.00, 15000.00, 15000.00, 5000.00, 10000.00, 55000.00, 'pending'),
(1, 'parameter', 2, 'CHOL', 'Kolesterol Total', 'Kimia Klinik', 'CHOD-PAP', 'mg/dL', '< 200', 15000.00, 20000.00, 20000.00, 5000.00, 10000.00, 70000.00, 'pending'),

-- Seed Rincian Detail Sample 2 (Nurul Hidayati)
(2, 'template', 1, 'DL', 'Darah Lengkap (Hematologi Analisis)', 'Hematologi', 'Flowcytometry', '-', 'Standar Hematologi', 15000.00, 25000.00, 25000.00, 5000.00, 15000.00, 85000.00, 'pending'),

-- Seed Rincian Detail Sample 3 (Sampel Limbah PT RAPP)
(3, 'parameter', 3, 'BOD', 'BOD (Biochemical Oxygen Demand)', 'Kesehatan Lingkungan', 'Winkler Titrimetri', 'mg/L', '< 50.0', 40000.00, 60000.00, 60000.00, 20000.00, 20000.00, 200000.00, 'pending'),
(3, 'parameter', 4, 'COD', 'COD (Chemical Oxygen Demand)', 'Kesehatan Lingkungan', 'Refluks Tertutup Spektrofotometri', 'mg/L', '< 100.0', 40000.00, 60000.00, 60000.00, 20000.00, 25000.00, 205000.00, 'pending')
ON DUPLICATE KEY UPDATE `tarif`=VALUES(`tarif`);
