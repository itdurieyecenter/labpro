-- ============================================================
-- MIGRASI: Tagihan Order Kesehatan Lingkungan -> Antrian Kasir
-- Order lingkungan menyimpan biaya pengujian dan tertaut ke satu baris
-- pendaftaran (kategori 'lingkungan') agar muncul di Kasir & Closing Harian.
-- ============================================================
SET @c1 := (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'env_orders' AND COLUMN_NAME = 'biaya_pengujian');
SET @s1 := IF(@c1 = 0,
    'ALTER TABLE `env_orders` ADD COLUMN `biaya_pengujian` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `notes`',
    'SELECT 1');
PREPARE st FROM @s1; EXECUTE st; DEALLOCATE PREPARE st;

SET @c2 := (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'env_orders' AND COLUMN_NAME = 'pendaftaran_id');
SET @s2 := IF(@c2 = 0,
    'ALTER TABLE `env_orders` ADD COLUMN `pendaftaran_id` INT(11) DEFAULT NULL COMMENT ''Tagihan di tabel pendaftaran'' AFTER `biaya_pengujian`, ADD KEY `idx_env_pendaftaran` (`pendaftaran_id`)',
    'SELECT 1');
PREPARE st FROM @s2; EXECUTE st; DEALLOCATE PREPARE st;
