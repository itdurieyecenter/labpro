-- ============================================================
-- MIGRASI VERIFIKASI BERJENJANG: hasil TIDAK bisa dicetak sebelum seluruh
-- tanda tangan lengkap (klinis & lingkungan), + sampel Makanan/Minuman Kesling
-- Prasyarat: migration_kesling.sql, migration_pendaftaran_lingkungan.sql
-- ============================================================

-- 1. Rantai approval per modul (semua | klinis | lingkungan)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'setting_approval_hasil' AND COLUMN_NAME = 'berlaku_untuk');
SET @sql := IF(@col = 0,
    'ALTER TABLE `setting_approval_hasil` ADD COLUMN `berlaku_untuk` ENUM(''semua'',''klinis'',''lingkungan'') NOT NULL DEFAULT ''semua'' AFTER `keterangan`',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Dokter Sp.PK hanya untuk hasil klinis; Penanggung Jawab Kesling hanya untuk lingkungan
UPDATE `setting_approval_hasil` SET `berlaku_untuk` = 'klinis'
 WHERE `jabatan_approval` LIKE '%Sp.PK%';
UPDATE `setting_approval_hasil` SET `berlaku_untuk` = 'lingkungan'
 WHERE `jabatan_approval` LIKE '%Lingkungan%';

-- 2. Tracking approval order lingkungan
CREATE TABLE IF NOT EXISTS `env_order_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `rule_id` int(11) DEFAULT NULL,
  `urutan` int(11) NOT NULL,
  `pegawai_id` int(11) NOT NULL,
  `pegawai_nama` varchar(150) NOT NULL,
  `pegawai_nip` varchar(50) DEFAULT NULL,
  `jabatan_approval` varchar(150) NOT NULL,
  `status` enum('waiting','pending','approved') NOT NULL DEFAULT 'waiting',
  `catatan` text DEFAULT NULL,
  `tanda_tangan` longtext DEFAULT NULL,
  `approved_by_user_id` int(11) DEFAULT NULL,
  `approved_by_user_name` varchar(100) DEFAULT NULL,
  `waktu_approval` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cetak hasil diblokir total selama belum sah
UPDATE `pengaturan` SET `setting_value` = 'block' WHERE `setting_key` = 'approval_mode_belum_lengkap';

-- 4. Menu Verifikasi Hasil Lingkungan (parent Kesehatan Lingkungan = 48)
INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
(56, 48, 'Verifikasi Hasil Lingkungan', 'kesling/verifikasi', 'shield-check', 3, 1)
ON DUPLICATE KEY UPDATE `menu_name`=VALUES(`menu_name`), `menu_url`=VALUES(`menu_url`), `menu_icon`=VALUES(`menu_icon`), `parent_id`=VALUES(`parent_id`), `sort_order`=VALUES(`sort_order`);
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 56, 1, 1, 1, 1), (2, 56, 1, 0, 1, 0), (3, 56, 1, 0, 0, 0)
ON DUPLICATE KEY UPDATE `can_view`=VALUES(`can_view`), `can_create`=VALUES(`can_create`), `can_edit`=VALUES(`can_edit`), `can_delete`=VALUES(`can_delete`);

-- 5. Tipe sampel Makanan / Minuman + parameter mikrobiologi + standar acuan
INSERT INTO `env_sample_types` (`code`, `name`, `description`, `sort_order`, `is_active`)
SELECT 'MAKANAN', 'Makanan / Minuman', 'Sampel makanan, minuman olahan, jajanan, dan bahan pangan (uji cemaran mikroba)', 6, 1
WHERE NOT EXISTS (SELECT 1 FROM `env_sample_types` WHERE `code` = 'MAKANAN');
SET @st := (SELECT id FROM `env_sample_types` WHERE `code` = 'MAKANAN');

INSERT INTO `env_parameters` (`sample_type_id`,`sub_category`,`param_code`,`param_name`,`param_name_en`,`unit`,`method_default`,`data_type`,`is_accredited`,`sort_order`)
SELECT @st, v.sc, v.pc, v.pn, v.pe, v.un, v.me, v.dt, 0, v.so FROM (
  SELECT 'mikrobiologi' sc,'MKN_ALT' pc,'Angka Lempeng Total (ALT)' pn,'Total Plate Count' pe,'koloni/g' un,'SNI ISO 4833-1:2015' me,'numeric' dt,1 so UNION ALL
  SELECT 'mikrobiologi','MKN_ECOLI','Escherichia coli','Escherichia coli','APM/g','SNI ISO 7251:2012','numeric',2 UNION ALL
  SELECT 'mikrobiologi','MKN_COLI','Koliform','Coliform','APM/g','SNI ISO 4831:2012','numeric',3 UNION ALL
  SELECT 'mikrobiologi','MKN_SALM','Salmonella sp.','Salmonella sp.','/25 g','SNI ISO 6579-1:2017','text',4 UNION ALL
  SELECT 'mikrobiologi','MKN_SAUR','Staphylococcus aureus','Staphylococcus aureus','koloni/g','SNI ISO 6888-1:2010','numeric',5 UNION ALL
  SELECT 'mikrobiologi','MKN_KK','Kapang dan Khamir','Yeast and Mold','koloni/g','SNI ISO 21527-1:2012','numeric',6
) v WHERE @st IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `env_parameters` p WHERE p.`param_code` = v.pc AND p.`sample_type_id` = @st);

INSERT INTO `env_standards` (`sample_type_id`,`code`,`name`,`regulation_ref`,`description`,`sort_order`)
SELECT @st, 'BPOM_CEMARAN_MIKROBA', 'Batas Maksimum Cemaran Mikroba Pangan Olahan (acuan umum)', 'Peraturan BPOM tentang Batas Maksimal Cemaran Mikroba dalam Pangan Olahan',
       'Nilai ambang bersifat acuan umum; sesuaikan per kategori pangan sebelum dipakai resmi.', 1
WHERE @st IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `env_standards` WHERE `code` = 'BPOM_CEMARAN_MIKROBA');
SET @sd := (SELECT id FROM `env_standards` WHERE `code` = 'BPOM_CEMARAN_MIKROBA');

INSERT INTO `env_thresholds` (`standard_id`,`parameter_id`,`threshold_display`,`operator`,`min_val`,`max_val`,`text_expected`,`sort_order`)
SELECT @sd, p.id, v.td, v.op, NULL, v.mx, v.tx, p.sort_order FROM (
  SELECT 'MKN_ALT' pc,'maks. 1x10^5' td,'max' op,100000 mx,NULL tx UNION ALL
  SELECT 'MKN_ECOLI','< 3','max',3,NULL UNION ALL
  SELECT 'MKN_COLI','maks. 1x10^2','max',100,NULL UNION ALL
  SELECT 'MKN_SALM','Negatif','text',NULL,'Negatif' UNION ALL
  SELECT 'MKN_SAUR','maks. 1x10^2','max',100,NULL UNION ALL
  SELECT 'MKN_KK','maks. 1x10^4','max',10000,NULL
) v JOIN `env_parameters` p ON p.`param_code` = v.pc AND p.`sample_type_id` = @st
WHERE @sd IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `env_thresholds` t WHERE t.`standard_id` = @sd AND t.`parameter_id` = p.id);
