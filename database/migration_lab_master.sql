-- Migration Master Data Laboratorium LIS PRO
-- Kategori, Metode, Parameter, Tarif (Manajemen, Dokter, Petugas, KSO, BHP, Total),
-- Nilai Rujukan & Klasifikasi Umur, dan Template / Paket Pemeriksaan Lab

USE `lispro`;

-- 1. Tabel Kategori / Departemen Laboratorium
CREATE TABLE IF NOT EXISTS `lab_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_code` VARCHAR(20) NOT NULL UNIQUE,
  `category_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Master Metode Pemeriksaan Laboratorium
CREATE TABLE IF NOT EXISTS `lab_methods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `method_code` VARCHAR(50) NOT NULL UNIQUE,
  `method_name` VARCHAR(150) NOT NULL,
  `standard_code` VARCHAR(100) DEFAULT NULL COMMENT 'Contoh: IKM/5.4.40/LKL-PR, Standar SOP',
  `instrument_name` VARCHAR(150) DEFAULT NULL COMMENT 'Nama Analyzer/Alat Otomatis, misal: Mindray BC-5380',
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Master Parameter / Uji Laboratorium Satuan
CREATE TABLE IF NOT EXISTS `lab_parameters` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `method_id` INT(11) DEFAULT NULL,
  `parent_id` INT(11) DEFAULT NULL COMMENT 'ID parent jika sub-parameter, misal Diff Count',
  `parameter_code` VARCHAR(50) NOT NULL UNIQUE,
  `parameter_name` VARCHAR(150) NOT NULL,
  `unit` VARCHAR(50) DEFAULT NULL COMMENT 'Satuan, misal g/dl, /mm 3, %, f l, pg',
  `data_type` ENUM('numeric', 'text', 'options') NOT NULL DEFAULT 'numeric',
  `options_list` TEXT DEFAULT NULL COMMENT 'Pilihan kualitatif dipisah koma jika data_type=options',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_param_category` (`category_id`),
  KEY `fk_param_method` (`method_id`),
  KEY `fk_param_parent` (`parent_id`),
  CONSTRAINT `fk_param_category` FOREIGN KEY (`category_id`) REFERENCES `lab_categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_param_method` FOREIGN KEY (`method_id`) REFERENCES `lab_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_param_parent` FOREIGN KEY (`parent_id`) REFERENCES `lab_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Nilai Rujukan & Klasifikasi Umur (Standard Reference Value by Age & Gender)
CREATE TABLE IF NOT EXISTS `lab_reference_ranges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `parameter_id` INT(11) NOT NULL,
  `gender` ENUM('ALL', 'L', 'P') NOT NULL DEFAULT 'ALL' COMMENT 'ALL=Semua, L=Laki-laki, P=Perempuan',
  `age_classification` ENUM('semua_umur', 'neonatus', 'bayi', 'anak', 'remaja', 'dewasa', 'lansia', 'hamil') NOT NULL DEFAULT 'semua_umur',
  `age_min_days` INT(11) NOT NULL DEFAULT 0 COMMENT 'Umur minimum dalam hari',
  `age_max_days` INT(11) NOT NULL DEFAULT 36500 COMMENT 'Umur maksimum dalam hari (100 tahun)',
  `age_label` VARCHAR(100) NOT NULL DEFAULT 'Dewasa',
  `min_val` DECIMAL(12,3) DEFAULT NULL COMMENT 'Batas bawah normal untuk tipe numerik',
  `max_val` DECIMAL(12,3) DEFAULT NULL COMMENT 'Batas atas normal untuk tipe numerik',
  `text_normal` VARCHAR(255) DEFAULT NULL COMMENT 'Nilai teks normal jika non-numerik, misal Negatif',
  `critical_low` DECIMAL(12,3) DEFAULT NULL COMMENT 'Batas kritis panik rendah',
  `critical_high` DECIMAL(12,3) DEFAULT NULL COMMENT 'Batas kritis panik tinggi',
  `reference_display` VARCHAR(255) NOT NULL COMMENT 'Teks tampilan pada cetak hasil, misal 13,5 - 16,5',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ref_parameter` (`parameter_id`),
  CONSTRAINT `fk_ref_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `lab_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Template / Paket Pemeriksaan Laboratorium
