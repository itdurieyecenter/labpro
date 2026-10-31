-- ============================================================
-- MIGRASI: Paket Uji Lingkungan (kumpulan parameter dengan satu harga)
-- Harga paket disimpan di lab_tariffs (item_type 'env_package') per kelompok tarif.
-- ============================================================
CREATE TABLE IF NOT EXISTS `env_packages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `package_code` VARCHAR(40) NOT NULL,
  `package_name` VARCHAR(180) NOT NULL,
  `sample_type_id` INT(11) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_env_package_code` (`package_code`),
  KEY `idx_env_package_type` (`sample_type_id`),
  CONSTRAINT `fk_envpkg_type` FOREIGN KEY (`sample_type_id`) REFERENCES `env_sample_types` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `env_package_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `package_id` INT(11) NOT NULL,
  `parameter_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pkg_param` (`package_id`, `parameter_id`),
  KEY `idx_pkgitem_param` (`parameter_id`),
  CONSTRAINT `fk_pkgitem_pkg` FOREIGN KEY (`package_id`) REFERENCES `env_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pkgitem_param` FOREIGN KEY (`parameter_id`) REFERENCES `env_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `lab_tariffs`
  MODIFY COLUMN `item_type` ENUM('parameter','template','env_parameter','env_package') NOT NULL DEFAULT 'parameter';

ALTER TABLE `pendaftaran_detail`
  MODIFY COLUMN `tipe_item` ENUM('parameter','template','env_parameter','env_package') NOT NULL DEFAULT 'parameter';
