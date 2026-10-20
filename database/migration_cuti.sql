-- =========================================================================
-- MIGRATION: MODUL KEPEGAWAIAN - MANAJEMEN CUTI ASN
-- UPT Laboratorium Kesehatan Provinsi Riau
--
-- Dasar hukum  : PP No. 11 Tahun 2017 & Peraturan BKN No. 24 Tahun 2017
-- Pola approval: mengikuti engine berjenjang yang sudah dipakai modul
--                validasi hasil lab (setting_approval_hasil + pendaftaran_approval)
-- =========================================================================

USE `lispro`;

-- 1. MASTER JENIS CUTI
CREATE TABLE IF NOT EXISTS `cuti_jenis` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(150) NOT NULL,
  `dasar_hukum` VARCHAR(255) DEFAULT NULL,
  `max_hari` INT(11) NOT NULL DEFAULT 0 COMMENT 'Batas hari per pengajuan, 0 = tanpa batas',
  `mengurangi_saldo` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = memotong saldo cuti tahunan N/N-1/N-2',
  `wajib_lampiran` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = wajib unggah surat keterangan, misal surat dokter',
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cuti_jenis_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. HARI LIBUR NASIONAL / CUTI BERSAMA
--    Dipakai untuk menghitung jumlah hari kerja cuti secara akurat.
CREATE TABLE IF NOT EXISTS `cuti_hari_libur` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tanggal` DATE NOT NULL UNIQUE,
  `keterangan` VARCHAR(150) NOT NULL,
  `jenis` ENUM('libur_nasional', 'cuti_bersama') NOT NULL DEFAULT 'libur_nasional',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_libur_tanggal` (`tanggal`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SALDO HAK CUTI TAHUNAN PER PEGAWAI PER TAHUN
--    Sisa cuti N-1 dan N-2 masing-masing hanya boleh dipakai maksimal 6 hari
--    pada tahun berjalan (Perka BKN 24/2017), batas diatur di tabel pengaturan.
CREATE TABLE IF NOT EXISTS `cuti_saldo` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT(11) NOT NULL,
  `tahun` YEAR NOT NULL,
  `hak_tahun_berjalan` INT(11) NOT NULL DEFAULT 12 COMMENT 'Hak cuti tahunan tahun N',
  `saldo_n1` INT(11) NOT NULL DEFAULT 0 COMMENT 'Sisa hak cuti tahun N-1 yang dibawa',
  `saldo_n2` INT(11) NOT NULL DEFAULT 0 COMMENT 'Sisa hak cuti tahun N-2 yang dibawa',
  `terpakai_n` INT(11) NOT NULL DEFAULT 0,
  `terpakai_n1` INT(11) NOT NULL DEFAULT 0,
  `terpakai_n2` INT(11) NOT NULL DEFAULT 0,
  `penyesuaian` INT(11) NOT NULL DEFAULT 0 COMMENT 'Koreksi manual oleh TU, boleh negatif',
  `catatan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_saldo_pegawai_tahun` (`pegawai_id`, `tahun`),
  CONSTRAINT `fk_saldo_pegawai` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. SETTING HIRARKI APPROVAL CUTI
--    Struktur sengaja dibuat sama dengan `setting_approval_hasil`.
CREATE TABLE IF NOT EXISTS `setting_approval_cuti` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `urutan` INT(11) NOT NULL,
  `pegawai_id` INT(11) NOT NULL,
  `jabatan_approval` VARCHAR(150) NOT NULL COMMENT 'Peran dalam alur, misal Atasan Langsung / Kasubbag TU / Kepala UPT',
  `keterangan` TEXT DEFAULT NULL,
  `is_required` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_approval_cuti_urutan` (`urutan`),
  KEY `idx_approval_cuti_pegawai` (`pegawai_id`),
  CONSTRAINT `fk_setting_cuti_pegawai` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PENGAJUAN CUTI
--    Data pegawai & jenis cuti di-snapshot agar surat lama tetap tercetak
--    sesuai keadaan saat pengajuan, meski master berubah kemudian.
CREATE TABLE IF NOT EXISTS `cuti_pengajuan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nomor_pengajuan` VARCHAR(50) NOT NULL UNIQUE,
  `nomor_surat_izin` VARCHAR(100) DEFAULT NULL COMMENT 'Nomor surat izin cuti resmi, diisi saat disetujui',
  `pegawai_id` INT(11) NOT NULL,
  `pegawai_nama` VARCHAR(200) NOT NULL,
  `pegawai_nip` VARCHAR(18) DEFAULT NULL,
  `pegawai_jabatan` VARCHAR(150) DEFAULT NULL,
  `pegawai_pangkat` VARCHAR(100) DEFAULT NULL,
  `pegawai_golongan` VARCHAR(10) DEFAULT NULL,
  `pegawai_seksi` VARCHAR(150) DEFAULT NULL,
  `pegawai_mkg_tahun` INT(11) DEFAULT 0,
  `jenis_cuti_id` INT(11) NOT NULL,
  `jenis_cuti_kode` VARCHAR(20) NOT NULL,
  `jenis_cuti_nama` VARCHAR(150) NOT NULL,
  `tahun_hak` YEAR NOT NULL COMMENT 'Tahun saldo yang dibebani',
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `jumlah_hari` INT(11) NOT NULL DEFAULT 0 COMMENT 'Hari kerja, di luar akhir pekan & hari libur',
  `pakai_n2` INT(11) NOT NULL DEFAULT 0,
  `pakai_n1` INT(11) NOT NULL DEFAULT 0,
  `pakai_n` INT(11) NOT NULL DEFAULT 0,
  `alasan` TEXT NOT NULL,
  `alamat_selama_cuti` TEXT DEFAULT NULL,
  `telepon_selama_cuti` VARCHAR(30) DEFAULT NULL,
  `lampiran` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('draft', 'diajukan', 'proses', 'disetujui', 'ditolak', 'dibatalkan') NOT NULL DEFAULT 'draft',
  `catatan_penolakan` TEXT DEFAULT NULL,
  `diinput_oleh_user_id` INT(11) DEFAULT NULL,
  `diinput_oleh_nama` VARCHAR(100) DEFAULT NULL,
  `waktu_pengajuan` DATETIME DEFAULT NULL,
  `waktu_selesai_approval` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cuti_pegawai` (`pegawai_id`),
  KEY `idx_cuti_status` (`status`),
  KEY `idx_cuti_tahun` (`tahun_hak`),
  KEY `idx_cuti_tanggal` (`tanggal_mulai`, `tanggal_selesai`),
  CONSTRAINT `fk_cuti_pegawai` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cuti_jenis` FOREIGN KEY (`jenis_cuti_id`) REFERENCES `cuti_jenis` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. RANTAI APPROVAL PER PENGAJUAN
--    Struktur sengaja dibuat sama dengan `pendaftaran_approval`.
CREATE TABLE IF NOT EXISTS `cuti_approval` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cuti_id` INT(11) NOT NULL,
  `rule_id` INT(11) DEFAULT NULL,
  `urutan` INT(11) NOT NULL,
  `pegawai_id` INT(11) NOT NULL,
  `pegawai_nama` VARCHAR(200) NOT NULL,
  `pegawai_nip` VARCHAR(50) DEFAULT NULL,
  `pegawai_jabatan` VARCHAR(150) DEFAULT NULL,
  `jabatan_approval` VARCHAR(150) NOT NULL,
  `status` ENUM('waiting', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'waiting',
  `catatan` TEXT DEFAULT NULL,
  `tanda_tangan` LONGTEXT DEFAULT NULL,
  `approved_by_user_id` INT(11) DEFAULT NULL,
  `approved_by_user_name` VARCHAR(100) DEFAULT NULL,
  `waktu_approval` DATETIME DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cuti_approval_cuti` (`cuti_id`),
  KEY `idx_cuti_approval_urutan` (`urutan`),
  KEY `idx_cuti_approval_status` (`status`),
  CONSTRAINT `fk_cuti_approval_cuti` FOREIGN KEY (`cuti_id`) REFERENCES `cuti_pengajuan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. LOG AKTIVITAS PENGAJUAN CUTI (audit trail)
CREATE TABLE IF NOT EXISTS `cuti_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cuti_id` INT(11) NOT NULL,
  `aksi` VARCHAR(50) NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `status_sebelum` VARCHAR(30) DEFAULT NULL,
  `status_sesudah` VARCHAR(30) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `user_nama` VARCHAR(100) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cuti_log_cuti` (`cuti_id`),
  CONSTRAINT `fk_cuti_log_cuti` FOREIGN KEY (`cuti_id`) REFERENCES `cuti_pengajuan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SEED DATA
-- =========================================================================

-- 8. JENIS CUTI SESUAI PP 11/2017
INSERT INTO `cuti_jenis`
  (`kode`, `nama`, `dasar_hukum`, `max_hari`, `mengurangi_saldo`, `wajib_lampiran`, `keterangan`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES
  ('TAHUNAN', 'Cuti Tahunan', 'PP No. 11 Tahun 2017 Pasal 310-313', 24, 1, 0, 'Hak 12 hari kerja per tahun, dapat ditambah sisa N-1 dan N-2 maksimal 6 hari masing-masing', 1, 1, NOW(), NOW()),
  ('BESAR', 'Cuti Besar', 'PP No. 11 Tahun 2017 Pasal 314-318', 90, 0, 0, 'Untuk PNS yang telah bekerja paling singkat 5 tahun secara terus-menerus', 2, 1, NOW(), NOW()),
  ('SAKIT', 'Cuti Sakit', 'PP No. 11 Tahun 2017 Pasal 319-324', 0, 0, 1, 'Wajib melampirkan surat keterangan dokter', 3, 1, NOW(), NOW()),
  ('MELAHIRKAN', 'Cuti Melahirkan', 'PP No. 11 Tahun 2017 Pasal 325-329', 90, 0, 1, 'Untuk kelahiran anak pertama sampai ketiga', 4, 1, NOW(), NOW()),
  ('PENTING', 'Cuti Karena Alasan Penting', 'PP No. 11 Tahun 2017 Pasal 330-333', 30, 0, 0, 'Ibu/bapak/istri/suami/anak sakit keras atau meninggal, melangsungkan perkawinan', 5, 1, NOW(), NOW()),
  ('CLTN', 'Cuti di Luar Tanggungan Negara', 'PP No. 11 Tahun 2017 Pasal 334-339', 1095, 0, 1, 'Paling lama 3 tahun, memerlukan persetujuan Kepala BKN', 6, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `nama` = VALUES(`nama`),
  `dasar_hukum` = VALUES(`dasar_hukum`),
  `updated_at` = NOW();

-- 9. PARAMETER ATURAN CUTI (konfigurabel, tidak di-hardcode)
INSERT INTO `pengaturan`
  (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `label`, `description`, `sort_order`, `created_at`, `updated_at`)
VALUES
  ('cuti', 'cuti_hak_tahunan', '12', 'number', 'Hak Cuti Tahunan (hari kerja)', 'Jumlah hak cuti tahunan per pegawai dalam satu tahun', 50, NOW(), NOW()),
  ('cuti', 'cuti_max_pakai_n1', '6', 'number', 'Batas Pemakaian Sisa Cuti N-1', 'Maksimal hari sisa cuti tahun lalu yang boleh dipakai tahun berjalan', 51, NOW(), NOW()),
  ('cuti', 'cuti_max_pakai_n2', '6', 'number', 'Batas Pemakaian Sisa Cuti N-2', 'Maksimal hari sisa cuti dua tahun lalu yang boleh dipakai tahun berjalan', 52, NOW(), NOW()),
  ('cuti', 'cuti_max_total_setahun', '24', 'number', 'Batas Total Cuti Tahunan Setahun', 'Akumulasi maksimal cuti tahunan yang boleh diambil dalam satu tahun', 53, NOW(), NOW()),
  ('cuti', 'cuti_hitung_hari_kerja', '1', 'boolean', 'Hitung Hari Kerja Saja', 'Jika aktif, Sabtu/Minggu dan hari libur tidak dihitung sebagai hari cuti', 54, NOW(), NOW()),
  ('cuti', 'cuti_prefix_nomor', 'CT', 'text', 'Prefix Nomor Pengajuan Cuti', 'Awalan nomor pengajuan, format: PREFIX/0001/ROMAWI/TAHUN', 55, NOW(), NOW()),
  ('cuti', 'cuti_format_surat', '800/{nomor}/LKL-PR/{romawi}/{tahun}', 'text', 'Format Nomor Surat Izin Cuti', 'Placeholder tersedia: {nomor}, {romawi}, {tahun}', 56, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();

-- 10. HARI LIBUR NASIONAL 2026 (dasar perhitungan hari kerja)
INSERT INTO `cuti_hari_libur` (`tanggal`, `keterangan`, `jenis`, `is_active`, `created_at`, `updated_at`) VALUES
  ('2026-01-01', 'Tahun Baru Masehi', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-02-17', 'Tahun Baru Imlek', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-03-19', 'Hari Raya Nyepi', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-03-20', 'Idul Fitri 1447 H', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-03-21', 'Idul Fitri 1447 H', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-04-03', 'Wafat Isa Almasih', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-05-01', 'Hari Buruh Internasional', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-05-14', 'Kenaikan Isa Almasih', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-05-27', 'Idul Adha 1447 H', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-05-31', 'Hari Raya Waisak', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-06-01', 'Hari Lahir Pancasila', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-06-16', 'Tahun Baru Islam 1448 H', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-08-17', 'Hari Kemerdekaan RI', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-08-25', 'Maulid Nabi Muhammad SAW', 'libur_nasional', 1, NOW(), NOW()),
  ('2026-12-25', 'Hari Raya Natal', 'libur_nasional', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `keterangan` = VALUES(`keterangan`),
  `updated_at` = NOW();
