-- Migration Modul Kesehatan Lingkungan (Pilot: Air Minum)
-- Sumber baku mutu: file induk LHU klien di ALLDOC/LHU Lingkungan/Air Minum/A File Master
--   - Permen Kes RI No. 2 Tahun 2023 (Air Minum)
--   - Permen Kes No. 492/MENKES/PER/IV/2010 (Air Minum)
-- Nilai NILAI RUJUKAN / THRESHOLD VALUE, satuan, dan metode diambil verbatim dari dokumen tsb.
-- Idempoten: CREATE TABLE IF NOT EXISTS + ON DUPLICATE KEY UPDATE.

USE `lispro`;

-- ============================================================
-- 1. SKEMA
-- ============================================================

-- Tipe sampel lingkungan (Air Minum, Air Bersih, Air Limbah, Badan Air, Udara, dst.)
CREATE TABLE IF NOT EXISTS `env_sample_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Standar / regulasi baku mutu per tipe sampel
CREATE TABLE IF NOT EXISTS `env_standards` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sample_type_id` INT(11) NOT NULL,
  `code` VARCHAR(40) NOT NULL UNIQUE,
  `name` VARCHAR(180) NOT NULL,
  `regulation_ref` VARCHAR(200) DEFAULT NULL COMMENT 'Rujukan peraturan resmi untuk kaki LHU',
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_std_sampletype` (`sample_type_id`),
  CONSTRAINT `fk_std_sampletype` FOREIGN KEY (`sample_type_id`) REFERENCES `env_sample_types` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Katalog parameter uji lingkungan (per tipe sampel)
CREATE TABLE IF NOT EXISTS `env_parameters` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sample_type_id` INT(11) NOT NULL,
  `sub_category` ENUM('fisika','kimia','mikrobiologi','lainnya') NOT NULL DEFAULT 'kimia',
  `param_code` VARCHAR(40) NOT NULL UNIQUE,
  `param_name` VARCHAR(150) NOT NULL,
  `param_name_en` VARCHAR(150) DEFAULT NULL,
  `unit` VARCHAR(40) DEFAULT NULL,
  `method_default` VARCHAR(100) DEFAULT NULL COMMENT 'Metode uji default (mis. SNI 6989.27:2019)',
  `data_type` ENUM('numeric','text') NOT NULL DEFAULT 'numeric',
  `is_accredited` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Dalam lingkup akreditasi (tanda *). Set dari sertifikat akreditasi klien.',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_param_sampletype` (`sample_type_id`),
  CONSTRAINT `fk_envparam_sampletype` FOREIGN KEY (`sample_type_id`) REFERENCES `env_sample_types` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Baku mutu per (parameter, standar). threshold_display = verbatim utk cetak; kolom mesin utk flag.
CREATE TABLE IF NOT EXISTS `env_thresholds` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `standard_id` INT(11) NOT NULL,
  `parameter_id` INT(11) NOT NULL,
  `threshold_display` VARCHAR(100) NOT NULL COMMENT 'Teks NILAI RUJUKAN persis dokumen, mis. < 300 / 6,5 - 8,5 / Tidak Berbau',
  `operator` ENUM('max','min','range','equal','text','none') NOT NULL DEFAULT 'max'
     COMMENT 'max: hasil<=max; min: hasil>=min; range: min..max; text: samakan teks; none: tak diflag otomatis',
  `min_val` DECIMAL(16,5) DEFAULT NULL,
  `max_val` DECIMAL(16,5) DEFAULT NULL,
  `text_expected` VARCHAR(100) DEFAULT NULL,
  `unit_override` VARCHAR(40) DEFAULT NULL COMMENT 'Satuan khusus standar ini bila beda dari parameter',
  `method_override` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_threshold` (`standard_id`, `parameter_id`),
  KEY `fk_thr_param` (`parameter_id`),
  CONSTRAINT `fk_thr_standard` FOREIGN KEY (`standard_id`) REFERENCES `env_standards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_thr_param` FOREIGN KEY (`parameter_id`) REFERENCES `env_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order pengujian sampel lingkungan (FPPS, koordinat, personel, waktu, standar terpilih)
CREATE TABLE IF NOT EXISTS `env_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(60) NOT NULL UNIQUE COMMENT 'No. LHU / order internal',
  `fpps_number` VARCHAR(60) DEFAULT NULL COMMENT 'No. FPPS (Formulir Permintaan Pengujian Sampel)',
  `lab_number` VARCHAR(60) DEFAULT NULL COMMENT 'No. Laboratorium',
  `sample_type_id` INT(11) NOT NULL,
  `standard_id` INT(11) DEFAULT NULL COMMENT 'Baku mutu acuan yang dipilih untuk order ini',
  `customer_name` VARCHAR(180) NOT NULL,
  `customer_address` VARCHAR(255) DEFAULT NULL,
  `contact_person` VARCHAR(120) DEFAULT NULL,
  `contact_phone` VARCHAR(50) DEFAULT NULL,
  `sample_description` VARCHAR(255) DEFAULT NULL COMMENT 'Deskripsi Sampel',
  `sample_location` VARCHAR(255) DEFAULT NULL COMMENT 'Lokasi / titik sampling',
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `sampling_by` VARCHAR(120) DEFAULT NULL COMMENT 'Personel pengambil sampel',
  `sampling_at` DATETIME DEFAULT NULL,
  `received_by` VARCHAR(120) DEFAULT NULL COMMENT 'Personel penerima sampel',
  `received_at` DATETIME DEFAULT NULL,
  `analysis_start` DATE DEFAULT NULL,
  `analysis_end` DATE DEFAULT NULL,
  `status` ENUM('registered','sampling','testing','validasi','completed','cancelled') NOT NULL DEFAULT 'registered',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ord_sampletype` (`sample_type_id`),
  KEY `fk_ord_standard` (`standard_id`),
  KEY `idx_env_order_status` (`status`),
  CONSTRAINT `fk_ord_sampletype` FOREIGN KEY (`sample_type_id`) REFERENCES `env_sample_types` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ord_standard` FOREIGN KEY (`standard_id`) REFERENCES `env_standards` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hasil uji per parameter (nilai verbatim + nilai numerik utk pembanding). Status lewat-ambang DIHITUNG, tidak disimpan.
CREATE TABLE IF NOT EXISTS `env_order_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `parameter_id` INT(11) NOT NULL,
  `result_value` VARCHAR(100) DEFAULT NULL COMMENT 'Hasil uji verbatim utk cetak (mis. <0,0005, Tidak Berbau, 42)',
  `result_numeric` DECIMAL(16,5) DEFAULT NULL COMMENT 'Bentuk numerik utk pembanding ambang (NULL bila non-numerik)',
  `method_used` VARCHAR(100) DEFAULT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_param` (`order_id`, `parameter_id`),
  KEY `fk_res_param` (`parameter_id`),
  CONSTRAINT `fk_res_order` FOREIGN KEY (`order_id`) REFERENCES `env_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_res_param` FOREIGN KEY (`parameter_id`) REFERENCES `env_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. SEED — TIPE SAMPEL
