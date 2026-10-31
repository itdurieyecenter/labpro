-- ============================================================
-- MIGRASI: Menu "Pendaftaran Uji Lingkungan" (hak akses terpisah)
-- Pendaftaran perusahaan / instansi untuk uji limbah, air, udara, dll. punya menu sendiri
-- di grup Kesehatan Lingkungan agar bisa diberikan ke unit terkait lewat Hak Akses (RBAC).
-- Jalankan setelah migration_kesling.sql (butuh menu grup id 48).
-- ============================================================
INSERT INTO `menus` (`id`, `parent_id`, `menu_name`, `menu_url`, `menu_icon`, `sort_order`, `is_active`) VALUES
(55, 48, 'Pendaftaran Uji Lingkungan', 'pendaftaran/lingkungan', 'building-2', 0, 1)
ON DUPLICATE KEY UPDATE `menu_name`=VALUES(`menu_name`), `menu_url`=VALUES(`menu_url`), `menu_icon`=VALUES(`menu_icon`), `parent_id`=VALUES(`parent_id`), `sort_order`=VALUES(`sort_order`);

-- Default: hanya Admin. Role unit lingkungan diberi akses lewat menu Hak Akses (RBAC).
INSERT INTO `role_permissions` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 55, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE `can_view`=VALUES(`can_view`), `can_create`=VALUES(`can_create`), `can_edit`=VALUES(`can_edit`), `can_delete`=VALUES(`can_delete`);
