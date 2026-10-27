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
(1, 'AIR_MINUM', 'Air Minum', 'Air minum / air siap konsumsi (DAMIU, AMDK, dll.)', 1, 1),
(2, 'AIR_BERSIH', 'Air Bersih', 'Air bersih untuk keperluan higiene sanitasi', 2, 0),
(3, 'AIR_LIMBAH', 'Air Limbah', 'Air limbah industri / fasilitas', 3, 0),
(4, 'BADAN_AIR',  'Badan Air', 'Air sungai / badan air permukaan', 4, 0),
(5, 'UDARA',      'Udara', 'Kualitas udara ambien / emisi', 5, 0)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `sort_order`=VALUES(`sort_order`);

-- ============================================================
-- 3. SEED — STANDAR BAKU MUTU (Air Minum)
-- ============================================================
INSERT INTO `env_standards` (`id`, `sample_type_id`, `code`, `name`, `regulation_ref`, `description`, `sort_order`, `is_active`) VALUES
(1, 1, 'PERMENKES_2_2023',  'Permenkes RI No. 2 Tahun 2023 (Air Minum)', 'Permen Kes RI No. 2 Tahun 2023', 'Standar baku mutu kesehatan lingkungan untuk media air minum', 1, 1),
(2, 1, 'PERMENKES_492_2010','Permenkes No. 492/MENKES/PER/IV/2010 (Air Minum)', 'Permen Kes No. 492/MENKES/PER/IV/2010', 'Persyaratan kualitas air minum', 2, 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `regulation_ref`=VALUES(`regulation_ref`);

-- ============================================================
-- 4. SEED — PARAMETER (Air Minum, gabungan kedua standar)
-- ============================================================
INSERT INTO `env_parameters` (`id`, `sample_type_id`, `sub_category`, `param_code`, `param_name`, `param_name_en`, `unit`, `method_default`, `data_type`, `sort_order`) VALUES
-- Fisika
(1,  1, 'fisika', 'AM-SUHU',      'Suhu',        'Temperature',       'oC',        'SNI 06-6989.23-2005', 'numeric', 1),
(2,  1, 'fisika', 'AM-TDS',       'TDS',         'Total Dissolved Solids', 'mg/L', 'SNI 6989.27:2019',   'numeric', 2),
(3,  1, 'fisika', 'AM-KEKERUHAN', 'Kekeruhan',   'Turbidity',         'Skala NTU', 'SNI 06-6989.25-2005', 'numeric', 3),
(4,  1, 'fisika', 'AM-WARNA',     'Warna',       'Colour',            'TCU',       'SNI 06-6989.24-2005', 'numeric', 4),
(5,  1, 'fisika', 'AM-BAU',       'Bau',         'Odour',             '-',         'SNI 06-2413-1991',    'text',    5),
(6,  1, 'fisika', 'AM-RASA',      'Rasa',        'Taste',             '-',         'SNI 06-2413-1991',    'text',    6),
-- Kimia
(7,  1, 'kimia',  'AM-PH',        'pH',          'pH',                '-',         'SNI 06-6989.11-2019', 'numeric', 10),
(8,  1, 'kimia',  'AM-NITRAT',    'Nitrat sebagai N', 'Nitrate as N', 'mg/L',      'APHA 2012',           'numeric', 11),
(9,  1, 'kimia',  'AM-NITRIT',    'Nitrit sebagai N', 'Nitrite as N', 'mg/L',      'SNI 06-6989-9-2004',  'numeric', 12),
(10, 1, 'kimia',  'AM-CR6',       'Kromium valensi 6', 'Hexavalent Chromium', 'mg/L', 'SNI 6989.84:2019', 'numeric', 13),
(11, 1, 'kimia',  'AM-CR-TOTAL',  'Kromium Total', 'Total Chromium',   'mg/L',      'SNI 06-6989.12-2004', 'numeric', 14),
(12, 1, 'kimia',  'AM-BESI',      'Besi (sebagai logam terlarut)', 'Iron (dissolved)', 'mg/L', 'SNI 6989.84:2019', 'numeric', 15),
(13, 1, 'kimia',  'AM-MANGAN',    'Mangan (sebagai logam terlarut)', 'Manganese (dissolved)', 'mg/L', 'SNI 6989.84:2019', 'numeric', 16),
(14, 1, 'kimia',  'AM-SISA-KHLOR','Sisa Khlor',  'Residual Chlorine', 'mg/L',      'USEPA',               'numeric', 17),
(15, 1, 'kimia',  'AM-ARSEN',     'Arsen',       'Arsenic',           'mg/L',      'SNI 6989.84:2019',    'numeric', 18),
(16, 1, 'kimia',  'AM-KADMIUM',   'Kadmium (sebagai logam terlarut)', 'Cadmium (dissolved)', 'mg/L', 'SNI 6989.84:2019', 'numeric', 19),
(17, 1, 'kimia',  'AM-TIMBAL',    'Timbal (sebagai logam terlarut)', 'Lead (dissolved)', 'mg/L', 'SNI 6989-84:2019', 'numeric', 20),
(18, 1, 'kimia',  'AM-FLUORIDA',  'Fluorida',    'Fluoride',          'mg/L',      'SNI 06.6989.29.2005', 'numeric', 21),
(19, 1, 'kimia',  'AM-ALUMINIUM', 'Aluminium',   'Aluminium',         'mg/L',      'SNI 06-6989.35-2005', 'numeric', 22),
(20, 1, 'kimia',  'AM-KESADAHAN', 'Kesadahan (CaCO3)', 'Hardness (CaCO3)', 'mg/L', 'SNI 06-6989.12-2004', 'numeric', 23),
(21, 1, 'kimia',  'AM-KHLORIDA',  'Khlorida',    'Chloride',          'mg/L',      'SNI 6989.19:2019',    'numeric', 24),
(22, 1, 'kimia',  'AM-AMONIAK',   'Amoniak',     'Ammonia',           'mg/L',      'USEPA',               'numeric', 25),
(23, 1, 'kimia',  'AM-SENG',      'Seng (sebagai logam terlarut)', 'Zinc (dissolved)', 'mg/L', 'SNI 6989-84:2019', 'numeric', 26),
(24, 1, 'kimia',  'AM-SIANIDA',   'Sianida',     'Cyanide',           'mg/L',      'USEPA',               'numeric', 27),
(25, 1, 'kimia',  'AM-SULFAT',    'Sulfat',      'Sulphate',          'mg/L',      'SNI 6989-20:2019',    'numeric', 28),
(26, 1, 'kimia',  'AM-TEMBAGA',   'Tembaga (sebagai logam terlarut)', 'Copper (dissolved)', 'mg/L', 'SNI 6989-84:2019', 'numeric', 29),
-- Mikrobiologi
(27, 1, 'mikrobiologi', 'AM-ECOLI',    'E.coli',         'E.coli',          'CFU/100 ml', 'Biakan', 'numeric', 40),
(28, 1, 'mikrobiologi', 'AM-TCOLI',    'Total Coliform', 'Total Coliform',  'CFU/100 ml', 'Biakan', 'numeric', 41),
(29, 1, 'mikrobiologi', 'AM-COLI-TINJA','Coliform Tinja', 'Faecal Coliform', 'MPN/100 ml', 'Biakan Tabung Ganda', 'numeric', 42)
ON DUPLICATE KEY UPDATE `param_name`=VALUES(`param_name`), `unit`=VALUES(`unit`), `method_default`=VALUES(`method_default`), `sub_category`=VALUES(`sub_category`), `sort_order`=VALUES(`sort_order`);

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

-- ============================================================
-- 6. MENU & RBAC
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