-- ============================================================
INSERT INTO `env_sample_types` (`id`, `code`, `name`, `description`, `sort_order`, `is_active`) VALUES
(1, 'AIR_MINUM',  'Air Minum',  'Air minum / air siap konsumsi (DAMIU, AMDK, PDAM siap minum)', 1, 1),
(2, 'AIR_BERSIH', 'Air Bersih', 'Air untuk keperluan higiene sanitasi (sumur bor, PDAM, air tanah)', 2, 1),
(3, 'AIR_LIMBAH', 'Air Limbah', 'Air limbah domestik, fasilitas pelayanan kesehatan, dan industri', 3, 1),
(4, 'BADAN_AIR',  'Badan Air',  'Air sungai, danau, waduk, dan badan air permukaan lainnya', 4, 1),
(5, 'UDARA',      'Udara',      'Kualitas udara ambien dan udara dalam ruang / lingkungan kerja', 5, 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `sort_order`=VALUES(`sort_order`), `is_active`=VALUES(`is_active`);

-- ============================================================
-- 3. SEED — STANDAR BAKU MUTU (Seluruh Tipe Sampel)
-- ============================================================
INSERT INTO `env_standards` (`id`, `sample_type_id`, `code`, `name`, `regulation_ref`, `description`, `sort_order`, `is_active`) VALUES
-- 1. Air Minum
(1,  1, 'PERMENKES_2_2023',        'Permenkes RI No. 2 Tahun 2023 (Air Minum)', 'Permen Kes RI No. 2 Tahun 2023', 'Standar baku mutu kesehatan lingkungan untuk media air minum', 1, 1),
(2,  1, 'PERMENKES_492_2010',      'Permenkes No. 492/MENKES/PER/IV/2010 (Air Minum)', 'Permen Kes No. 492/MENKES/PER/IV/2010', 'Persyaratan kualitas air minum', 2, 1),
-- 2. Air Bersih / Higiene Sanitasi
(3,  2, 'PERMENKES_2_2023_AB',     'Permenkes RI No. 2 Tahun 2023 (Air Higiene Sanitasi)', 'Permen Kes RI No. 2 Tahun 2023 Lampiran Bab II', 'Standar baku mutu kesehatan lingkungan media air untuk keperluan higiene sanitasi', 1, 1),
(4,  2, 'PERMENKES_32_2017',       'Permenkes RI No. 32 Tahun 2017 (Air Higiene Sanitasi)', 'Permen Kes RI No. 32 Tahun 2017', 'Standar baku mutu kesehatan lingkungan dan persyaratan kesehatan air untuk keperluan higiene sanitasi', 2, 1),
-- 3. Air Limbah
(5,  3, 'PERMENLHK_P68_2016',      'Permen LHK No. P.68/2016 (Air Limbah Domestik)', 'Permen LHK No. P.68/Menlhk/Setjen/Kum.1/8/2016', 'Baku mutu air limbah domestik tersendiri / IPAL terpadu', 1, 1),
(6,  3, 'PERMENLH_5_2014_FASYANKES','Permen LH No. 5 Tahun 2014 (Air Limbah Fasyankes)', 'Permen LH RI No. 5 Tahun 2014 Lampiran XLIV', 'Baku mutu air limbah bagi usaha dan/atau kegiatan fasilitas pelayanan kesehatan', 2, 1),
-- 4. Badan Air (Air Permukaan / Sungai)
(7,  4, 'PP_22_2021_KELAS_1',      'PP RI No. 22 Tahun 2021 - Air Sungai Kelas I', 'PP RI No. 22 Tahun 2021 Lampiran VI (Kelas I)', 'Peruntukan air baku air minum dan peruntukan lain yang mempersyaratkan mutu yang sama', 1, 1),
(8,  4, 'PP_22_2021_KELAS_2',      'PP RI No. 22 Tahun 2021 - Air Sungai Kelas II', 'PP RI No. 22 Tahun 2021 Lampiran VI (Kelas II)', 'Peruntukan prasarana/sarana rekreasi air, pembudidayaan ikan air tawar, peternakan, dan pertanaman', 2, 1),
(9,  4, 'PP_22_2021_KELAS_3',      'PP RI No. 22 Tahun 2021 - Air Sungai Kelas III', 'PP RI No. 22 Tahun 2021 Lampiran VI (Kelas III)', 'Peruntukan pembudidayaan ikan air tawar, peternakan, dan air untuk mengairi pertanaman', 3, 1),
(10, 4, 'PP_22_2021_KELAS_4',      'PP RI No. 22 Tahun 2021 - Air Sungai Kelas IV', 'PP RI No. 22 Tahun 2021 Lampiran VI (Kelas IV)', 'Peruntukan mengairi pertanaman dan/atau peruntukan lain yang mempersyaratkan mutu air yang sama', 4, 1),
-- 5. Udara
(11, 5, 'PP_22_2021_UDARA_AMBIEN', 'PP RI No. 22 Tahun 2021 (Udara Ambien)', 'PP RI No. 22 Tahun 2021 Lampiran VII', 'Baku mutu udara ambien nasional', 1, 1),
(12, 5, 'PERMENKES_2_2023_UDARA',  'Permenkes RI No. 2 Tahun 2023 (Udara Dalam Ruang)', 'Permen Kes RI No. 2 Tahun 2023 Bab II Media Udara', 'Standar baku mutu kesehatan lingkungan untuk media udara dalam ruang', 2, 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `regulation_ref`=VALUES(`regulation_ref`), `description`=VALUES(`description`), `sort_order`=VALUES(`sort_order`), `is_active`=VALUES(`is_active`);

-- ============================================================
-- 4. SEED — PARAMETER (Air Minum, Air Bersih, Air Limbah, Badan Air, Udara)
-- ============================================================
INSERT INTO `env_parameters` (`id`, `sample_type_id`, `sub_category`, `param_code`, `param_name`, `param_name_en`, `unit`, `method_default`, `data_type`, `is_accredited`, `sort_order`) VALUES
-- [TIPE 1: AIR MINUM]
(1,  1, 'fisika', 'AM-SUHU',      'Suhu',        'Temperature',       'oC',        'SNI 06-6989.23-2005', 'numeric', 1, 1),
(2,  1, 'fisika', 'AM-TDS',       'TDS',         'Total Dissolved Solids', 'mg/L', 'SNI 6989.27:2019',   'numeric', 1, 2),
(3,  1, 'fisika', 'AM-KEKERUHAN', 'Kekeruhan',   'Turbidity',         'Skala NTU', 'SNI 06-6989.25-2005', 'numeric', 1, 3),
(4,  1, 'fisika', 'AM-WARNA',     'Warna',       'Colour',            'TCU',       'SNI 06-6989.24-2005', 'numeric', 1, 4),
(5,  1, 'fisika', 'AM-BAU',       'Bau',         'Odour',             '-',         'SNI 06-2413-1991',    'text',    0, 5),
(6,  1, 'fisika', 'AM-RASA',      'Rasa',        'Taste',             '-',         'SNI 06-2413-1991',    'text',    0, 6),
(7,  1, 'kimia',  'AM-PH',        'pH',          'pH',                '-',         'SNI 06-6989.11-2019', 'numeric', 1, 10),
(8,  1, 'kimia',  'AM-NITRAT',    'Nitrat sebagai N', 'Nitrate as N', 'mg/L',      'APHA 2012',           'numeric', 1, 11),
(9,  1, 'kimia',  'AM-NITRIT',    'Nitrit sebagai N', 'Nitrite as N', 'mg/L',      'SNI 06-6989-9-2004',  'numeric', 1, 12),
(10, 1, 'kimia',  'AM-CR6',       'Kromium valensi 6', 'Hexavalent Chromium', 'mg/L', 'SNI 6989.84:2019', 'numeric', 1, 13),
(11, 1, 'kimia',  'AM-CR-TOTAL',  'Kromium Total', 'Total Chromium',   'mg/L',      'SNI 06-6989.12-2004', 'numeric', 1, 14),
(12, 1, 'kimia',  'AM-BESI',      'Besi (sebagai logam terlarut)', 'Iron (dissolved)', 'mg/L', 'SNI 6989.84:2019', 'numeric', 1, 15),
(13, 1, 'kimia',  'AM-MANGAN',    'Mangan (sebagai logam terlarut)', 'Manganese (dissolved)', 'mg/L', 'SNI 6989.84:2019', 'numeric', 1, 16),
(14, 1, 'kimia',  'AM-SISA-KHLOR','Sisa Khlor',  'Residual Chlorine', 'mg/L',      'USEPA',               'numeric', 0, 17),
(15, 1, 'kimia',  'AM-ARSEN',     'Arsen',       'Arsenic',           'mg/L',      'SNI 6989.84:2019',    'numeric', 1, 18),
(16, 1, 'kimia',  'AM-KADMIUM',   'Kadmium (sebagai logam terlarut)', 'Cadmium (dissolved)', 'mg/L', 'SNI 6989.84:2019', 'numeric', 1, 19),
(17, 1, 'kimia',  'AM-TIMBAL',    'Timbal (sebagai logam terlarut)', 'Lead (dissolved)', 'mg/L', 'SNI 6989-84:2019', 'numeric', 1, 20),
(18, 1, 'kimia',  'AM-FLUORIDA',  'Fluorida',    'Fluoride',          'mg/L',      'SNI 06.6989.29.2005', 'numeric', 1, 21),
(19, 1, 'kimia',  'AM-ALUMINIUM', 'Aluminium',   'Aluminium',         'mg/L',      'SNI 06-6989.35-2005', 'numeric', 1, 22),
(20, 1, 'kimia',  'AM-KESADAHAN', 'Kesadahan (CaCO3)', 'Hardness (CaCO3)', 'mg/L', 'SNI 06-6989.12-2004', 'numeric', 1, 23),
(21, 1, 'kimia',  'AM-KHLORIDA',  'Khlorida',    'Chloride',          'mg/L',      'SNI 6989.19:2019',    'numeric', 1, 24),
(22, 1, 'kimia',  'AM-AMONIAK',   'Amoniak',     'Ammonia',           'mg/L',      'USEPA',               'numeric', 0, 25),
(23, 1, 'kimia',  'AM-SENG',      'Seng (sebagai logam terlarut)', 'Zinc (dissolved)', 'mg/L', 'SNI 6989-84:2019', 'numeric', 1, 26),
(24, 1, 'kimia',  'AM-SIANIDA',   'Sianida',     'Cyanide',           'mg/L',      'USEPA',               'numeric', 0, 27),
(25, 1, 'kimia',  'AM-SULFAT',    'Sulfat',      'Sulphate',          'mg/L',      'SNI 6989-20:2019',    'numeric', 1, 28),
(26, 1, 'kimia',  'AM-TEMBAGA',   'Tembaga (sebagai logam terlarut)', 'Copper (dissolved)', 'mg/L', 'SNI 6989-84:2019', 'numeric', 1, 29),
(27, 1, 'mikrobiologi', 'AM-ECOLI',     'E.coli',         'E.coli',          'CFU/100 ml', 'Biakan', 'numeric', 1, 40),
(28, 1, 'mikrobiologi', 'AM-TCOLI',     'Total Coliform', 'Total Coliform',  'CFU/100 ml', 'Biakan', 'numeric', 1, 41),
(29, 1, 'mikrobiologi', 'AM-COLI-TINJA','Coliform Tinja', 'Faecal Coliform', 'MPN/100 ml', 'Biakan Tabung Ganda', 'numeric', 0, 42),

-- [TIPE 2: AIR BERSIH / HIGIENE SANITASI]
(30, 2, 'fisika', 'AB-KEKERUHAN', 'Kekeruhan',   'Turbidity',              'NTU',        'SNI 06-6989.25-2005', 'numeric', 1, 1),
(31, 2, 'fisika', 'AB-WARNA',     'Warna',       'Colour',                 'TCU',        'SNI 06-6989.24-2005', 'numeric', 1, 2),
(32, 2, 'fisika', 'AB-TDS',       'Zat Padat Terlarut (TDS)', 'Total Dissolved Solids', 'mg/L', 'SNI 6989.27:2019', 'numeric', 1, 3),
(33, 2, 'fisika', 'AB-SUHU',      'Suhu',        'Temperature',            'oC',         'SNI 06-6989.23-2005', 'numeric', 1, 4),
(34, 2, 'fisika', 'AB-BAU',       'Bau',         'Odour',                  '-',          'SNI 06-2413-1991',    'text',    0, 5),
(35, 2, 'fisika', 'AB-RASA',      'Rasa',        'Taste',                  '-',          'SNI 06-2413-1991',    'text',    0, 6),
(36, 2, 'kimia',  'AB-PH',        'pH',          'pH',                     '-',          'SNI 06-6989.11-2019', 'numeric', 1, 10),
(37, 2, 'kimia',  'AB-BESI',      'Besi (Fe)',   'Iron (Fe)',              'mg/L',       'SNI 6989.84:2019',    'numeric', 1, 11),
(38, 2, 'kimia',  'AB-FLUORIDA',  'Fluorida',    'Fluoride',               'mg/L',       'SNI 06-6989.29-2005', 'numeric', 1, 12),
(39, 2, 'kimia',  'AB-KESADAHAN', 'Kesadahan (CaCO3)', 'Hardness (CaCO3)', 'mg/L',       'SNI 06-6989.12-2004', 'numeric', 1, 13),
(40, 2, 'kimia',  'AB-MANGAN',    'Mangan (Mn)', 'Manganese (Mn)',         'mg/L',       'SNI 6989.84:2019',    'numeric', 1, 14),
(41, 2, 'kimia',  'AB-NITRAT',    'Nitrat (sebagai N)', 'Nitrate as N',    'mg/L',       'APHA 4500-NO3-B',     'numeric', 1, 15),
(42, 2, 'kimia',  'AB-NITRIT',    'Nitrit (sebagai N)', 'Nitrite as N',    'mg/L',       'SNI 06-6989.9-2004',  'numeric', 1, 16),
(43, 2, 'kimia',  'AB-SIANIDA',   'Sianida',     'Cyanide',                'mg/L',       'USEPA',               'numeric', 0, 17),
(44, 2, 'kimia',  'AB-DETERJEN',  'Deterjen (MBAS)', 'Surfactants (MBAS)', 'mg/L',       'SNI 06-6989.51-2005', 'numeric', 1, 18),
(45, 2, 'kimia',  'AB-KADMIUM',   'Kadmium (Cd)', 'Cadmium (Cd)',          'mg/L',       'SNI 6989.84:2019',    'numeric', 1, 19),
(46, 2, 'kimia',  'AB-CR6',       'Kromium Valensi 6 (Cr6+)', 'Hexavalent Chromium', 'mg/L', 'SNI 6989.71:2009', 'numeric', 1, 20),
(47, 2, 'kimia',  'AB-SENG',      'Seng (Zn)',   'Zinc (Zn)',              'mg/L',       'SNI 6989.84:2019',    'numeric', 1, 21),
(48, 2, 'kimia',  'AB-SULFAT',    'Sulfat (SO4)', 'Sulphate',              'mg/L',       'SNI 6989.20:2019',    'numeric', 1, 22),
(49, 2, 'kimia',  'AB-TIMBAL',    'Timbal (Pb)', 'Lead (Pb)',              'mg/L',       'SNI 6989.84:2019',    'numeric', 1, 23),
(50, 2, 'kimia',  'AB-KMNO4',     'Zat Organik (KMnO4)', 'Organic Matter (KMnO4)', 'mg/L', 'SNI 06-6989.22-2004', 'numeric', 1, 24),
(51, 2, 'mikrobiologi', 'AB-ECOLI', 'E. coli',   'E. coli',                'CFU/100 ml', 'APHA 9222',           'numeric', 1, 40),
(52, 2, 'mikrobiologi', 'AB-TCOLI', 'Total Coliform', 'Total Coliform',    'CFU/100 ml', 'APHA 9222',           'numeric', 1, 41),

-- [TIPE 3: AIR LIMBAH]
(53, 3, 'fisika', 'AL-SUHU',      'Suhu',        'Temperature',            'oC',         'SNI 06-6989.23-2005', 'numeric', 1, 1),
(54, 3, 'fisika', 'AL-TSS',       'Zat Padat Tersuspensi (TSS)', 'Total Suspended Solids', 'mg/L', 'SNI 6989.3:2019', 'numeric', 1, 2),
(55, 3, 'fisika', 'AL-TDS',       'Zat Padat Terlarut (TDS)', 'Total Dissolved Solids', 'mg/L', 'SNI 6989.27:2019', 'numeric', 1, 3),
(56, 3, 'kimia',  'AL-PH',        'pH',          'pH',                     '-',          'SNI 6989.11:2019',    'numeric', 1, 10),
(57, 3, 'kimia',  'AL-BOD',       'BOD5',        'Biochemical Oxygen Demand', 'mg/L',    'SNI 6989.72:2009',    'numeric', 1, 11),
(58, 3, 'kimia',  'AL-COD',       'COD',         'Chemical Oxygen Demand', 'mg/L',       'SNI 6989.2:2019',     'numeric', 1, 12),
(59, 3, 'kimia',  'AL-MINYAK',    'Minyak dan Lemak', 'Oil and Grease',    'mg/L',       'SNI 6989.10:2011',    'numeric', 1, 13),
(60, 3, 'kimia',  'AL-AMONIAK',   'Amoniak (NH3-N)', 'Ammonia Nitrogen',   'mg/L',       'SNI 06-6989.30-2005', 'numeric', 1, 14),
(61, 3, 'kimia',  'AL-MBAS',      'Deterjen Total (MBAS)', 'Surfactants (MBAS)', 'mg/L', 'SNI 06-6989.51-2005', 'numeric', 1, 15),
(62, 3, 'kimia',  'AL-FENOL',     'Fenol',       'Phenol',                 'mg/L',       'SNI 06-6989.21-2004', 'numeric', 0, 16),
(63, 3, 'kimia',  'AL-KHLOR',     'Sisa Khlor (Cl2)', 'Free Chlorine',     'mg/L',       'APHA 4500-Cl-G',      'numeric', 0, 17),
(64, 3, 'mikrobiologi', 'AL-TCOLI', 'Total Coliform', 'Total Coliform',    'MPN/100 ml', 'APHA 9221-B',         'numeric', 1, 40),

-- [TIPE 4: BADAN AIR / AIR PERMUKAAN]
(65, 4, 'fisika', 'BA-SUHU',      'Suhu',        'Temperature',            'oC',         'SNI 06-6989.23-2005', 'numeric', 1, 1),
(66, 4, 'fisika', 'BA-TDS',       'Zat Padat Terlarut (TDS)', 'Total Dissolved Solids', 'mg/L', 'SNI 6989.27:2019', 'numeric', 1, 2),
(67, 4, 'fisika', 'BA-TSS',       'Zat Padat Tersuspensi (TSS)', 'Total Suspended Solids', 'mg/L', 'SNI 6989.3:2019', 'numeric', 1, 3),
(68, 4, 'fisika', 'BA-WARNA',     'Warna',       'Colour',                 'Pt-Co',      'SNI 6989.80:2011',    'numeric', 1, 4),
(69, 4, 'kimia',  'BA-PH',        'pH',          'pH',                     '-',          'SNI 6989.11:2019',    'numeric', 1, 10),
(70, 4, 'kimia',  'BA-BOD',       'BOD5',        'Biochemical Oxygen Demand', 'mg/L',    'SNI 6989.72:2009',    'numeric', 1, 11),
(71, 4, 'kimia',  'BA-COD',       'COD',         'Chemical Oxygen Demand', 'mg/L',       'SNI 6989.2:2019',     'numeric', 1, 12),
(72, 4, 'kimia',  'BA-DO',        'Oksigen Terlarut (DO)', 'Dissolved Oxygen', 'mg/L',   'SNI 06-6989.14-2004', 'numeric', 1, 13),
(73, 4, 'kimia',  'BA-FOSFAT',    'Total Fosfat (sebagai P)', 'Total Phosphate as P', 'mg/L', 'SNI 06-6989.31-2005', 'numeric', 1, 14),
(74, 4, 'kimia',  'BA-NITRAT',    'Nitrat (sebagai N)', 'Nitrate as N',    'mg/L',       'APHA 4500-NO3-B',     'numeric', 1, 15),
(75, 4, 'kimia',  'BA-AMONIAK',   'Amoniak Total (NH3-N)', 'Total Ammonia','mg/L',       'SNI 06-6989.30-2005', 'numeric', 1, 16),
(76, 4, 'kimia',  'BA-BESI',      'Besi Terlarut (Fe)', 'Dissolved Iron',  'mg/L',       'SNI 6989.84:2019',    'numeric', 1, 17),
(77, 4, 'kimia',  'BA-MANGAN',    'Mangan Terlarut (Mn)', 'Dissolved Manganese', 'mg/L', 'SNI 6989.84:2019',    'numeric', 1, 18),
(78, 4, 'kimia',  'BA-TEMBAGA',   'Tembaga Terlarut (Cu)', 'Dissolved Copper', 'mg/L',   'SNI 6989.84:2019',    'numeric', 1, 19),
(79, 4, 'kimia',  'BA-SENG',      'Seng Terlarut (Zn)', 'Dissolved Zinc',  'mg/L',       'SNI 6989.84:2019',    'numeric', 1, 20),
(80, 4, 'kimia',  'BA-CR6',       'Kromium Heksavalen (Cr6+)', 'Hexavalent Chromium', 'mg/L', 'SNI 6989.71:2009','numeric', 1, 21),
(81, 4, 'kimia',  'BA-KADMIUM',   'Kadmium Terlarut (Cd)', 'Dissolved Cadmium', 'mg/L',  'SNI 6989.84:2019',    'numeric', 1, 22),
(82, 4, 'kimia',  'BA-TIMBAL',    'Timbal Terlarut (Pb)', 'Dissolved Lead','mg/L',       'SNI 6989.84:2019',    'numeric', 1, 23),
(83, 4, 'kimia',  'BA-NITRIT',    'Nitrit (sebagai N)', 'Nitrite as N',    'mg/L',       'SNI 06-6989.9-2004',  'numeric', 1, 24),
(84, 4, 'kimia',  'BA-MINYAK',    'Minyak dan Lemak', 'Oil and Grease',    'mg/L',       'SNI 6989.10:2011',    'numeric', 1, 25),
(85, 4, 'kimia',  'BA-DETERJEN',  'Deterjen Total (MBAS)', 'Surfactants (MBAS)', 'mg/L', 'SNI 06-6989.51-2005', 'numeric', 1, 26),
(86, 4, 'mikrobiologi', 'BA-FCOLI','Fecal Coliform', 'Fecal Coliform',     'MPN/100 ml', 'APHA 9221-E',         'numeric', 1, 40),
(87, 4, 'mikrobiologi', 'BA-TCOLI','Total Coliform', 'Total Coliform',     'MPN/100 ml', 'APHA 9221-B',         'numeric', 1, 41),

-- [TIPE 5: UDARA]
(88, 5, 'fisika', 'UD-SUHU',      'Suhu Udara',  'Air Temperature',        'oC',         'SNI 16-7061-2004',    'numeric', 1, 1),
(89, 5, 'fisika', 'UD-RH',        'Kelembaban Relatif (RH)', 'Relative Humidity', '%RH', 'SNI 16-7061-2004',    'numeric', 1, 2),
(90, 5, 'fisika', 'UD-BISING',    'Kebisingan',  'Noise Level',            'dBA',        'SNI 8427:2017',       'numeric', 1, 3),
(91, 5, 'fisika', 'UD-CAHAYA',    'Intensitas Pencahayaan', 'Illumination','Lux',        'SNI 7062:2019',       'numeric', 1, 4),
(92, 5, 'fisika', 'UD-TSP',       'Debu Total (TSP) 24 Jam', 'Total Suspended Particulate', 'µg/m3', 'SNI 7119-3:2017', 'numeric', 1, 5),
(93, 5, 'fisika', 'UD-PM10',      'Partikulat PM10', 'Particulate Matter <10 µm', 'µg/m3', 'SNI 7119-15:2016', 'numeric', 1, 6),
(94, 5, 'fisika', 'UD-PM25',      'Partikulat PM2.5', 'Particulate Matter <2.5 µm', 'µg/m3', 'SNI 7119-14:2016', 'numeric', 1, 7),
(95, 5, 'kimia',  'UD-SO2',       'Sulfur Dioksida (SO2)', 'Sulfur Dioxide', 'µg/m3',    'SNI 7119-7:2017',     'numeric', 1, 10),
(96, 5, 'kimia',  'UD-CO',        'Karbon Monoksida (CO)', 'Carbon Monoxide', 'µg/m3',   'SNI 7119.10:2011',    'numeric', 1, 11),
(97, 5, 'kimia',  'UD-NO2',       'Nitrogen Dioksida (NO2)', 'Nitrogen Dioxide', 'µg/m3','SNI 7119-2:2017',     'numeric', 1, 12),
(98, 5, 'kimia',  'UD-O3',        'Oksidan Fotokimia (O3)', 'Photochemical Oxidant', 'µg/m3', 'SNI 7119-8:2017','numeric', 1, 13),
(99, 5, 'kimia',  'UD-PB',        'Timbal (Pb)', 'Lead Particulate',       'µg/m3',      'SNI 7119-4:2017',     'numeric', 0, 14),
(100,5, 'kimia',  'UD-CO2',       'Karbon Dioksida (CO2)', 'Carbon Dioxide', 'ppm',      'Direct Reading NDIR', 'numeric', 0, 15),
(101,5, 'mikrobiologi', 'UD-KUMAN','Angka Kuman Udara', 'Airborne Total Plate Count', 'CFU/m3', 'Impaksi / Agar Plate', 'numeric', 0, 40)
ON DUPLICATE KEY UPDATE `param_name`=VALUES(`param_name`), `param_name_en`=VALUES(`param_name_en`), `unit`=VALUES(`unit`), `method_default`=VALUES(`method_default`), `sub_category`=VALUES(`sub_category`), `is_accredited`=VALUES(`is_accredited`), `sort_order`=VALUES(`sort_order`);

-- ============================================================
-- 5. SEED — BAKU MUTU (threshold) per standar
-- ============================================================
-- Standar 1: Permenkes 2/2023 (Air Minum)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(1, 1,  'Suhu Udara ± 3', 'none',  NULL,  NULL, NULL, NULL, NULL, 1),
(1, 2,  '< 300',   'max',  NULL, 300,   NULL, NULL, NULL, 2),
(1, 3,  '< 3',     'max',  NULL, 3,     NULL, NULL, NULL, 3),
(1, 4,  '10',      'max',  NULL, 10,    NULL, NULL, NULL, 4),
(1, 5,  'Tidak Berbau', 'text', NULL, NULL, 'Tidak Berbau', NULL, NULL, 5),
(1, 7,  '6,5 - 8,5', 'range', 6.5, 8.5, NULL, NULL, NULL, 10),
(1, 8,  '20',      'max',  NULL, 20,    NULL, NULL, NULL, 11),
(1, 9,  '3',       'max',  NULL, 3,     NULL, NULL, NULL, 12),
(1, 10, '0,01',    'max',  NULL, 0.01,  NULL, NULL, NULL, 13),
(1, 12, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 15),
(1, 13, '0,1',     'max',  NULL, 0.1,   NULL, NULL, NULL, 16),
(1, 14, '0,2 - 0,5', 'range', 0.2, 0.5, NULL, NULL, NULL, 17),
(1, 15, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 18),
(1, 16, '0,003',   'max',  NULL, 0.003, NULL, NULL, NULL, 19),
(1, 17, '0,01',    'max',  NULL, 0.01,  NULL, NULL, NULL, 20),
(1, 18, '1,5',     'max',  NULL, 1.5,   NULL, NULL, NULL, 21),
(1, 19, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 22),
(1, 27, '0',       'max',  NULL, 0,     NULL, 'CFU/100 ml', NULL, 40),
(1, 28, '0',       'max',  NULL, 0,     NULL, 'CFU/100 ml', NULL, 41)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 2: Permenkes 492/2010 (Air Minum)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(2, 5,  'Tidak Berbau', 'text', NULL, NULL, 'Tidak Berbau', NULL, NULL, 1),
(2, 3,  '5',       'max',  NULL, 5,     NULL, NULL, NULL, 2),
(2, 6,  'Tidak Berasa', 'text', NULL, NULL, 'Tidak Berasa', NULL, NULL, 3),
(2, 1,  'Suhu Air 27 ± 3', 'none', NULL, NULL, NULL, NULL, NULL, 4),
(2, 4,  '15',      'max',  NULL, 15,    NULL, 'Unit PtCo', NULL, 5),
(2, 2,  '500',     'max',  NULL, 500,   NULL, NULL, NULL, 6),
(2, 7,  '6,5 - 8,5', 'range', 6.5, 8.5, NULL, NULL, NULL, 10),
(2, 19, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 11),
(2, 12, '0,3',     'max',  NULL, 0.3,   NULL, NULL, NULL, 12),
(2, 18, '1,5',     'max',  NULL, 1.5,   NULL, NULL, NULL, 13),
(2, 16, '0,003',   'max',  NULL, 0.003, NULL, NULL, NULL, 14),
(2, 20, '500',     'max',  NULL, 500,   NULL, NULL, NULL, 15),
(2, 21, '250',     'max',  NULL, 250,   NULL, NULL, NULL, 16),
(2, 11, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 17),
(2, 13, '0,4',     'max',  NULL, 0.4,   NULL, NULL, NULL, 18),
(2, 8,  '50',      'max',  NULL, 50,    NULL, NULL, NULL, 19),
(2, 9,  '3',       'max',  NULL, 3,     NULL, NULL, NULL, 20),
(2, 22, '1,5',     'max',  NULL, 1.5,   NULL, NULL, NULL, 21),
(2, 23, '3',       'max',  NULL, 3,     NULL, NULL, NULL, 22),
(2, 24, '0,07',    'max',  NULL, 0.07,  NULL, NULL, NULL, 23),
(2, 25, '250',     'max',  NULL, 250,   NULL, NULL, NULL, 24),
(2, 26, '2',       'max',  NULL, 2,     NULL, NULL, NULL, 25),
(2, 29, '0',       'max',  NULL, 0,     NULL, 'MPN/100 ml', 'Biakan Tabung Ganda', 40),
(2, 28, '0',       'max',  NULL, 0,     NULL, 'MPN/100 ml', 'Biakan Tabung Ganda', 41)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 3: Permenkes RI No. 2 Tahun 2023 (Air Higiene Sanitasi)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(3, 30, '< 3',     'max',  NULL, 3,     NULL, NULL, NULL, 1),
(3, 31, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 2),
(3, 32, '< 300',   'max',  NULL, 300,   NULL, NULL, NULL, 3),
(3, 33, 'Suhu Udara ± 3', 'none', NULL, NULL, NULL, NULL, NULL, 4),
(3, 34, 'Tidak Berbau', 'text', NULL, NULL, 'Tidak Berbau', NULL, NULL, 5),
(3, 36, '6,5 - 8,5', 'range', 6.5, 8.5, NULL, NULL, NULL, 10),
(3, 37, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 11),
(3, 38, '1,5',     'max',  NULL, 1.5,   NULL, NULL, NULL, 12),
(3, 39, '500',     'max',  NULL, 500,   NULL, NULL, NULL, 13),
(3, 40, '0,1',     'max',  NULL, 0.1,   NULL, NULL, NULL, 14),
(3, 41, '20',      'max',  NULL, 20,    NULL, NULL, NULL, 15),
(3, 42, '3',       'max',  NULL, 3,     NULL, NULL, NULL, 16),
(3, 44, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 18),
(3, 46, '0,01',    'max',  NULL, 0.01,  NULL, NULL, NULL, 20),
(3, 50, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 24),
(3, 51, '0',       'max',  NULL, 0,     NULL, NULL, NULL, 40),
(3, 52, '0',       'max',  NULL, 0,     NULL, NULL, NULL, 41)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 4: Permenkes RI No. 32 Tahun 2017 (Air Higiene Sanitasi)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(4, 30, '25',      'max',  NULL, 25,    NULL, NULL, NULL, 1),
(4, 31, '50',      'max',  NULL, 50,    NULL, NULL, NULL, 2),
(4, 32, '1000',    'max',  NULL, 1000,  NULL, NULL, NULL, 3),
(4, 33, 'Suhu Udara ± 3', 'none', NULL, NULL, NULL, NULL, NULL, 4),
(4, 34, 'Tidak Berbau', 'text', NULL, NULL, 'Tidak Berbau', NULL, NULL, 5),
(4, 35, 'Tidak Berasa', 'text', NULL, NULL, 'Tidak Berasa', NULL, NULL, 6),
(4, 36, '6,5 - 8,5', 'range', 6.5, 8.5, NULL, NULL, NULL, 10),
(4, 37, '1',       'max',  NULL, 1,     NULL, NULL, NULL, 11),
(4, 38, '1,5',     'max',  NULL, 1.5,   NULL, NULL, NULL, 12),
(4, 39, '500',     'max',  NULL, 500,   NULL, NULL, NULL, 13),
(4, 40, '0,5',     'max',  NULL, 0.5,   NULL, NULL, NULL, 14),
(4, 41, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 15),
(4, 42, '1',       'max',  NULL, 1,     NULL, NULL, NULL, 16),
(4, 43, '0,1',     'max',  NULL, 0.1,   NULL, NULL, NULL, 17),
(4, 44, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 18),
(4, 45, '0,005',   'max',  NULL, 0.005, NULL, NULL, NULL, 19),
(4, 46, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 20),
(4, 47, '15',      'max',  NULL, 15,    NULL, NULL, NULL, 21),
(4, 48, '400',     'max',  NULL, 400,   NULL, NULL, NULL, 22),
(4, 49, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 23),
(4, 50, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 24),
(4, 51, '0',       'max',  NULL, 0,     NULL, NULL, NULL, 40),
(4, 52, '50',      'max',  NULL, 50,    NULL, NULL, NULL, 41)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 5: Permen LHK No. P.68/2016 (Air Limbah Domestik)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(5, 54, '30',      'max',  NULL, 30,    NULL, NULL, NULL, 1),
(5, 56, '6,0 - 9,0', 'range', 6.0, 9.0, NULL, NULL, NULL, 10),
(5, 57, '30',      'max',  NULL, 30,    NULL, NULL, NULL, 11),
(5, 58, '100',     'max',  NULL, 100,   NULL, NULL, NULL, 12),
(5, 59, '5',       'max',  NULL, 5,     NULL, NULL, NULL, 13),
(5, 60, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 14),
(5, 64, '3000',    'max',  NULL, 3000,  NULL, NULL, NULL, 40)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 6: Permen LH No. 5 Tahun 2014 Lampiran XLIV (Air Limbah Fasyankes)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(6, 53, '30',      'max',  NULL, 30,    NULL, NULL, NULL, 1),
(6, 54, '30',      'max',  NULL, 30,    NULL, NULL, NULL, 2),
(6, 55, '2000',    'max',  NULL, 2000,  NULL, NULL, NULL, 3),
(6, 56, '6,0 - 9,0', 'range', 6.0, 9.0, NULL, NULL, NULL, 10),
(6, 57, '30',      'max',  NULL, 30,    NULL, NULL, NULL, 11),
(6, 58, '80',      'max',  NULL, 80,    NULL, NULL, NULL, 12),
(6, 59, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 13),
(6, 60, '0,1',     'max',  NULL, 0.1,   NULL, NULL, NULL, 14),
(6, 61, '0,5',     'max',  NULL, 0.5,   NULL, NULL, NULL, 15),
(6, 62, '0,01',    'max',  NULL, 0.01,  NULL, NULL, NULL, 16),
(6, 63, '0,5',     'max',  NULL, 0.5,   NULL, NULL, NULL, 17),
(6, 64, '5000',    'max',  NULL, 5000,  NULL, NULL, NULL, 40)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 7, 8, 9, 10: PP RI No. 22 Tahun 2021 Lampiran VI (Air Sungai Kelas I, II, III, IV)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
-- Kelas I
(7, 65, 'Deviasi 3', 'none', NULL, NULL, NULL, NULL, NULL, 1),
(7, 66, '1000',    'max',  NULL, 1000,  NULL, NULL, NULL, 2),
(7, 67, '40',      'max',  NULL, 40,    NULL, NULL, NULL, 3),
(7, 68, '15',      'max',  NULL, 15,    NULL, NULL, NULL, 4),
(7, 69, '6 - 9',   'range', 6.0, 9.0,   NULL, NULL, NULL, 10),
(7, 70, '2',       'max',  NULL, 2,     NULL, NULL, NULL, 11),
(7, 71, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 12),
(7, 72, '≥ 6',     'min',  6.0,  NULL,  NULL, NULL, NULL, 13),
(7, 73, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 14),
(7, 74, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 15),
(7, 75, '0,1',     'max',  NULL, 0.1,   NULL, NULL, NULL, 16),
(7, 76, '0,3',     'max',  NULL, 0.3,   NULL, NULL, NULL, 17),
(7, 77, '0,1',     'max',  NULL, 0.1,   NULL, NULL, NULL, 18),
(7, 78, '0,02',    'max',  NULL, 0.02,  NULL, NULL, NULL, 19),
(7, 79, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 20),
(7, 80, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 21),
(7, 81, '0,01',    'max',  NULL, 0.01,  NULL, NULL, NULL, 22),
(7, 82, '0,03',    'max',  NULL, 0.03,  NULL, NULL, NULL, 23),
(7, 83, '0,06',    'max',  NULL, 0.06,  NULL, NULL, NULL, 24),
(7, 84, '1',       'max',  NULL, 1,     NULL, NULL, NULL, 25),
(7, 85, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 26),
(7, 86, '100',     'max',  NULL, 100,   NULL, NULL, NULL, 40),
(7, 87, '1000',    'max',  NULL, 1000,  NULL, NULL, NULL, 41),
-- Kelas II
(8, 65, 'Deviasi 3', 'none', NULL, NULL, NULL, NULL, NULL, 1),
(8, 66, '1000',    'max',  NULL, 1000,  NULL, NULL, NULL, 2),
(8, 67, '50',      'max',  NULL, 50,    NULL, NULL, NULL, 3),
(8, 68, '50',      'max',  NULL, 50,    NULL, NULL, NULL, 4),
(8, 69, '6 - 9',   'range', 6.0, 9.0,   NULL, NULL, NULL, 10),
(8, 70, '3',       'max',  NULL, 3,     NULL, NULL, NULL, 11),
(8, 71, '25',      'max',  NULL, 25,    NULL, NULL, NULL, 12),
(8, 72, '≥ 4',     'min',  4.0,  NULL,  NULL, NULL, NULL, 13),
(8, 73, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 14),
(8, 74, '10',      'max',  NULL, 10,    NULL, NULL, NULL, 15),
(8, 75, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 16),
(8, 78, '0,02',    'max',  NULL, 0.02,  NULL, NULL, NULL, 19),
(8, 79, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 20),
(8, 80, '0,05',    'max',  NULL, 0.05,  NULL, NULL, NULL, 21),
(8, 81, '0,01',    'max',  NULL, 0.01,  NULL, NULL, NULL, 22),
(8, 82, '0,03',    'max',  NULL, 0.03,  NULL, NULL, NULL, 23),
(8, 83, '0,06',    'max',  NULL, 0.06,  NULL, NULL, NULL, 24),
(8, 84, '1',       'max',  NULL, 1,     NULL, NULL, NULL, 25),
(8, 85, '0,2',     'max',  NULL, 0.2,   NULL, NULL, NULL, 26),
(8, 86, '1000',    'max',  NULL, 1000,  NULL, NULL, NULL, 40),
(8, 87, '5000',    'max',  NULL, 5000,  NULL, NULL, NULL, 41),
-- Kelas III
(9, 65, 'Deviasi 3', 'none', NULL, NULL, NULL, NULL, NULL, 1),
(9, 66, '1000',    'max',  NULL, 1000,  NULL, NULL, NULL, 2),
(9, 67, '100',     'max',  NULL, 100,   NULL, NULL, NULL, 3),
(9, 68, '100',     'max',  NULL, 100,   NULL, NULL, NULL, 4),
(9, 69, '6 - 9',   'range', 6.0, 9.0,   NULL, NULL, NULL, 10),
(9, 70, '6',       'max',  NULL, 6,     NULL, NULL, NULL, 11),
(9, 71, '40',      'max',  NULL, 40,    NULL, NULL, NULL, 12),
(9, 72, '≥ 3',     'min',  3.0,  NULL,  NULL, NULL, NULL, 13),
(9, 73, '1,0',     'max',  NULL, 1.0,   NULL, NULL, NULL, 14),
(9, 74, '20',      'max',  NULL, 20,    NULL, NULL, NULL, 15),
(9, 75, '0,5',     'max',  NULL, 0.5,   NULL, NULL, NULL, 16),
(9, 86, '2000',    'max',  NULL, 2000,  NULL, NULL, NULL, 40),
(9, 87, '10000',   'max',  NULL, 10000, NULL, NULL, NULL, 41),
-- Kelas IV
(10, 65, 'Deviasi 3', 'none', NULL, NULL, NULL, NULL, NULL, 1),
(10, 66, '2000',   'max',  NULL, 2000,  NULL, NULL, NULL, 2),
(10, 67, '400',    'max',  NULL, 400,   NULL, NULL, NULL, 3),
(10, 69, '6 - 9',  'range', 6.0, 9.0,   NULL, NULL, NULL, 10),
(10, 70, '12',     'max',  NULL, 12,    NULL, NULL, NULL, 11),
(10, 71, '80',     'max',  NULL, 80,    NULL, NULL, NULL, 12),
(10, 72, '≥ 1',    'min',  1.0,  NULL,  NULL, NULL, NULL, 13),
(10, 74, '20',     'max',  NULL, 20,    NULL, NULL, NULL, 15),
(10, 86, '2000',   'max',  NULL, 2000,  NULL, NULL, NULL, 40),
(10, 87, '10000',  'max',  NULL, 10000, NULL, NULL, NULL, 41)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 11: PP RI No. 22 Tahun 2021 Lampiran VII (Udara Ambien)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(11, 90, '85',     'max',  NULL, 85,    NULL, NULL, NULL, 3),
(11, 92, '230',    'max',  NULL, 230,   NULL, NULL, NULL, 5),
(11, 93, '75',     'max',  NULL, 75,    NULL, NULL, NULL, 6),
(11, 94, '55',     'max',  NULL, 55,    NULL, NULL, NULL, 7),
(11, 95, '150',    'max',  NULL, 150,   NULL, NULL, NULL, 10),
(11, 96, '10000',  'max',  NULL, 10000, NULL, NULL, NULL, 11),
(11, 97, '200',    'max',  NULL, 200,   NULL, NULL, NULL, 12),
(11, 98, '150',    'max',  NULL, 150,   NULL, NULL, NULL, 13),
(11, 99, '2',      'max',  NULL, 2,     NULL, NULL, NULL, 14)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- Standar 12: Permenkes RI No. 2 Tahun 2023 (Udara Dalam Ruang)
INSERT INTO `env_thresholds` (`standard_id`, `parameter_id`, `threshold_display`, `operator`, `min_val`, `max_val`, `text_expected`, `unit_override`, `method_override`, `sort_order`) VALUES
(12, 88, '18 - 30', 'range', 18.0, 30.0, NULL, NULL, NULL, 1),
(12, 89, '40 - 60', 'range', 40.0, 60.0, NULL, NULL, NULL, 2),
(12, 90, '85',     'max',  NULL, 85,    NULL, NULL, NULL, 3),
(12, 91, '≥ 60',   'min',  60.0, NULL,  NULL, NULL, NULL, 4),
(12, 93, '70',     'max',  NULL, 70,    NULL, NULL, NULL, 6),
(12, 94, '25',     'max',  NULL, 25,    NULL, NULL, NULL, 7),
(12, 95, '125',    'max',  NULL, 125,   NULL, NULL, NULL, 10),
(12, 96, '10000',  'max',  NULL, 10000, NULL, NULL, NULL, 11),
(12, 97, '200',    'max',  NULL, 200,   NULL, NULL, NULL, 12),
(12, 100,'1000',   'max',  NULL, 1000,  NULL, NULL, NULL, 15),
(12, 101,'< 700',  'max',  NULL, 700,   NULL, NULL, NULL, 40)
ON DUPLICATE KEY UPDATE `threshold_display`=VALUES(`threshold_display`), `operator`=VALUES(`operator`), `min_val`=VALUES(`min_val`), `max_val`=VALUES(`max_val`), `text_expected`=VALUES(`text_expected`);

-- ============================================================
-- 6. MENU, RBAC, & PENGATURAN KESLING
-- ============================================================
INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
(48, NULL, 'Kesehatan Lingkungan', '#kesling', 'droplets', 6, 1),
(49, 48, 'Order Lingkungan', 'kesling', 'clipboard-list', 1, 1),
(50, 48, 'Baku Mutu & Standar', 'kesling/baku_mutu', 'ruler', 2, 1)
ON DUPLICATE KEY UPDATE `menu_name`=VALUES(`menu_name`), `menu_url`=VALUES(`menu_url`), `menu_icon`=VALUES(`menu_icon`), `parent_id`=VALUES(`parent_id`), `sort_order`=VALUES(`sort_order`);

-- Role permissions: 1=Admin (penuh), 2/3=view, 4=Petugas (create/edit)
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 48, 1, 1, 1, 1), (1, 49, 1, 1, 1, 1), (1, 50, 1, 1, 1, 1),
(2, 48, 1, 0, 0, 0), (2, 49, 1, 0, 0, 0), (2, 50, 1, 0, 0, 0),
(3, 48, 1, 0, 0, 0), (3, 49, 1, 0, 0, 0), (3, 50, 1, 0, 0, 0),
(4, 48, 1, 0, 0, 0), (4, 49, 1, 1, 1, 0), (4, 50, 1, 0, 0, 0)
ON DUPLICATE KEY UPDATE `can_view`=VALUES(`can_view`), `can_create`=VALUES(`can_create`), `can_edit`=VALUES(`can_edit`), `can_delete`=VALUES(`can_delete`);

INSERT INTO `pengaturan` (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `label`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
('aplikasi', 'manajer_teknis', 'dr. H. Wan Muhamed Medy, Sp.PK', 'text', 'Nama Manajer Teknis Lab Lingkungan', 'Pejabat penandatangan kiri pada Laporan Hasil Uji (LHU) Lingkungan', 40, NOW(), NOW()),
('aplikasi', 'nip_manajer_teknis', '19760412 200501 1 008', 'text', 'NIP Manajer Teknis Lab Lingkungan', 'NIP Manajer Teknis pada LHU Lingkungan', 41, NOW(), NOW()),
('aplikasi', 'pj_pengujian_lingkungan', 'Hj. Siti Rahmawati, S.Si., M.Kes', 'text', 'Penanggung Jawab Pengujian Lingkungan', 'Pejabat penandatangan kanan pada Laporan Hasil Uji (LHU) Lingkungan', 42, NOW(), NOW()),
('aplikasi', 'nip_pj_pengujian_lingkungan', '19800819 200604 2 011', 'text', 'NIP PJ Pengujian Lingkungan', 'NIP Penanggung Jawab Pengujian pada LHU Lingkungan', 43, NOW(), NOW())
ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `updated_at`=NOW();
