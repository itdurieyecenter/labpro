-- =========================================================================
-- MIGRATION: MODUL ASET & INVENTARIS LABORATORIUM
-- UPT Laboratorium Kesehatan Provinsi Riau
--
-- Lingkup sesuai KAK:
--   - Pencatatan inventaris Barang Milik Daerah (BMD) dan instrumen laboratorium
--   - Nomor seri, tanggal perolehan, lokasi penempatan ruangan
--   - Kartu pemeliharaan berkala (preventive maintenance)
--   - Histori kalibrasi alat uji
--   - Rekam jejak mutasi dan penghapusan aset
--
-- Standar yang diikuti:
--   - Permendagri No. 108 Tahun 2016  : penggolongan dan kodefikasi BMD
--   - Permendagri No. 47 Tahun 2021   : tata cara pengelolaan BMD, KIB, penyusutan
--   - ISO/IEC 17025 & ISO 15189       : kalibrasi instrumen dan ketertelusuran
--
-- Struktur master mengacu Kartu Inventaris Barang B (Peralatan dan Mesin),
-- yaitu KIB yang berlaku untuk seluruh instrumen laboratorium.
-- =========================================================================

USE `lispro`;

-- 1. REFERENSI ASET
CREATE TABLE IF NOT EXISTS `aset_referensi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kategori` VARCHAR(50) NOT NULL COMMENT 'golongan_bmd | ruangan | sumber_dana | lembaga_kalibrasi | vendor',
  `kode` VARCHAR(50) DEFAULT NULL,
  `nama` VARCHAR(200) NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `nilai_bantu` VARCHAR(50) DEFAULT NULL COMMENT 'Mis. masa manfaat bawaan untuk golongan BMD',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_aset_ref` (`kategori`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MASTER ASET (KIB-B: PERALATAN DAN MESIN)
CREATE TABLE IF NOT EXISTS `aset_barang` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,

  -- Identitas BMD sesuai Permendagri 108/2016
  `kode_bmd` VARCHAR(50) NOT NULL COMMENT 'Kodefikasi BMD, mis. 02.09.01.01.001',
  `nup` INT(11) NOT NULL DEFAULT 1 COMMENT 'Nomor Urut Pendaftaran dalam satu kode barang',
  `nomor_register` VARCHAR(60) NOT NULL UNIQUE COMMENT 'Gabungan kode BMD dan NUP, identitas unik aset',
  `golongan` VARCHAR(100) NOT NULL DEFAULT 'Alat Laboratorium',

  -- Identitas fisik
  `nama_aset` VARCHAR(200) NOT NULL,
  `merk_tipe` VARCHAR(150) DEFAULT NULL,
  `nomor_seri` VARCHAR(100) DEFAULT NULL COMMENT 'Serial number pabrikan',
  `ukuran_spesifikasi` VARCHAR(200) DEFAULT NULL,
  `bahan` VARCHAR(100) DEFAULT NULL,

  -- Perolehan
  `tanggal_perolehan` DATE DEFAULT NULL,
  `tahun_perolehan` YEAR DEFAULT NULL,
  `cara_perolehan` VARCHAR(100) DEFAULT NULL COMMENT 'Mis. Pembelian APBD, Belanja BLUD, Hibah, DAK',
  `nomor_dokumen_perolehan` VARCHAR(100) DEFAULT NULL COMMENT 'Nomor SPK/kontrak/BAST pengadaan',
  `nilai_perolehan` DECIMAL(16,2) NOT NULL DEFAULT 0,
  `masa_manfaat_tahun` INT(11) NOT NULL DEFAULT 8 COMMENT 'Dasar penyusutan garis lurus',

  -- Penempatan dan penanggung jawab
  `lokasi_ruangan` VARCHAR(150) DEFAULT NULL,
  `penanggung_jawab_id` INT(11) DEFAULT NULL,
  `penanggung_jawab_nama` VARCHAR(200) DEFAULT NULL,

  -- Kondisi dan status
  `kondisi` ENUM('baik', 'rusak_ringan', 'rusak_berat') NOT NULL DEFAULT 'baik',
  `status` ENUM('aktif', 'dipinjam', 'perbaikan', 'dihapuskan') NOT NULL DEFAULT 'aktif',

  -- Kewajiban kalibrasi dan pemeliharaan
  `wajib_kalibrasi` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = instrumen ukur yang wajib terkalibrasi',
  `interval_kalibrasi_bulan` INT(11) NOT NULL DEFAULT 12,
  `wajib_pemeliharaan` TINYINT(1) NOT NULL DEFAULT 1,
  `interval_pemeliharaan_bulan` INT(11) NOT NULL DEFAULT 6,

  `keterangan` TEXT DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_aset_kode_nup` (`kode_bmd`, `nup`),
  KEY `idx_aset_golongan` (`golongan`),
  KEY `idx_aset_ruangan` (`lokasi_ruangan`),
  KEY `idx_aset_status` (`status`, `kondisi`),
  KEY `idx_aset_kalibrasi` (`wajib_kalibrasi`),
  CONSTRAINT `fk_aset_pj` FOREIGN KEY (`penanggung_jawab_id`) REFERENCES `pegawai` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. RIWAYAT KALIBRASI (ISO/IEC 17025)
