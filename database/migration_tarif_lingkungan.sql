-- ============================================================
-- MIGRASI: Tarif Parameter Uji Lingkungan (Master Tarif -> Pendaftaran)
-- Parameter di env_parameters diberi tarif lewat lab_tariffs (item_type 'env_parameter')
-- sehingga mengikuti kelompok tarif yang sama (umum/perusahaan/bpjs/asuransi).
-- Item pendaftaran bertipe 'env_parameter' menyimpan rincian uji lingkungan yang dipesan.
-- ============================================================
ALTER TABLE `lab_tariffs`
  MODIFY COLUMN `item_type` ENUM('parameter','template','env_parameter') NOT NULL DEFAULT 'parameter';

ALTER TABLE `pendaftaran_detail`
  MODIFY COLUMN `tipe_item` ENUM('parameter','template','env_parameter') NOT NULL DEFAULT 'parameter';

INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
(54, 25, 'Tarif Uji Lingkungan', 'tarif/lingkungan', 'droplets', 4, 1)
ON DUPLICATE KEY UPDATE `menu_name`=VALUES(`menu_name`), `menu_url`=VALUES(`menu_url`), `menu_icon`=VALUES(`menu_icon`), `parent_id`=VALUES(`parent_id`), `sort_order`=VALUES(`sort_order`);

INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 54, 1, 1, 1, 1),
(4, 54, 1, 0, 0, 0)
ON DUPLICATE KEY UPDATE `can_view`=VALUES(`can_view`), `can_create`=VALUES(`can_create`), `can_edit`=VALUES(`can_edit`), `can_delete`=VALUES(`can_delete`);
