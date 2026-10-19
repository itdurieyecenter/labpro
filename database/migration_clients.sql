USE `lispro`;

CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_type` ENUM('perorangan', 'perusahaan') NOT NULL DEFAULT 'perorangan',
  `client_code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `identity_number` VARCHAR(50) DEFAULT NULL,
  `gender` ENUM('L', 'P') DEFAULT NULL,
  `birth_date` DATE DEFAULT NULL,
  `blood_type` VARCHAR(10) DEFAULT NULL,
  `pic_name` VARCHAR(100) DEFAULT NULL,
  `pic_phone` VARCHAR(30) DEFAULT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `contract_discount` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `payment_term` VARCHAR(50) DEFAULT 'Tunai',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_type` (`client_type`),
  KEY `idx_client_code` (`client_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) 
VALUES (15, 7, 'Master Klien (Pasien & PT)', 'client', 'contact-2', 0, 1)
ON DUPLICATE KEY UPDATE `menu_name` = VALUES(`menu_name`), `menu_url` = VALUES(`menu_url`);

INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 15, 1, 1, 1, 1),
(2, 15, 1, 0, 0, 0),
(3, 15, 1, 0, 0, 0),
(4, 15, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE `can_view`=1, `can_create`=VALUES(`can_create`), `can_edit`=VALUES(`can_edit`), `can_delete`=VALUES(`can_delete`);

INSERT INTO `clients` (`client_type`, `client_code`, `name`, `identity_number`, `gender`, `birth_date`, `blood_type`, `pic_name`, `pic_phone`, `phone`, `email`, `address`, `contract_discount`, `payment_term`, `status`, `notes`) VALUES
('perorangan', 'PSN-2026-0001', 'Ahmad Fadillah', '3201011504920002', 'L', '1992-04-15', 'O', NULL, NULL, '081298765432', 'ahmad.fadillah@example.com', 'Jl. Sudirman No. 45, Jakarta Selatan', 0.00, 'Tunai', 'active', 'Pasien rujukan dr. Anita Sp.PD, riwayat alergi penisilin'),
('perorangan', 'PSN-2026-0002', 'Nurul Hidayati', '3201015608950001', 'P', '1995-08-16', 'B', NULL, NULL, '081387654321', 'nurul.h@example.com', 'Jl. Merak No. 12, Kebayoran Baru', 0.00, 'Tunai', 'active', 'Pasien tes gula darah rutin'),
('perusahaan', 'CORP-2026-0001', 'PT. Petro Graha Perkasa', '01.234.567.8-012.000', NULL, NULL, NULL, 'Bambang Kusuma, SE', '08119871234', '021-5558900', 'hrd@petrograha.co.id', 'Gedung Wisma 46 Lt. 18, Jakarta Pusat', 15.00, 'Tempo 30 Hari', 'active', 'Kontrak MCU tahunan 500 karyawan per tahun, diskon 15%'),
('perusahaan', 'CORP-2026-0002', 'PT. Sentosa Logistik Indonesia', '02.987.654.3-015.000', NULL, NULL, NULL, 'Ratna Sari', '08123456780', '021-7788990', 'medical@sentosalogistik.com', 'Kawasan Industri Pulogadung Blok B-3', 10.00, 'Tempo 14 Hari', 'active', 'MCU berkala supir & staf operasional gudang')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