--    Satu baris per peristiwa kalibrasi. Masa berlaku dipakai untuk menilai
--    kelaikan pakai instrumen pada saat pemeriksaan dilakukan.
CREATE TABLE IF NOT EXISTS `aset_kalibrasi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `aset_id` INT(11) NOT NULL,
  `tanggal_kalibrasi` DATE NOT NULL,
  `masa_berlaku_sampai` DATE NOT NULL,
  `lembaga_kalibrasi` VARCHAR(200) NOT NULL,
  `nomor_sertifikat` VARCHAR(100) DEFAULT NULL,
  `terakreditasi_kan` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = lembaga terakreditasi KAN',
  `hasil` ENUM('laik', 'laik_dengan_catatan', 'tidak_laik') NOT NULL DEFAULT 'laik',
  `nilai_koreksi` VARCHAR(150) DEFAULT NULL COMMENT 'Ringkasan faktor koreksi atau ketidakpastian',
  `biaya` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `file_sertifikat` VARCHAR(255) DEFAULT NULL,
  `catatan` TEXT DEFAULT NULL,
  `dicatat_oleh` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kal_aset` (`aset_id`, `masa_berlaku_sampai`),
  KEY `idx_kal_berlaku` (`masa_berlaku_sampai`),
  CONSTRAINT `fk_kalibrasi_aset` FOREIGN KEY (`aset_id`) REFERENCES `aset_barang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. KARTU PEMELIHARAAN
CREATE TABLE IF NOT EXISTS `aset_pemeliharaan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `aset_id` INT(11) NOT NULL,
  `jenis` ENUM('preventif', 'korektif', 'perbaikan') NOT NULL DEFAULT 'preventif',
  `tanggal_rencana` DATE DEFAULT NULL,
  `tanggal_realisasi` DATE DEFAULT NULL,
  `pelaksana` ENUM('internal', 'vendor') NOT NULL DEFAULT 'internal',
  `nama_pelaksana` VARCHAR(200) DEFAULT NULL,
  `uraian_pekerjaan` TEXT DEFAULT NULL,
  `suku_cadang` VARCHAR(255) DEFAULT NULL,
  `biaya` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `hasil` ENUM('normal', 'perlu_tindak_lanjut', 'gagal') DEFAULT NULL,
  `status` ENUM('terjadwal', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'terjadwal',
  `catatan` TEXT DEFAULT NULL,
  `dicatat_oleh` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pel_aset` (`aset_id`, `status`),
  KEY `idx_pel_rencana` (`tanggal_rencana`, `status`),
  CONSTRAINT `fk_pemeliharaan_aset` FOREIGN KEY (`aset_id`) REFERENCES `aset_barang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. MUTASI & PENGHAPUSAN ASET
--    Mencatat seluruh perubahan penempatan, kondisi, dan status aset,
--    termasuk penghapusan BMD yang memerlukan dasar surat keputusan.
CREATE TABLE IF NOT EXISTS `aset_mutasi` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `nomor_transaksi` VARCHAR(50) NOT NULL,
  `aset_id` INT(11) NOT NULL,
  `tipe` ENUM('penempatan_awal', 'pindah_ruangan', 'peminjaman', 'pengembalian', 'perubahan_kondisi', 'perbaikan', 'penghapusan') NOT NULL,
  `tanggal` DATE NOT NULL,
  `dari_ruangan` VARCHAR(150) DEFAULT NULL,
  `ke_ruangan` VARCHAR(150) DEFAULT NULL,
  `dari_kondisi` VARCHAR(30) DEFAULT NULL,
  `ke_kondisi` VARCHAR(30) DEFAULT NULL,
  `dari_status` VARCHAR(30) DEFAULT NULL,
  `ke_status` VARCHAR(30) DEFAULT NULL,
  `pihak_terkait` VARCHAR(200) DEFAULT NULL COMMENT 'Peminjam, vendor perbaikan, atau penerima penghapusan',
  `nomor_dokumen` VARCHAR(100) DEFAULT NULL COMMENT 'Nomor SK penghapusan / BAST / surat peminjaman',
  `keterangan` TEXT DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `user_nama` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mut_aset` (`aset_id`, `tanggal`),
  KEY `idx_mut_tipe` (`tipe`),
  CONSTRAINT `fk_mutasi_aset` FOREIGN KEY (`aset_id`) REFERENCES `aset_barang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SEED DATA
-- =========================================================================

-- 6. GOLONGAN BMD (Permendagri 108/2016, golongan 02 Peralatan dan Mesin)
--    nilai_bantu berisi masa manfaat bawaan dalam tahun.
INSERT INTO `aset_referensi` (`kategori`, `kode`, `nama`, `keterangan`, `nilai_bantu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
  ('golongan_bmd', '02.09', 'Alat Laboratorium', 'Instrumen pengujian laboratorium klinis dan lingkungan', '8', 1, 1, NOW(), NOW()),
  ('golongan_bmd', '02.08', 'Alat Kedokteran dan Kesehatan', 'Peralatan medis dan penunjang diagnostik', '8', 2, 1, NOW(), NOW()),
  ('golongan_bmd', '02.06', 'Alat Kantor dan Rumah Tangga', 'Komputer, printer, meubelair, pendingin ruangan', '4', 3, 1, NOW(), NOW()),
  ('golongan_bmd', '02.10', 'Alat Studio dan Komunikasi', 'Perangkat jaringan, server, dan komunikasi', '4', 4, 1, NOW(), NOW()),
  ('golongan_bmd', '02.02', 'Alat Angkutan', 'Kendaraan dinas operasional', '7', 5, 1, NOW(), NOW()),

  -- Ruangan sesuai sebaran 14 titik pada KAK butir 5.3
  ('ruangan', 'R01', 'Ruang Kepala', NULL, NULL, 1, 1, NOW(), NOW()),
  ('ruangan', 'R02', 'Ruang Pendaftaran', NULL, NULL, 2, 1, NOW(), NOW()),
  ('ruangan', 'R03', 'Ruang PPC (Pengambilan/Penerimaan Contoh)', NULL, NULL, 3, 1, NOW(), NOW()),
  ('ruangan', 'R04', 'Ruang TU (Tata Usaha)', NULL, NULL, 4, 1, NOW(), NOW()),
  ('ruangan', 'R05', 'Ruang Ka. Sub Bag TU', NULL, NULL, 5, 1, NOW(), NOW()),
  ('ruangan', 'R06', 'Ruang Kasie Klinis', NULL, NULL, 6, 1, NOW(), NOW()),
  ('ruangan', 'R07', 'Ruang Mikrobiologi', NULL, NULL, 7, 1, NOW(), NOW()),
  ('ruangan', 'R08', 'Ruang Kimia Klinik', NULL, NULL, 8, 1, NOW(), NOW()),
  ('ruangan', 'R09', 'Ruang Hematologi', NULL, NULL, 9, 1, NOW(), NOW()),
  ('ruangan', 'R10', 'Ruang Immunologi', NULL, NULL, 10, 1, NOW(), NOW()),
  ('ruangan', 'R11', 'Ruang Parasitologi', NULL, NULL, 11, 1, NOW(), NOW()),
  ('ruangan', 'R12', 'Ruang Kimia Lingkungan', NULL, NULL, 12, 1, NOW(), NOW()),
  ('ruangan', 'R13', 'Ruang Kasie Lingkungan', NULL, NULL, 13, 1, NOW(), NOW()),
  ('ruangan', 'R14', 'Ruang Gudang', NULL, NULL, 14, 1, NOW(), NOW()),

  ('sumber_dana', NULL, 'Belanja Modal BLUD', 'Pendapatan jasa layanan BLUD', NULL, 1, 1, NOW(), NOW()),
  ('sumber_dana', NULL, 'APBD Provinsi Riau', 'Belanja modal APBD murni', NULL, 2, 1, NOW(), NOW()),
  ('sumber_dana', NULL, 'DAK Fisik Bidang Kesehatan', 'Dana Alokasi Khusus Kemenkes', NULL, 3, 1, NOW(), NOW()),
  ('sumber_dana', NULL, 'Hibah', 'Hibah pihak ketiga atau pemerintah pusat', NULL, 4, 1, NOW(), NOW()),
  ('sumber_dana', NULL, 'APBN / Tugas Pembantuan', NULL, NULL, 5, 1, NOW(), NOW()),

  ('lembaga_kalibrasi', NULL, 'Balai Pengamanan Fasilitas Kesehatan (BPFK)', 'Terakreditasi KAN', NULL, 1, 1, NOW(), NOW()),
  ('lembaga_kalibrasi', NULL, 'Badan Standardisasi Nasional (BSN/SNSU)', 'Standar Nasional Satuan Ukuran', NULL, 2, 1, NOW(), NOW()),
  ('lembaga_kalibrasi', NULL, 'Laboratorium Kalibrasi Swasta Terakreditasi KAN', NULL, NULL, 3, 1, NOW(), NOW()),
  ('lembaga_kalibrasi', NULL, 'Kalibrasi Internal (Verifikasi)', 'Hanya untuk verifikasi antara, bukan kalibrasi penuh', NULL, 4, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `keterangan` = VALUES(`keterangan`), `updated_at` = NOW();

-- 7. MENU MODUL ASET
--    Pengaturan Sistem kembali digeser agar tetap berada paling bawah.
UPDATE `menus` SET `sort_order` = 13 WHERE `id` = 11;

INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
  (42, NULL, 'Aset & Inventaris', '#aset', 'microscope', 12, 1),
  (43, 42, 'Monitoring Aset', 'aset', 'layout-dashboard', 1, 1),
  (44, 42, 'Daftar Aset (KIB-B)', 'aset/barang', 'clipboard-list', 2, 1),
  (45, 42, 'Kalibrasi Alat', 'aset/kalibrasi', 'ruler', 3, 1),
  (46, 42, 'Pemeliharaan', 'aset/pemeliharaan', 'wrench', 4, 1),
  (47, 42, 'Mutasi & Penghapusan', 'aset/mutasi', 'arrow-left-right', 5, 1)
ON DUPLICATE KEY UPDATE
  `menu_name` = VALUES(`menu_name`),
  `menu_url` = VALUES(`menu_url`),
  `menu_icon` = VALUES(`menu_icon`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

-- 8. HAK AKSES ADMIN
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
  (1, 42, 1, 1, 1, 1),
  (1, 43, 1, 1, 1, 1),
  (1, 44, 1, 1, 1, 1),
  (1, 45, 1, 1, 1, 1),
  (1, 46, 1, 1, 1, 1),
  (1, 47, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE
  `can_view` = VALUES(`can_view`),
  `can_create` = VALUES(`can_create`),
  `can_edit` = VALUES(`can_edit`),
  `can_delete` = VALUES(`can_delete`);

-- 9. PARAMETER MODUL ASET
INSERT INTO `pengaturan`
  (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `label`, `description`, `sort_order`, `created_at`, `updated_at`)
VALUES
  ('aset', 'aset_alert_kalibrasi_hari', '90', 'number', 'Ambang Peringatan Kalibrasi (hari)', 'Mulai memperingatkan sebelum masa berlaku kalibrasi habis', 80, NOW(), NOW()),
  ('aset', 'aset_alert_kalibrasi_kritis', '30', 'number', 'Ambang Kritis Kalibrasi (hari)', 'Di bawah jumlah hari ini kalibrasi ditandai kritis', 81, NOW(), NOW()),
  ('aset', 'aset_alert_pemeliharaan_hari', '30', 'number', 'Ambang Peringatan Pemeliharaan (hari)', 'Peringatan sebelum jadwal pemeliharaan berkala jatuh tempo', 82, NOW(), NOW()),
  ('aset', 'aset_prefix_mutasi', 'MA', 'text', 'Prefix Nomor Mutasi Aset', 'Format: PREFIX/0001/ROMAWI/TAHUN', 83, NOW(), NOW()),
  ('aset', 'aset_masa_manfaat_default', '8', 'number', 'Masa Manfaat Bawaan (tahun)', 'Dipakai bila golongan BMD tidak menetapkan masa manfaat', 84, NOW(), NOW()),
  ('aset', 'aset_blokir_tak_laik', '1', 'boolean', 'Tandai Alat Tak Laik Pakai', 'Jika aktif, alat dengan kalibrasi kedaluwarsa atau hasil tidak laik ditandai dilarang dipakai', 85, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();
