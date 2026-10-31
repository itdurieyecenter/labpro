-- ============================================================
-- MIGRASI PERFORMA: indeks pendukung query ringkasan/laporan pada data besar
-- Idempotent (cek INFORMATION_SCHEMA).
-- ============================================================
DROP PROCEDURE IF EXISTS _add_idx;
DELIMITER $$
CREATE PROCEDURE _add_idx(IN t VARCHAR(64), IN i VARCHAR(64), IN cols VARCHAR(255))
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = t AND INDEX_NAME = i) THEN
    SET @s = CONCAT('ALTER TABLE `', t, '` ADD KEY `', i, '` (', cols, ')');
    PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
  END IF;
END$$
DELIMITER ;

CALL _add_idx('pendaftaran', 'idx_kategori_status', '`kategori_pemeriksaan`,`status_order`');
CALL _add_idx('pendaftaran', 'idx_bayar_waktu', '`status_pembayaran`,`waktu_bayar`');
CALL _add_idx('pendaftaran', 'idx_nomor_kuitansi', '`nomor_kuitansi`');
CALL _add_idx('pendaftaran', 'idx_tgl_tipe', '`tanggal_pendaftaran`,`tipe_pasien`');
CALL _add_idx('pendaftaran_detail', 'idx_pd_laporan', '`pendaftaran_id`,`tipe_item`,`nama_item`,`kategori_lab`');
CALL _add_idx('pendaftaran_approval', 'idx_pa_status', '`pendaftaran_id`,`status`');
CALL _add_idx('env_orders', 'idx_env_status_id', '`status`,`id`');
CALL _add_idx('env_orders', 'idx_env_pendaftaran', '`pendaftaran_id`');
CALL _add_idx('env_order_approvals', 'idx_eoa_order_status', '`order_id`,`status`');
CALL _add_idx('env_order_results', 'idx_eor_order', '`order_id`,`parameter_id`');

DROP PROCEDURE IF EXISTS _add_idx;
