-- Editable COBIT 2019 practice -> COBIT 5 practice mapping (page: Evolusi GAMO 4.1 -> 5 -> 2019).
-- Import once in phpMyAdmin. Safe to re-run (the data part only fills practices that have no saved row).
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

-- Data: COBIT 2019 practice -> COBIT 5 practice, 198 rows (name-based match of the 2019 practice to the COBIT 5 practice of its GAMO).
-- Only fills practices that have no saved row yet, so re-running never duplicates or overwrites what was edited on the page.
-- 2019 practices without a COBIT 5 equivalent (e.g. APO14.*, MEA04.*) are left out and show 'Belum dipetakan' / 'Baru di COBIT 2019'.
INSERT INTO `trs_cobit2019_cobit5_mappings` (`source_code`, `target_code`, `order_no`, `created_at`, `updated_at`)
SELECT v.source_code, v.target_code, v.order_no, NOW(), NOW()
FROM (
  SELECT 'EDM01.01' AS source_code, 'EDM01.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM01.02' AS source_code, 'EDM01.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM01.03' AS source_code, 'EDM01.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM02.02' AS source_code, 'EDM02.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM02.03' AS source_code, 'EDM02.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM02.04' AS source_code, 'EDM02.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM03.01' AS source_code, 'EDM03.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM03.02' AS source_code, 'EDM03.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM03.03' AS source_code, 'EDM03.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM04.01' AS source_code, 'EDM04.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM04.02' AS source_code, 'EDM04.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM04.03' AS source_code, 'EDM04.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM05.01' AS source_code, 'EDM05.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM05.02' AS source_code, 'EDM05.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'EDM05.03' AS source_code, 'EDM05.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO01.02' AS source_code, 'APO01.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO01.05' AS source_code, 'APO01.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO01.06' AS source_code, 'APO01.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO01.07' AS source_code, 'APO01.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO01.11' AS source_code, 'APO01.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO02.01' AS source_code, 'APO02.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO02.02' AS source_code, 'APO02.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO02.03' AS source_code, 'APO02.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO02.04' AS source_code, 'APO02.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO02.05' AS source_code, 'APO02.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO02.06' AS source_code, 'APO02.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO03.01' AS source_code, 'APO03.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO03.02' AS source_code, 'APO03.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO03.03' AS source_code, 'APO03.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO03.04' AS source_code, 'APO03.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO03.05' AS source_code, 'APO03.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO04.01' AS source_code, 'APO04.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO04.02' AS source_code, 'APO04.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO04.03' AS source_code, 'APO04.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO04.04' AS source_code, 'APO04.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO04.05' AS source_code, 'APO04.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO04.06' AS source_code, 'APO04.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO05.01' AS source_code, 'APO05.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO05.02' AS source_code, 'APO05.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO05.03' AS source_code, 'APO05.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO05.04' AS source_code, 'APO05.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO05.05' AS source_code, 'APO05.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO06.01' AS source_code, 'APO06.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO06.02' AS source_code, 'APO06.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO06.03' AS source_code, 'APO06.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO06.04' AS source_code, 'APO06.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO06.05' AS source_code, 'APO06.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO07.01' AS source_code, 'APO07.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO07.02' AS source_code, 'APO07.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO07.03' AS source_code, 'APO07.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO07.04' AS source_code, 'APO07.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO07.05' AS source_code, 'APO07.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO07.06' AS source_code, 'APO07.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO08.01' AS source_code, 'APO08.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO08.02' AS source_code, 'APO08.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO08.03' AS source_code, 'APO08.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO08.05' AS source_code, 'APO08.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO09.01' AS source_code, 'APO09.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO09.02' AS source_code, 'APO09.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO09.03' AS source_code, 'APO09.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO09.04' AS source_code, 'APO09.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO09.05' AS source_code, 'APO09.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO10.01' AS source_code, 'APO10.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO10.02' AS source_code, 'APO10.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO10.03' AS source_code, 'APO10.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO10.04' AS source_code, 'APO10.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO10.05' AS source_code, 'APO10.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO11.01' AS source_code, 'APO11.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO11.02' AS source_code, 'APO11.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO11.04' AS source_code, 'APO11.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO11.05' AS source_code, 'APO11.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO12.01' AS source_code, 'APO12.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO12.02' AS source_code, 'APO12.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO12.03' AS source_code, 'APO12.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO12.04' AS source_code, 'APO12.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO12.05' AS source_code, 'APO12.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO12.06' AS source_code, 'APO12.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO13.01' AS source_code, 'APO13.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO13.02' AS source_code, 'APO13.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'APO13.03' AS source_code, 'APO13.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI01.01' AS source_code, 'BAI01.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI01.02' AS source_code, 'BAI01.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI01.03' AS source_code, 'BAI01.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI01.04' AS source_code, 'BAI01.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI01.05' AS source_code, 'BAI01.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI01.06' AS source_code, 'BAI01.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI02.01' AS source_code, 'BAI02.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI02.02' AS source_code, 'BAI02.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI02.03' AS source_code, 'BAI02.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI02.04' AS source_code, 'BAI02.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.01' AS source_code, 'BAI03.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.02' AS source_code, 'BAI03.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.03' AS source_code, 'BAI03.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.04' AS source_code, 'BAI03.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.05' AS source_code, 'BAI03.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.06' AS source_code, 'BAI03.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.07' AS source_code, 'BAI03.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.08' AS source_code, 'BAI03.08' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.09' AS source_code, 'BAI03.09' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.10' AS source_code, 'BAI03.10' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI03.11' AS source_code, 'BAI03.11' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI04.01' AS source_code, 'BAI04.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI04.02' AS source_code, 'BAI04.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI04.03' AS source_code, 'BAI04.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI04.04' AS source_code, 'BAI04.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI04.05' AS source_code, 'BAI04.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI05.01' AS source_code, 'BAI05.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI05.02' AS source_code, 'BAI05.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI05.03' AS source_code, 'BAI05.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI05.04' AS source_code, 'BAI05.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI05.05' AS source_code, 'BAI05.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI05.06' AS source_code, 'BAI05.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI05.07' AS source_code, 'BAI05.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI06.01' AS source_code, 'BAI06.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI06.02' AS source_code, 'BAI06.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI06.03' AS source_code, 'BAI06.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI06.04' AS source_code, 'BAI06.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.01' AS source_code, 'BAI07.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.02' AS source_code, 'BAI07.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.03' AS source_code, 'BAI07.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.04' AS source_code, 'BAI07.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.05' AS source_code, 'BAI07.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.06' AS source_code, 'BAI07.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.07' AS source_code, 'BAI07.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI07.08' AS source_code, 'BAI07.08' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI08.01' AS source_code, 'BAI08.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI08.02' AS source_code, 'BAI08.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI08.03' AS source_code, 'BAI08.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI08.04' AS source_code, 'BAI08.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI09.01' AS source_code, 'BAI09.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI09.02' AS source_code, 'BAI09.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI09.03' AS source_code, 'BAI09.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI09.04' AS source_code, 'BAI09.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI09.05' AS source_code, 'BAI09.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI10.01' AS source_code, 'BAI10.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI10.02' AS source_code, 'BAI10.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI10.03' AS source_code, 'BAI10.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI10.04' AS source_code, 'BAI10.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI10.05' AS source_code, 'BAI10.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.01' AS source_code, 'BAI01.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.02' AS source_code, 'BAI01.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.03' AS source_code, 'BAI01.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.04' AS source_code, 'BAI01.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.05' AS source_code, 'BAI01.09' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.06' AS source_code, 'BAI01.10' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.07' AS source_code, 'BAI01.11' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.08' AS source_code, 'BAI01.12' AS target_code, 1 AS order_no
  UNION ALL SELECT 'BAI11.09' AS source_code, 'BAI01.13' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS01.01' AS source_code, 'DSS01.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS01.02' AS source_code, 'DSS01.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS01.03' AS source_code, 'DSS01.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS01.04' AS source_code, 'DSS01.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS01.05' AS source_code, 'DSS01.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS02.01' AS source_code, 'DSS02.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS02.02' AS source_code, 'DSS02.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS02.03' AS source_code, 'DSS02.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS02.04' AS source_code, 'DSS02.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS02.05' AS source_code, 'DSS02.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS02.06' AS source_code, 'DSS02.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS02.07' AS source_code, 'DSS02.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS03.01' AS source_code, 'DSS03.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS03.02' AS source_code, 'DSS03.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS03.03' AS source_code, 'DSS03.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS03.04' AS source_code, 'DSS03.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS03.05' AS source_code, 'DSS03.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS04.01' AS source_code, 'DSS04.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS04.03' AS source_code, 'DSS04.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS04.04' AS source_code, 'DSS04.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS04.05' AS source_code, 'DSS04.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS04.06' AS source_code, 'DSS04.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS04.07' AS source_code, 'DSS04.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS04.08' AS source_code, 'DSS04.08' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS05.01' AS source_code, 'DSS05.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS05.02' AS source_code, 'DSS05.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS05.03' AS source_code, 'DSS05.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS05.04' AS source_code, 'DSS05.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS05.05' AS source_code, 'DSS05.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS05.06' AS source_code, 'DSS05.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS05.07' AS source_code, 'DSS05.07' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS06.01' AS source_code, 'DSS06.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS06.02' AS source_code, 'DSS06.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS06.03' AS source_code, 'DSS06.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS06.04' AS source_code, 'DSS06.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS06.05' AS source_code, 'DSS06.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'DSS06.06' AS source_code, 'DSS06.06' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA01.01' AS source_code, 'MEA01.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA01.02' AS source_code, 'MEA01.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA01.03' AS source_code, 'MEA01.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA01.04' AS source_code, 'MEA01.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA01.05' AS source_code, 'MEA01.05' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA02.01' AS source_code, 'MEA02.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA02.02' AS source_code, 'MEA02.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA02.03' AS source_code, 'MEA02.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA02.04' AS source_code, 'MEA02.04' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA03.01' AS source_code, 'MEA03.01' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA03.02' AS source_code, 'MEA03.02' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA03.03' AS source_code, 'MEA03.03' AS target_code, 1 AS order_no
  UNION ALL SELECT 'MEA03.04' AS source_code, 'MEA03.04' AS target_code, 1 AS order_no
) v
WHERE NOT EXISTS (SELECT 1 FROM `trs_cobit2019_cobit5_mappings` t WHERE t.source_code = v.source_code);

-- Register the migration so 'php artisan migrate' does not try to create the table again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.name, 99 FROM (SELECT '2026_11_16_000001_create_trs_cobit2019_cobit5_mappings_table' AS name) m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = m.name);
