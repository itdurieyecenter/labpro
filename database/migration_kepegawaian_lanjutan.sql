-- =========================================================================
-- MIGRATION: KEPEGAWAIAN LANJUTAN
-- UPT Laboratorium Kesehatan Provinsi Riau
--
-- Melengkapi modul Kepegawaian sesuai KAK:
--   1. Pemantauan masa berlaku kredensial (STR / SIP)
--   2. Monitoring beban kerja pegawai
--   3. Log riwayat aktivitas pengguna (audit trail)
-- =========================================================================

USE `lispro`;

-- 1. LOG AKTIVITAS PENGGUNA (AUDIT TRAIL GLOBAL)
--    Diisi otomatis oleh hook untuk setiap aksi tulis, dan dapat diisi
--    eksplisit lewat Audit_lib untuk keterangan yang lebih rinci.
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `user_nama` VARCHAR(100) DEFAULT NULL,
  `user_role` VARCHAR(50) DEFAULT NULL,
  `modul` VARCHAR(100) NOT NULL COMMENT 'Controller yang diakses',
  `aksi` VARCHAR(100) NOT NULL COMMENT 'Method yang dijalankan',
  `metode` VARCHAR(10) NOT NULL DEFAULT 'POST',
  `url` VARCHAR(255) DEFAULT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `ref_tabel` VARCHAR(64) DEFAULT NULL,
  `ref_id` INT(11) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_modul` (`modul`, `aksi`),
  KEY `idx_audit_waktu` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MENU BARU DI BAWAH KEPEGAWAIAN (parent_id = 19)
INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
  (32, 19, 'Kredensial STR & SIP', 'kepegawaian/kredensial', 'badge-check', 6, 1),
  (33, 19, 'Beban Kerja Pegawai', 'kepegawaian/beban_kerja', 'activity', 7, 1),
  (34, 19, 'Audit Trail Aktivitas', 'kepegawaian/audit_trail', 'history', 8, 1)
ON DUPLICATE KEY UPDATE
  `menu_name` = VALUES(`menu_name`),
  `menu_url` = VALUES(`menu_url`),
  `menu_icon` = VALUES(`menu_icon`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

-- 3. HAK AKSES ADMIN UNTUK MENU BARU
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
  (1, 32, 1, 1, 1, 1),
  (1, 33, 1, 1, 1, 1),
  (1, 34, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE
  `can_view` = VALUES(`can_view`),
  `can_create` = VALUES(`can_create`),
  `can_edit` = VALUES(`can_edit`),
  `can_delete` = VALUES(`can_delete`);

-- 4. PARAMETER PEMANTAUAN KREDENSIAL & BEBAN KERJA
INSERT INTO `pengaturan`
  (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `label`, `description`, `sort_order`, `created_at`, `updated_at`)
VALUES
  ('kepegawaian', 'kredensial_peringatan_hari', '90', 'number', 'Ambang Peringatan Kredensial (hari)', 'Jumlah hari sebelum masa berlaku SIP/STR habis untuk mulai diperingatkan', 60, NOW(), NOW()),
  ('kepegawaian', 'kredensial_kritis_hari', '30', 'number', 'Ambang Kritis Kredensial (hari)', 'Di bawah jumlah hari ini, kredensial ditandai kritis dan wajib segera diperpanjang', 61, NOW(), NOW()),
  ('kepegawaian', 'beban_target_harian', '20', 'number', 'Target Pemeriksaan per Analis per Hari', 'Dipakai sebagai pembanding pada monitoring beban kerja', 62, NOW(), NOW()),
  ('kepegawaian', 'audit_retensi_hari', '365', 'number', 'Retensi Log Audit (hari)', 'Umur maksimal catatan audit trail yang dipertahankan sistem', 63, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();
