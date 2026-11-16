-- Editable COBIT 2019 practice -> COBIT 5 practice mapping (page: Evolusi GAMO 4.1 -> 5 -> 2019).
-- Import once in phpMyAdmin. Safe to re-run.
-- source_code = COBIT 2019 practice (e.g. APO01.04); target_code = COBIT 5 practice (e.g. APO01.01).
-- A NULL target_code row means "reviewed, no COBIT 5 equivalent".

CREATE TABLE IF NOT EXISTS `trs_cobit2019_cobit5_mappings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_code` varchar(20) NOT NULL,
  `target_code` varchar(20) DEFAULT NULL,
  `order_no` smallint unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trs_cobit2019_cobit5_mappings_source_code_target_code_unique` (`source_code`,`target_code`),
  KEY `trs_cobit2019_cobit5_mappings_target_code_index` (`target_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Register the migration so 'php artisan migrate' does not try to create the table again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.name, 99 FROM (SELECT '2026_11_16_000001_create_trs_cobit2019_cobit5_mappings_table' AS name) m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = m.name);