CREATE TABLE IF NOT EXISTS `lab_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `template_code` VARCHAR(50) NOT NULL UNIQUE,
  `template_name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tpl_category` (`category_id`),
  CONSTRAINT `fk_tpl_category` FOREIGN KEY (`category_id`) REFERENCES `lab_categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Item Parameter dalam Template
CREATE TABLE IF NOT EXISTS `lab_template_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_id` INT(11) NOT NULL,
  `parameter_id` INT(11) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tpl_param` (`template_id`, `parameter_id`),
  KEY `fk_ti_parameter` (`parameter_id`),
  CONSTRAINT `fk_ti_template` FOREIGN KEY (`template_id`) REFERENCES `lab_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ti_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `lab_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel Tarif Laboratorium (Parameter Satuan & Template Paket)
-- Lengkap dengan Jasa Manajemen, Jasa Dokter, Jasa Petugas Lab, KSO, BHP, dan Total
CREATE TABLE IF NOT EXISTS `lab_tariffs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_type` ENUM('parameter', 'template') NOT NULL DEFAULT 'parameter',
  `item_id` INT(11) NOT NULL COMMENT 'ID dari lab_parameters atau lab_templates',
  `tariff_group` ENUM('umum', 'perusahaan', 'bpjs', 'asuransi') NOT NULL DEFAULT 'umum',
  `jasa_manajemen` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jasa Manajemen / Operasional Lab',
  `jasa_dokter` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jasa Dokter Penanggung Jawab / Otorisasi',
  `jasa_petugas` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jasa Petugas / Analis Laboratorium',
  `kso` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'KSO (Kerja Sama Operasional sewa alat)',
  `bhp` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'BHP (Bahan Habis Pakai / Reagen / Tabung)',
  `total_tarif` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total = manajemen + dokter + petugas + kso + bhp',
  `notes` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tariff_item` (`item_type`, `item_id`, `tariff_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATA AWAL (SEED DATA)
-- Berdasarkan Formulir Lab Klinis LIS: HEMATOLOGI, URINALISA, KIMIA KLINIK
-- ========================================================

-- Kategori Lab
INSERT INTO `lab_categories` (`id`, `category_code`, `category_name`, `description`, `sort_order`) VALUES
(1, 'HEMATOLOGI', 'Hematologi', 'Pemeriksaan darah lengkap, hemostasis, dan komponen seluler darah', 1),
(2, 'URINALISA', 'Urinalisa', 'Pemeriksaan urine rutin, kimia urine, sedimen mikroskopis', 2),
(3, 'K_KLINIK', 'Kimia Klinik', 'Pemeriksaan fungsi hati, ginjal, profil lipid, gula darah, elektrolit', 3),
(4, 'PARASITOLOGI', 'Parasitologi', 'Pemeriksaan feses rutin, malaria, mikologi, parasit saluran cerna', 4),
(5, 'IMMUNOLOGI', 'Imunologi & Serologi', 'Penanda infeksi HBsAg, Anti-HCV, HIV, Widal, Typhoid, Dengue NS1', 5),
(6, 'KULTUR', 'Kultur & Mikrobiologi', 'Kultur kuman, tes resistensi antibiotik, pewarnaan gram/BTA', 6)
ON DUPLICATE KEY UPDATE `category_name`=VALUES(`category_name`);

-- Master Metode Pemeriksaan
INSERT INTO `lab_methods` (`id`, `method_code`, `method_name`, `standard_code`, `instrument_name`, `description`) VALUES
(1, 'MET-AUTO-BC', 'Automatic B.C Counter', 'IKM/5.4.40/LKL-PR', 'Hematology Analyzer 5-Diff', 'Penghitungan sel darah otomatis berbasis laser scatter dan impedansi'),
(2, 'MET-WESTERGREEN', 'Westergreen', 'IKM/5.4.12/LKL-PR', 'Rak & Pipet Westergreen Manual', 'Metode laju endap darah standar 1 jam'),
(3, 'MET-MANUAL-MIC', 'Manual (Mikroskopis)', 'IKM/5.4.05/LKL-PR', 'Mikroskop Binokuler & Counter Sel', 'Pemeriksaan hitung jenis leukosit dan sedimen preparat apus darah tepi'),
(4, 'MET-LEE-WHITE', 'Lee dan White', 'IKM/5.4.18/LKL-PR', 'Waterbath & Tabung Reaksi', 'Waktu pembekuan darah lengkap tabung bertahap'),
(5, 'MET-DUKE', 'Duke', 'IKM/5.4.19/LKL-PR', 'Lancet & Kertas Saring', 'Waktu perdarahan metode tusukan cuping telinga / jari'),
(6, 'MET-RUMPLE-LEEDE', 'Rumple Leede', 'IKM/5.4.22/LKL-PR', 'Tensimeter & Stetoskop', 'Uji fragilitas kapiler (RL Test) pembendungan manset'),
(7, 'MET-SLIDE-AGGLU', 'Slide / Aglutinasi', 'IKM/5.4.01/LKL-PR', 'Kaca Objek & Reagen Antisera', 'Penetapan golongan darah ABO dan Rhesus'),
(8, 'MET-PHOTOMETRIC', 'Fotometrik Kimia Klinis', 'IKM/5.4.30/LKL-PR', 'Chemistry Analyzer Otomatis', 'Pemeriksaan enzim, substrat, dan protein enzimatik'),
(9, 'MET-STRIP-REFLECT', 'Reflektansi Strip Urin', 'IKM/5.4.15/LKL-PR', 'Urine Analyzer Strip', 'Pemeriksaan kimiawi 10-11 parameter urine strip otomatis')
ON DUPLICATE KEY UPDATE `method_name`=VALUES(`method_name`);

-- Master Parameter Lab (Hematologi)
INSERT INTO `lab_parameters` (`id`, `category_id`, `method_id`, `parent_id`, `parameter_code`, `parameter_name`, `unit`, `data_type`, `sort_order`, `is_active`) VALUES
(1, 1, 1, NULL, 'HB', 'Haemoglobin', 'g/dl', 'numeric', 1, 1),
(2, 1, 1, NULL, 'LEU', 'Leukosit', '/mm 3', 'numeric', 2, 1),
(3, 1, 2, NULL, 'LED', 'L.E.D (Laju Endap Darah)', 'mm / jam', 'numeric', 3, 1),
(4, 1, 3, NULL, 'DIFF_COUNT', 'Diff. Count (Hitung Jenis)', '%', 'text', 4, 1),
(5, 1, 3, 4, 'DIFF_BASO', 'Basofil', '%', 'numeric', 5, 1),
(6, 1, 3, 4, 'DIFF_EOS', 'Eosinofil', '%', 'numeric', 6, 1),
(7, 1, 3, 4, 'DIFF_STAF', 'Staf (Batang)', '%', 'numeric', 7, 1),
(8, 1, 3, 4, 'DIFF_SEG', 'Segmen', '%', 'numeric', 8, 1),
(9, 1, 3, 4, 'DIFF_LIMF', 'Limfosit', '%', 'numeric', 9, 1),
(10, 1, 3, 4, 'DIFF_MONO', 'Monosit', '%', 'numeric', 10, 1),
(11, 1, 1, NULL, 'RBC', 'Eritrosit', '/mm 3', 'numeric', 11, 1),
(12, 1, 1, NULL, 'PLT', 'Trombosit', '/mm 3', 'numeric', 12, 1),
(13, 1, 1, NULL, 'HCT', 'Hematokrit', '%', 'numeric', 13, 1),
(14, 1, 4, NULL, 'CT', 'C.T (Clotting Time)', 'Menit', 'numeric', 14, 1),
(15, 1, 5, NULL, 'BT', 'B.T (Bleeding Time)', 'Menit', 'numeric', 15, 1),
(16, 1, 6, NULL, 'RL_TEST', 'R.L Test (Rumple Leede)', '-', 'options', 16, 1),
(17, 1, 3, NULL, 'RETIKULOSIT', 'Retikulosit', '%', 'numeric', 17, 1),
(18, 1, 3, NULL, 'RETRAKSI_BEKUAN', 'Retraksi Bekuan', '%', 'numeric', 18, 1),
(19, 1, 1, NULL, 'MCV', 'M.C.V', 'f l', 'numeric', 19, 1),
(20, 1, 1, NULL, 'MCH', 'M.C.H', 'pg', 'numeric', 20, 1),
(21, 1, 1, NULL, 'MCHC', 'M.C.H.C', '%', 'numeric', 21, 1),
(22, 1, 7, NULL, 'GOL_DARAH', 'Golongan Darah & Rhesus', '-', 'options', 22, 1)
ON DUPLICATE KEY UPDATE `parameter_name`=VALUES(`parameter_name`);

-- Nilai Rujukan & Klasifikasi Umur (Lengkap Pria/Wanita, Anak/Dewasa, dsb)
INSERT INTO `lab_reference_ranges` (`parameter_id`, `gender`, `age_classification`, `age_min_days`, `age_max_days`, `age_label`, `min_val`, `max_val`, `text_normal`, `critical_low`, `critical_high`, `reference_display`) VALUES
-- 1. Haemoglobin
(1, 'L', 'dewasa', 6570, 36500, 'Dewasa (Pria)', 13.500, 16.500, NULL, 7.000, 20.000, '♂ 13,5 - 16,5'),
(1, 'P', 'dewasa', 6570, 36500, 'Dewasa (Wanita)', 12.000, 15.000, NULL, 7.000, 20.000, '♀ 12,0 - 15,0'),
(1, 'ALL', 'anak', 365, 6569, 'Anak-anak (1-17 th)', 11.000, 14.500, NULL, 7.000, 18.000, '11,0 - 14,5'),
(1, 'ALL', 'bayi', 30, 364, 'Bayi (1-12 bln)', 10.500, 13.500, NULL, 6.500, 17.500, '10,5 - 13,5'),

-- 2. Leukosit
(2, 'L', 'dewasa', 6570, 36500, 'Dewasa (Pria)', 4700.000, 9600.000, NULL, 2000.000, 30000.000, 'Lk 4,7 - 9,6 rb'),
(2, 'P', 'dewasa', 6570, 36500, 'Dewasa (Wanita)', 4900.000, 12300.000, NULL, 2000.000, 30000.000, 'Pr 4,9 - 12,3 rb'),
(2, 'ALL', 'anak', 365, 6569, 'Anak-anak (1-12 th)', 5000.000, 14500.000, NULL, 2000.000, 30000.000, '5,0 - 14,5 rb'),

-- 3. LED
(3, 'L', 'semua_umur', 0, 36500, 'Pria (Semua Umur)', 0.000, 10.000, '< 10', NULL, 100.000, '♂ < 10'),
(3, 'P', 'semua_umur', 0, 36500, 'Wanita (Semua Umur)', 0.000, 15.000, '< 15', NULL, 100.000, '♀ < 15'),

-- 4. Diff Count Sub-items
(5, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 0.000, 1.000, NULL, NULL, NULL, '0 - 1'),
(6, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 1.000, 3.000, NULL, NULL, NULL, '1 - 3'),
(7, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 2.000, 6.000, NULL, NULL, NULL, '2 - 6'),
(8, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 50.000, 70.000, NULL, NULL, NULL, '50 - 70'),
(9, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 20.000, 40.000, NULL, NULL, NULL, '20 - 40'),
(10, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 2.000, 8.000, NULL, NULL, NULL, '2 - 8'),

-- 5. Eritrosit
(11, 'L', 'dewasa', 6570, 36500, 'Dewasa (Pria)', 4370000.000, 5630000.000, NULL, NULL, NULL, '♂ 4,37 - 5,63 jt'),
(11, 'P', 'dewasa', 6570, 36500, 'Dewasa (Wanita)', 3900000.000, 5100000.000, NULL, NULL, NULL, '♀ 3,9 - 5,1 jt'),

-- 6. Trombosit
(12, 'L', 'dewasa', 6570, 36500, 'Dewasa (Pria)', 145000.000, 355000.000, NULL, 50000.000, 1000000.000, 'Lk 145 - 355 rb'),
(12, 'P', 'dewasa', 6570, 36500, 'Dewasa (Wanita)', 150000.000, 330000.000, NULL, 50000.000, 1000000.000, 'Pr 150 - 330 rb'),

-- 7. Hematokrit
(13, 'L', 'dewasa', 6570, 36500, 'Dewasa (Pria)', 41.000, 50.000, NULL, 20.000, 60.000, 'Lk 41 - 50'),
(13, 'P', 'dewasa', 6570, 36500, 'Dewasa (Wanita)', 37.000, 45.000, NULL, 20.000, 60.000, 'Pr 37 - 45'),

-- 8-15. Parameter Lainnya
(14, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 9.000, 15.000, NULL, NULL, NULL, '9 - 15'),
(15, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 1.000, 3.000, NULL, NULL, NULL, '1 - 3'),
(16, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', NULL, NULL, 'Negatif', NULL, NULL, 'Negatif'),
(17, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 0.500, 1.500, NULL, NULL, NULL, '0,5 - 1,5'),
(18, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 40.000, 60.000, NULL, NULL, NULL, '40 - 60'),
(19, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 82.000, 92.000, NULL, NULL, NULL, '82 - 92'),
(20, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 27.000, 31.000, NULL, NULL, NULL, '27 - 31'),
(21, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', 32.000, 37.000, NULL, NULL, NULL, '32 - 37'),
(22, 'ALL', 'semua_umur', 0, 36500, 'Semua Umur', NULL, NULL, 'A / B / AB / O (Rh + / -)', NULL, NULL, 'Sesuai Aglutinasi');

-- 5. Template / Paket Laboratorium
INSERT INTO `lab_templates` (`id`, `category_id`, `template_code`, `template_name`, `description`, `sort_order`, `is_active`) VALUES
(1, 1, 'PKT-DL-01', 'Darah Lengkap 5 Diff (Hematologi Rutin)', 'Pemeriksaan hematologi lengkap otomatis meliputi Hb, Leukosit, LED, Diff Count, Eritrosit, Trombosit, Hematokrit, dan Indeks Eritrosit (MCV, MCH, MCHC)', 1, 1),
(2, 1, 'PKT-HEMOSTASIS-01', 'Skrining Hemostasis Sederhana', 'Pemeriksaan fungsi hemostasis pembekuan darah: CT, BT, Rumple Leede, Retraksi Bekuan', 2, 1),
(3, 2, 'PKT-URIN-01', 'Urin Rutin Lengkap', 'Pemeriksaan makroskopis, kimia 10 parameter strip, dan sedimen mikroskopis urine', 3, 1)
ON DUPLICATE KEY UPDATE `template_name`=VALUES(`template_name`);

-- Items dalam Template 1 (Darah Lengkap 5 Diff)
INSERT INTO `lab_template_items` (`template_id`, `parameter_id`, `sort_order`) VALUES
(1, 1, 1),   -- Hb
(1, 2, 2),   -- Leukosit
(1, 3, 3),   -- LED
(1, 5, 4),   -- Basofil
(1, 6, 5),   -- Eosinofil
(1, 7, 6),   -- Staf
(1, 8, 7),   -- Segmen
(1, 9, 8),   -- Limfosit
(1, 10, 9),  -- Monosit
(1, 11, 10), -- Eritrosit
(1, 12, 11), -- Trombosit
(1, 13, 12), -- Hematokrit
(1, 19, 13), -- MCV
(1, 20, 14), -- MCH
(1, 21, 15)  -- MCHC
ON DUPLICATE KEY UPDATE `sort_order`=VALUES(`sort_order`);

-- Items dalam Template 2 (Hemostasis Sederhana)
INSERT INTO `lab_template_items` (`template_id`, `parameter_id`, `sort_order`) VALUES
(2, 14, 1),  -- CT
(2, 15, 2),  -- BT
(2, 16, 3),  -- Rumple Leede
(2, 18, 4)   -- Retraksi Bekuan
ON DUPLICATE KEY UPDATE `sort_order`=VALUES(`sort_order`);

-- 7. Tarif Berdasarkan Parameter Satuan & Berdasarkan Template (Rincian Jasa Manajemen, Jasa Dokter, Jasa Petugas, KSO, BHP, Total)
-- Tarif Template Paket
INSERT INTO `lab_tariffs` (`item_type`, `item_id`, `tariff_group`, `jasa_manajemen`, `jasa_dokter`, `jasa_petugas`, `kso`, `bhp`, `total_tarif`, `notes`) VALUES
('template', 1, 'umum', 15000.00, 20000.00, 15000.00, 15000.00, 15000.00, 80000.00, 'Paket Hematologi Darah Lengkap 5 Diff Tarif Pasien Umum'),
('template', 1, 'perusahaan', 12000.00, 16000.00, 12000.00, 12000.00, 13000.00, 65000.00, 'Paket Hematologi Darah Lengkap Rekanan/Perusahaan (Diskon Khusus)'),
('template', 2, 'umum', 10000.00, 15000.00, 10000.00, 5000.00, 10000.00, 50000.00, 'Paket Hemostasis Sederhana'),

-- Tarif Parameter Satuan
('parameter', 1, 'umum', 3000.00, 5000.00, 4000.00, 4000.00, 4000.00, 20000.00, 'Haemoglobin Satuan'),
('parameter', 2, 'umum', 3000.00, 5000.00, 4000.00, 4000.00, 4000.00, 20000.00, 'Leukosit Satuan'),
('parameter', 3, 'umum', 2000.00, 4000.00, 4000.00, 2000.00, 3000.00, 15000.00, 'Laju Endap Darah (LED) Westergreen'),
('parameter', 11, 'umum', 3000.00, 5000.00, 4000.00, 4000.00, 4000.00, 20000.00, 'Eritrosit Satuan'),
('parameter', 12, 'umum', 3000.00, 5000.00, 4000.00, 4000.00, 4000.00, 20000.00, 'Trombosit Satuan'),
('parameter', 13, 'umum', 2000.00, 4000.00, 4000.00, 2000.00, 3000.00, 15000.00, 'Hematokrit Satuan'),
('parameter', 14, 'umum', 2000.00, 4000.00, 4000.00, 1000.00, 4000.00, 15000.00, 'Masa Pembekuan (CT)'),
('parameter', 15, 'umum', 2000.00, 4000.00, 4000.00, 1000.00, 4000.00, 15000.00, 'Masa Perdarahan (BT)'),
('parameter', 16, 'umum', 2000.00, 5000.00, 5000.00, 1000.00, 2000.00, 15000.00, 'Rumple Leede Test'),
('parameter', 22, 'umum', 3000.00, 5000.00, 5000.00, 2000.00, 5000.00, 20000.00, 'Golongan Darah ABO & Rhesus')
ON DUPLICATE KEY UPDATE `total_tarif`=VALUES(`total_tarif`);

-- Pastikan Menu RBAC terdaftar
UPDATE `menus` SET `menu_name` = 'Tarif & Parameter Lab', `menu_url` = 'tarif', `menu_icon` = 'list-plus' WHERE `id` = 8;

INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`)
VALUES 
(16, 7, 'Template / Paket Lab', 'tarif/template', 'layers', 3, 1),
(17, 7, 'Metode Pemeriksaan', 'tarif/metode', 'microscope', 4, 1)
ON DUPLICATE KEY UPDATE `menu_name`=VALUES(`menu_name`), `menu_url`=VALUES(`menu_url`), `menu_icon`=VALUES(`menu_icon`);

-- Role Permissions untuk Admin & Petugas
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`)
VALUES
(1, 8, 1, 1, 1, 1),
(1, 16, 1, 1, 1, 1),
(1, 17, 1, 1, 1, 1),
(2, 8, 1, 0, 0, 0),
(2, 16, 1, 0, 0, 0),
(2, 17, 1, 0, 0, 0),
(3, 8, 1, 0, 0, 0),
(3, 16, 1, 0, 0, 0),
(3, 17, 1, 0, 0, 0),
(4, 8, 1, 1, 1, 0),
(4, 16, 1, 1, 1, 0),
(4, 17, 1, 1, 1, 0)
ON DUPLICATE KEY UPDATE `can_view`=VALUES(`can_view`), `can_create`=VALUES(`can_create`), `can_edit`=VALUES(`can_edit`), `can_delete`=VALUES(`can_delete`);
