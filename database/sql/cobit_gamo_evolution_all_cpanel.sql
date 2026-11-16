-- ============================================================================================
-- COBIT GAMO Mapping Evolution: semua tabel dan data dalam satu file (import sekali di phpMyAdmin).
--
-- Isi, berurutan:
--   1. Evolusi GAMO: tabel mst_cobit_evolution_domains + mst_cobit_evolution (40 GAMO) dan datanya
--   2. Mapping COBIT 4.1 -> COBIT 5: mst_cobit4_cobit5_mapping_sources + trs_cobit4_cobit5_mappings dan datanya
--   3. Mapping practice COBIT 2019 -> COBIT 5 yang diedit user: trs_cobit2019_cobit5_mappings (tabel kosong)
--   4. Katalog COBIT 5 (GAMO + practice): mst_cobit5_catalog_gamos + mst_cobit5_catalog_practices dan datanya
--
-- PENTING: file ini untuk setup awal. Bagian 1 dan 2 memakai INSERT ... ON DUPLICATE KEY UPDATE, jadi
-- import ulang MENIMPA data seed (status, note, kode GAMO COBIT 5, status mapping 4.1) dan mengembalikan
-- mapping 4.1 -> 5 yang sudah dihapus dari halaman. Setelah ada perubahan dari halaman, jangan import ulang
-- bagian 1 dan 2. Bagian 3 dan 4 aman diulang.
-- Tiap bagian juga bisa diimpor sendiri dari file aslinya di database/sql/.
-- Prasyarat: tabel `migrations` (bawaan Laravel) sudah ada.
-- ============================================================================================

-- --------------------------------------------------------------------------------------------
-- BAGIAN 1. Evolusi GAMO: tabel mst_cobit_evolution_domains + mst_cobit_evolution (40 GAMO) dan datanya
-- File asal: database/sql/cobit_gamo_evolution_cpanel.sql
-- --------------------------------------------------------------------------------------------

-- COBIT GAMO Mapping Evolution: tracking COBIT 4.1 -> COBIT 5 -> COBIT 2019 (40 GAMO).
-- Source of the 4.1 side: official COBIT 4.1 -> COBIT 5 crosswalk (see cobit4_cobit5_mapping_full.sql).
-- Status: new = no predecessor in the previous version; restructured = merged/split/partial; evolved = continues 1:1.
-- Additive and repeatable: rows are matched on cobit2019_id.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `mst_cobit_evolution` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cobit2019_id` VARCHAR(20) NOT NULL,
    `domain` VARCHAR(10) NOT NULL,
    `title_2019` VARCHAR(255) NOT NULL,
    `desc_2019` TEXT NULL,
    `cobit5_id` VARCHAR(100) NULL DEFAULT NULL,
    `cobit5_title` VARCHAR(255) NULL DEFAULT NULL,
    `cobit4_id` VARCHAR(100) NULL DEFAULT NULL,
    `cobit4_title` VARCHAR(255) NULL DEFAULT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'evolved',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `mst_cobit_evolution_cobit2019_id_unique` (`cobit2019_id`),
    KEY `mst_cobit_evolution_domain_index` (`domain`),
    KEY `mst_cobit_evolution_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mst_cobit_evolution_domains` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(10) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `type` VARCHAR(30) NOT NULL,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `mst_cobit_evolution_domains_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mst_cobit_evolution_domains` (`code`, `name`, `type`, `order_no`, `created_at`, `updated_at`) VALUES
('EDM', 'Evaluate, Direct and Monitor', 'Governance', 1, NOW(), NOW()),
('APO', 'Align, Plan and Organize', 'Management', 2, NOW(), NOW()),
('BAI', 'Build, Acquire and Implement', 'Management', 3, NOW(), NOW()),
('DSS', 'Deliver, Service and Support', 'Management', 4, NOW(), NOW()),
('MEA', 'Monitor, Evaluate and Assess', 'Management', 5, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `type`=VALUES(`type`), `order_no`=VALUES(`order_no`), `updated_at`=NOW();

INSERT INTO `mst_cobit_evolution` (`cobit2019_id`, `domain`, `title_2019`, `desc_2019`, `cobit5_id`, `cobit5_title`, `cobit4_id`, `cobit4_title`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('EDM01', 'EDM', 'Evaluated, Directed and Monitored Governance Framework Setting and Maintenance', 'Menganalisis dan mengartikulasikan persyaratan untuk tata kelola I&T enterprise, menetapkan serta memelihara struktur, proses, dan praktik tata kelola yang transparan dan dapat diandalkan.', 'EDM01', 'Ensure Governance Framework Setting and Maintenance', 'ME4', 'ME4 Provide IT Governance', 'restructured', 'ME4 dipecah ke EDM01, EDM02, EDM03, EDM04, MEA02.', NOW(), NOW()),
('EDM02', 'EDM', 'Evaluated, Directed and Monitored Benefits Delivery', 'Mengoptimalkan kontribusi nilai terhadap bisnis dari proses bisnis, layanan I&T, dan aset I&T yang dihasilkan dari pengeluaran investasi I&T dengan biaya yang dapat diterima.', 'EDM02', 'Ensure Benefits Delivery', 'ME4', 'ME4 Provide IT Governance', 'restructured', 'ME4 dipecah ke EDM01, EDM02, EDM03, EDM04, MEA02.', NOW(), NOW()),
('EDM03', 'EDM', 'Evaluated, Directed and Monitored Risk Optimization', 'Memastikan bahwa selera risiko (risk appetite) dan toleransi risiko perusahaan dipahami, diartikulasikan, dan dikomunikasikan secara konsisten di seluruh enterprise.', 'EDM03', 'Ensure Risk Optimization', 'ME4', 'ME4 Provide IT Governance', 'restructured', 'ME4 dipecah ke EDM01, EDM02, EDM03, EDM04, MEA02.', NOW(), NOW()),
('EDM04', 'EDM', 'Evaluated, Directed and Monitored Resource Optimization', 'Memastikan bahwa kapabilitas sumber daya (manusia, finansial, informasi, infrastruktur) I&T perusahaan terpenuhi secara optimal untuk mendukung sasaran strategis bisnis.', 'EDM04', 'Ensure Resource Optimization', 'ME4', 'ME4 Provide IT Governance', 'restructured', 'ME4 dipecah ke EDM01, EDM02, EDM03, EDM04, MEA02.', NOW(), NOW()),
('EDM05', 'EDM', 'Evaluated, Directed and Monitored Stakeholder Engagement', 'Memastikan kinerja dan kepatuhan sistem tata kelola I&T dikomunikasikan secara efektif kepada pemangku kepentingan internal dan eksternal secara transparan.', 'EDM05', 'Ensure Stakeholder Transparency', '-', '-', 'new', 'Baru di COBIT 5, tanpa padanan di COBIT 4.1.', NOW(), NOW()),
('APO01', 'APO', 'Managed I&T Management Framework', 'Merancang sistem manajemen terintegrasi untuk I&T enterprise berdasarkan enterprise goals dan design factors, serta menerapkan seluruh komponen tata kelola yang diperlukan.', 'APO01', 'Manage the IT Management Framework', 'PO4, PO6', 'PO4 Define the IT Processes, Organisation and Relationships & PO6 Communicate Management Aims and Direction', 'restructured', 'Gabungan PO4 + PO6.', NOW(), NOW()),
('APO02', 'APO', 'Managed Strategy', 'Mendukung strategi enterprise dengan merumuskan strategi I&T yang selaras, menerjemahkan arah strategis ke dalam rencana taktis dan inisiatif digital.', 'APO02', 'Manage Strategy', 'PO1, PO3', 'PO1 Define a Strategic IT Plan & PO3 Determine Technological Direction', 'restructured', 'Gabungan PO1 + PO3.', NOW(), NOW()),
('APO03', 'APO', 'Managed Enterprise Architecture', 'Membangun arsitektur enterprise yang konsisten yang mencakup lapisan proses bisnis, informasi/data, aplikasi, dan teknologi.', 'APO03', 'Manage Enterprise Architecture', 'PO2', 'PO2 Define the Information Architecture', 'evolved', '', NOW(), NOW()),
('APO04', 'APO', 'Managed Innovation', 'Menjaga kesadaran akan perkembangan I&T terbaru dan tren teknologi yang berpotensi memberikan keunggulan kompetitif bagi bisnis.', 'APO04', 'Manage Innovation', 'PO3', 'PO3 Determine Technological Direction', 'restructured', 'PO3 dipecah ke APO02, APO04.', NOW(), NOW()),
('APO05', 'APO', 'Managed Portfolio', 'Mengoptimalkan kinerja keseluruhan portofolio program, proyek, dan layanan I&T yang selaras dengan sasaran strategis bisnis.', 'APO05', 'Manage Portfolio', 'PO1 (parsial), PO5 (parsial)', 'PO1 Define a Strategic IT Plan (parsial) & PO5 Manage the IT Investment (parsial)', 'restructured', 'Padanan COBIT 4.1 hanya parsial.', NOW(), NOW()),
('APO06', 'APO', 'Managed Budget and Costs', 'Mengelola keuangan dan akuntansi I&T secara transparan dan akuntabel, memastikan alokasi anggaran mencerminkan prioritas bisnis.', 'APO06', 'Manage Budgets and Costs', 'PO5, DS6', 'PO5 Manage the IT Investment & DS6 Identify and Allocate Costs', 'restructured', 'Gabungan PO5 + DS6.', NOW(), NOW()),
('APO07', 'APO', 'Managed Human Resources', 'Menyediakan dan memelihara tenaga kerja I&T yang kompeten, termotivasi, dan selaras dengan kebutuhan organisasi saat ini dan masa depan.', 'APO07', 'Manage Human Resources', 'PO7, PO4, DS7', 'PO7 Manage IT Human Resources & PO4 Define the IT Processes, Organisation and Relationships & DS7 Educate and Train Users', 'restructured', 'Gabungan PO7 + PO4 + DS7.', NOW(), NOW()),
('APO08', 'APO', 'Managed Relationships', 'Menciptakan dan memelihara hubungan kolaboratif dan terpercaya antara fungsi I&T dan unit bisnis organisasi.', 'APO08', 'Manage Relationships', '-', '-', 'new', 'Baru di COBIT 5, tanpa padanan di COBIT 4.1.', NOW(), NOW()),
('APO09', 'APO', 'Managed Service Agreements', 'Menyelaraskan layanan I&T yang diberikan dengan kebutuhan bisnis melalui penetapan katalog layanan dan Service Level Agreements (SLA).', 'APO09', 'Manage Service Agreements', 'DS1', 'DS1 Define and Manage Service Levels', 'evolved', '', NOW(), NOW()),
('APO10', 'APO', 'Managed Vendors', 'Mengelola hubungan, kontrak, dan kinerja vendor eksternal untuk memastikan penyediaan produk dan layanan I&T yang optimal.', 'APO10', 'Manage Suppliers', 'AI5, DS2', 'AI5 Procure IT Resources & DS2 Manage Third-party Services', 'restructured', 'Gabungan AI5 + DS2.', NOW(), NOW()),
('APO11', 'APO', 'Managed Quality', 'Mendefinisikan dan menerapkan standar mutu untuk semua proses, produk, dan layanan I&T secara berkesinambungan.', 'APO11', 'Manage Quality', 'PO8', 'PO8 Manage Quality', 'evolved', '', NOW(), NOW()),
('APO12', 'APO', 'Managed Risk', 'Mengidentifikasi, menilai, dan memitigasi risiko terkait I&T secara berkala dalam batas toleransi risiko yang telah ditetapkan oleh dewan tata kelola.', 'APO12', 'Manage Risk', 'PO9', 'PO9 Assess and Manage IT Risks', 'evolved', '', NOW(), NOW()),
('APO13', 'APO', 'Managed Security', 'Mendefinisikan, mengoperasikan, dan memantau sistem manajemen keamanan informasi (ISMS) untuk melindungi kerahasiaan, integritas, dan ketersediaan aset informasi.', 'APO13', 'Manage Security', 'DS5', 'DS5 Ensure Systems Security', 'restructured', 'DS5 dipecah ke APO13, DSS05.', NOW(), NOW()),
('APO14', 'APO', 'Managed Data', 'Memastikan bahwa data enterprise dikelola sebagai aset bernilai strategis, menjamin kualitas, keamanan, privasi, kepatuhan, dan ketersediaan data.', 'Belum ada', 'Tidak tersedia di COBIT 5 (tersebar parsial di DSS06)', 'DS11 (parsial)', 'DS11 Manage Data (parsial)', 'new', 'Baru di COBIT 2019; konsep terkait DS11 di COBIT 4.1.', NOW(), NOW()),
('BAI01', 'BAI', 'Managed Programs', 'Mengelola program investasi I&T secara menyeluruh, mengoordinasikan paket proyek yang saling terkait untuk memastikan realisasi manfaat bisnis.', 'BAI01 (parsial)', 'Manage Programmes and Projects', 'PO10', 'PO10 Manage Projects', 'restructured', 'Dipecah di COBIT 2019: program tetap di BAI01, proyek ke BAI11.', NOW(), NOW()),
('BAI02', 'BAI', 'Managed Requirements Definition', 'Mengidentifikasi dan mendokumentasikan kebutuhan bisnis dan teknis secara jelas, serta memvalidasi kesesuaian solusi digital dengan kebutuhan organisasi.', 'BAI02', 'Manage Requirements Definition', 'AI1', 'AI1 Identify Automated Solutions', 'evolved', '', NOW(), NOW()),
('BAI03', 'BAI', 'Managed Solutions Identification and Build', 'Membangun atau mengadakan solusi I&T yang tepat guna sesuai kebutuhan bisnis, mencakup perangkat lunak, infrastruktur, dan integrasi sistem.', 'BAI03', 'Manage Solutions Identification and Build', 'AI2, AI3, AC1-AC6', 'AI2 Acquire and Maintain Application Software & AI3 Acquire and Maintain Technology Infrastructure & AC1-AC6 Application Controls', 'restructured', 'Gabungan AI2 + AI3 + AC1-AC6.', NOW(), NOW()),
('BAI04', 'BAI', 'Managed Availability and Capacity', 'Mengimbangi kebutuhan ketersediaan dan kapasitas layanan I&T terhadap biaya dan sumber daya yang tersedia untuk memenuhi ekspektasi performa.', 'BAI04', 'Manage Availability and Capacity', 'DS3', 'DS3 Manage Performance and Capacity', 'evolved', '', NOW(), NOW()),
('BAI05', 'BAI', 'Managed Organizational Change', 'Mempersiapkan dan mengelola pemangku kepentingan organisasi agar siap mengadopsi perubahan proses bisnis dan sistem teknologi baru.', 'BAI05', 'Manage Organisational Change Enablement', 'AI4 (parsial), AI7 (parsial)', 'AI4 Enable Operation and Use (parsial) & AI7 Install and Accredit Solutions and Changes (parsial)', 'restructured', 'Padanan COBIT 4.1 hanya parsial.', NOW(), NOW()),
('BAI06', 'BAI', 'Managed IT Changes', 'Mengendalikan seluruh perubahan pada sistem I&T secara terencana dan terstruktur untuk meminimalkan dampak gangguan operasional pada bisnis.', 'BAI06', 'Manage Changes', 'AI6', 'AI6 Manage Changes', 'evolved', '', NOW(), NOW()),
('BAI07', 'BAI', 'Managed IT Change Acceptance and Transition', 'Menguji dan mengesahkan solusi sistem baru atau perubahan sistem sebelum diterapkan ke lingkungan produksi operasional bisnis.', 'BAI07', 'Manage Change Acceptance and Transition', 'AI7', 'AI7 Install and Accredit Solutions and Changes', 'evolved', '', NOW(), NOW()),
('BAI08', 'BAI', 'Managed Knowledge', 'Memelihara ketersediaan pengetahuan, informasi operasional, dan dokumentasi yang relevan untuk mendukung seluruh aktivitas I&T.', 'BAI08', 'Manage Knowledge', 'AI4', 'AI4 Enable Operation and Use', 'evolved', '', NOW(), NOW()),
('BAI09', 'BAI', 'Managed Assets', 'Mengelola aset I&T di seluruh siklus hidupnya untuk memastikan nilai penggunaan aset maksimal dan kepatuhan terhadap lisensi.', 'BAI09', 'Manage Assets', 'DS13 (parsial)', 'DS13 Manage Operations (parsial)', 'restructured', 'Padanan COBIT 4.1 hanya parsial.', NOW(), NOW()),
('BAI10', 'BAI', 'Managed Configuration', 'Mendefinisikan dan memelihara informasi tentang seluruh item konfigurasi (CI) dan keterhubungannya dalam Configuration Management Database (CMDB).', 'BAI10', 'Manage Configuration', 'DS9', 'DS9 Manage the Configuration', 'restructured', 'DS9 dipecah ke BAI10, DSS02.', NOW(), NOW()),
('BAI11', 'BAI', 'Managed Projects', 'Mengelola setiap proyek I&T secara terdefinisi dan konsisten, mencakup perencanaan lingkup, jadwal, biaya, risiko, dan penyampaian hasil proyek.', 'Dipisah dari BAI01', 'Dipisahkan dari COBIT 5 BAI01 (Manage Programmes and Projects)', 'PO10', 'PO10 Manage Projects', 'restructured', 'Dipisah dari BAI01 COBIT 5 sebagai objective proyek tersendiri.', NOW(), NOW()),
('DSS01', 'DSS', 'Managed Operations', 'Menjalankan prosedur operasional I&T sehari-hari secara konsisten dan andal untuk memastikan kestabilan pengiriman layanan bagi bisnis.', 'DSS01', 'Manage Operations', 'DS12, DS13, DS11', 'DS12 Manage the Physical Environment & DS13 Manage Operations & DS11 Manage Data', 'restructured', 'Gabungan DS12 + DS13 + DS11.', NOW(), NOW()),
('DSS02', 'DSS', 'Managed Service Requests and Incidents', 'Memberikan respons tepat waktu terhadap permintaan layanan dan insiden I&T untuk meminimalkan dampak terhadap produktivitas bisnis.', 'DSS02', 'Manage Service Requests and Incidents', 'DS8, DS9', 'DS8 Manage Service Desk and Incidents & DS9 Manage the Configuration', 'restructured', 'Gabungan DS8 + DS9.', NOW(), NOW()),
('DSS03', 'DSS', 'Managed Problems', 'Mengidentifikasi akar penyebab (root cause) insiden, mencatat solusi sementara (workaround), dan mencegah insiden serupa terulang kembali.', 'DSS03', 'Manage Problems', 'DS10', 'DS10 Manage Problems', 'evolved', '', NOW(), NOW()),
('DSS04', 'DSS', 'Managed Continuity', 'Menetapkan dan menguji kapabilitas ketahanan dan pemulihan sistem I&T (Disaster Recovery) untuk memastikan kontinuitas operasi bisnis.', 'DSS04', 'Manage Continuity', 'DS4, DS11', 'DS4 Ensure Continuous Service & DS11 Manage Data', 'restructured', 'Gabungan DS4 + DS11.', NOW(), NOW()),
('DSS05', 'DSS', 'Managed Security Services', 'Mengoperasikan kontrol keamanan teknis untuk melindungi aset I&T enterprise dari ancaman siber, malware, dan akses yang tidak sah.', 'DSS05', 'Manage Security Services', 'DS5, DS12, DS11', 'DS5 Ensure Systems Security & DS12 Manage the Physical Environment & DS11 Manage Data', 'restructured', 'Gabungan DS5 + DS12 + DS11.', NOW(), NOW()),
('DSS06', 'DSS', 'Managed Business Process Controls', 'Menerapkan dan memelihara pengendalian aplikasi terotomasi dalam proses bisnis untuk menjaga integritas dan keabsahan transaksi informasi.', 'DSS06', 'Manage Business Process Controls', 'AC1-AC6, DS11', 'AC1-AC6 Application Controls & DS11 Manage Data', 'restructured', 'Gabungan AC1-AC6 + DS11.', NOW(), NOW()),
('MEA01', 'MEA', 'Managed Performance and Conformance Monitoring', 'Mengumpulkan, memvalidasi, dan mengevaluasi data metrik proses I&T untuk memastikan kesesuaian target kinerja dan rencana bisnis.', 'MEA01', 'Monitor, Evaluate and Assess Performance and Conformance', 'ME1', 'ME1 Monitor and Evaluate IT Performance', 'evolved', '', NOW(), NOW()),
('MEA02', 'MEA', 'Managed System of Internal Control', 'Secara berkesinambungan memantau dan mengevaluasi efektivitas lingkungan pengendalian internal I&T enterprise.', 'MEA02', 'Monitor, Evaluate and Assess the System of Internal Control', 'ME2, ME4', 'ME2 Monitor and Evaluate Internal Control & ME4 Provide IT Governance', 'restructured', 'Gabungan ME2 + ME4.', NOW(), NOW()),
('MEA03', 'MEA', 'Managed Compliance With External Requirements', 'Mengevaluasi dan memastikan bahwa proses I&T dan solusi teknologi senantiasa mematuhi semua hukum, regulasi, dan kewajiban kontrak eksternal.', 'MEA03', 'Monitor, Evaluate and Assess Compliance with External Requirements', 'ME3', 'ME3 Ensure Compliance With External Requirements', 'evolved', '', NOW(), NOW()),
('MEA04', 'MEA', 'Managed Assurance', 'Merencanakan, memfasilitasi, dan melaksanakan inisiatif penjaminan (assurance) I&T independen untuk memberikan keyakinan memadai bagi dewan dan regulator.', 'Belum ada', 'Tidak tersedia di COBIT 5 (tersebar parsial di MEA02)', 'ME2 (parsial), ME4 (parsial)', 'ME2 Monitor and Evaluate Internal Control (parsial) & ME4 Provide IT Governance (parsial)', 'new', 'Baru di COBIT 2019; terkait ME2 dan ME4 di COBIT 4.1.', NOW(), NOW())
ON DUPLICATE KEY UPDATE `domain`=VALUES(`domain`), `title_2019`=VALUES(`title_2019`), `desc_2019`=VALUES(`desc_2019`), `cobit5_id`=VALUES(`cobit5_id`), `cobit5_title`=VALUES(`cobit5_title`), `cobit4_id`=VALUES(`cobit4_id`), `cobit4_title`=VALUES(`cobit4_title`), `status`=VALUES(`status`), `notes`=VALUES(`notes`), `updated_at`=NOW();

-- Register the migrations so 'php artisan migrate' does not try to create the tables again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.name, 99 FROM (SELECT '2026_10_12_000001_create_mst_cobit_evolution_table' AS name UNION SELECT '2026_11_13_000001_create_mst_cobit_evolution_domains_table') m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = m.name);

-- --------------------------------------------------------------------------------------------
-- BAGIAN 2. Mapping COBIT 4.1 -> COBIT 5: mst_cobit4_cobit5_mapping_sources + trs_cobit4_cobit5_mappings dan datanya
-- File asal: database/sql/cobit4_cobit5_mapping_full.sql
-- --------------------------------------------------------------------------------------------

-- COBIT 4.1 to COBIT 5 mapping data (additive and repeatable; preserves unrelated rows).
-- Creates the two mapping tables if needed, then upserts all source and relation rows.
-- Create the mapping tables if they are not present yet (matches the Laravel migration).
CREATE TABLE IF NOT EXISTS `mst_cobit4_cobit5_mapping_sources` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL,
    `domain` VARCHAR(10) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `mapping_status` VARCHAR(20) NOT NULL DEFAULT 'mapped',
    `mapping_note` TEXT NULL,
    `review_note` TEXT NULL,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `mst_cobit4_cobit5_mapping_sources_code_unique` (`code`),
    KEY `mst_cobit4_cobit5_mapping_sources_domain_index` (`domain`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `trs_cobit4_cobit5_mappings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `source_id` BIGINT UNSIGNED NOT NULL,
    `target_code` VARCHAR(20) NOT NULL,
    `target_level` VARCHAR(20) NOT NULL,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `trs_cobit4_cobit5_mappings_source_id_target_code_unique` (`source_id`, `target_code`),
    KEY `trs_cobit4_cobit5_mappings_target_code_index` (`target_code`),
    CONSTRAINT `trs_cobit4_cobit5_mappings_source_id_foreign`
        FOREIGN KEY (`source_id`) REFERENCES `mst_cobit4_cobit5_mapping_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

START TRANSACTION;

INSERT INTO `mst_cobit4_cobit5_mapping_sources`
    (`code`, `domain`, `title`, `mapping_status`, `mapping_note`, `review_note`, `order_no`, `created_at`, `updated_at`)
VALUES
    ('AC1', 'AC', 'Source Data Preparation and Authorisation', 'mapped', NULL, NULL, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC2', 'AC', 'Source Data Collection and Entry', 'mapped', NULL, NULL, 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC3', 'AC', 'Accuracy, Completeness and Authenticity Checks', 'mapped', NULL, NULL, 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC4', 'AC', 'Processing Integrity and Validity', 'mapped', NULL, NULL, 4, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC5', 'AC', 'Output Review, Reconciliation and Error Handling', 'mapped', NULL, NULL, 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC6', 'AC', 'Transaction Authentication and Integrity', 'mapped', NULL, NULL, 6, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.1', 'PO', 'IT Value Management', 'mapped', NULL, NULL, 7, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.2', 'PO', 'Business-IT Alignment', 'mapped', NULL, NULL, 8, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.3', 'PO', 'Assessment of Current Capability and Performance', 'mapped', NULL, NULL, 9, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.4', 'PO', 'IT Strategic Plan', 'mapped', NULL, NULL, 10, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.5', 'PO', 'IT Tactical Plans', 'mapped', NULL, NULL, 11, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.6', 'PO', 'IT Portfolio Management', 'mapped', NULL, NULL, 12, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.1', 'PO', 'Enterprise Information Architecture Model', 'mapped', NULL, NULL, 13, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.2', 'PO', 'Enterprise Data Dictionary and Data Syntax Rules', 'mapped', NULL, NULL, 14, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.3', 'PO', 'Data Classification Scheme', 'mapped', NULL, NULL, 15, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.4', 'PO', 'Integrity Management', 'mapped', NULL, NULL, 16, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.1', 'PO', 'Technological Direction Planning', 'mapped', NULL, NULL, 17, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.2', 'PO', 'Technical Infrastructure Plan', 'mapped', NULL, NULL, 18, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.3', 'PO', 'Monitor Future Trends and Regulations', 'mapped', NULL, NULL, 19, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.4', 'PO', 'Technology Standards', 'mapped', NULL, NULL, 20, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.5', 'PO', 'IT Architecture Board', 'mapped', NULL, NULL, 21, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.1', 'PO', 'IT Process Framework', 'mapped', NULL, NULL, 22, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.2', 'PO', 'IT Strategy Committee', 'mapped', NULL, NULL, 23, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.3', 'PO', 'IT Steering Committee', 'mapped', NULL, NULL, 24, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.4', 'PO', 'Organisational Placement of the IT Function', 'mapped', NULL, NULL, 25, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.5', 'PO', 'IT Organisational Structure', 'mapped', NULL, NULL, 26, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.6', 'PO', 'Establishment of Roles and Responsibilities', 'mapped', NULL, NULL, 27, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.7', 'PO', 'Responsibility for IT Quality Assurance', 'mapped', NULL, NULL, 28, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.8', 'PO', 'Responsibility for Risk, Security and Compliance', 'deleted', 'These specific roles are no longer explicitly specified as a practice.', NULL, 29, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.9', 'PO', 'Data and System Ownership', 'mapped', NULL, NULL, 30, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.10', 'PO', 'Supervision', 'mapped', NULL, NULL, 31, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.11', 'PO', 'Segregation of Duties', 'mapped', NULL, 'DSS08.02 is retained as shown in the supplied mapping image. Verify this code: the COBIT 5 process reference model lists DSS01–DSS06, not DSS08.', 32, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.12', 'PO', 'IT Staffing', 'mapped', NULL, NULL, 33, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.13', 'PO', 'Key IT Personnel', 'mapped', NULL, NULL, 34, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.14', 'PO', 'Contracted Staff Policies and Procedures', 'mapped', NULL, NULL, 35, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.15', 'PO', 'Relationships', 'mapped', NULL, NULL, 36, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.1', 'PO', 'Financial Management Framework', 'mapped', NULL, NULL, 37, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.2', 'PO', 'Prioritisation Within IT Budget', 'mapped', NULL, NULL, 38, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.3', 'PO', 'IT Budgeting', 'mapped', NULL, NULL, 39, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.4', 'PO', 'Cost Management', 'mapped', NULL, NULL, 40, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.5', 'PO', 'Benefit Management', 'mapped', NULL, NULL, 41, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.1', 'PO', 'IT Policy and Control Environment', 'mapped', NULL, NULL, 42, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.2', 'PO', 'Enterprise IT Risk and Control Framework', 'mapped', NULL, NULL, 43, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.3', 'PO', 'IT Policies Management', 'mapped', NULL, NULL, 44, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.4', 'PO', 'Policy, Standards and Procedures Rollout', 'mapped', NULL, NULL, 45, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.5', 'PO', 'Communication of IT Objectives and Direction', 'mapped', NULL, NULL, 46, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.1', 'PO', 'Personnel Recruitment and Retention', 'mapped', NULL, 'Figure 14 maps both practices to PO7.1; the supplied image labels the second row PO7.2.', 47, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.2', 'PO', 'Personnel Competencies', 'mapped', NULL, NULL, 48, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.3', 'PO', 'Staffing of Roles', 'mapped', NULL, 'Figure 14 includes APO07.01 as well as APO01.02.', 49, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.4', 'PO', 'Personnel Training', 'mapped', NULL, NULL, 50, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.5', 'PO', 'Dependence Upon Individuals', 'mapped', NULL, NULL, 51, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.6', 'PO', 'Personnel Clearance Procedures', 'mapped', NULL, NULL, 52, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.7', 'PO', 'Employee Job Performance Evaluation', 'mapped', NULL, NULL, 53, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.8', 'PO', 'Job Change and Termination', 'mapped', NULL, NULL, 54, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.1', 'PO', 'Quality Management System', 'mapped', NULL, NULL, 55, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.2', 'PO', 'IT Standards and Quality Practices', 'mapped', NULL, NULL, 56, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.3', 'PO', 'Development and Acquisition Standards', 'mapped', NULL, NULL, 57, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.4', 'PO', 'Customer Focus', 'mapped', NULL, NULL, 58, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.5', 'PO', 'Continuous Improvement', 'mapped', NULL, NULL, 59, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.6', 'PO', 'Quality Measurement, Monitoring and Review', 'mapped', NULL, NULL, 60, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.1', 'PO', 'IT Risk Management Framework', 'mapped', NULL, NULL, 61, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.2', 'PO', 'Establishment of Risk Context', 'mapped', NULL, NULL, 62, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.3', 'PO', 'Event Identification', 'mapped', NULL, NULL, 63, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.4', 'PO', 'Risk Assessment', 'mapped', NULL, NULL, 64, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.5', 'PO', 'Risk Response', 'mapped', NULL, NULL, 65, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.6', 'PO', 'Maintenance and Monitoring of a Risk Action Plan', 'mapped', NULL, NULL, 66, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.1', 'PO', 'Programme Management Framework', 'mapped', NULL, NULL, 67, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.2', 'PO', 'Project Management Framework', 'mapped', NULL, NULL, 68, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.3', 'PO', 'Project Management Approach', 'mapped', NULL, NULL, 69, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.4', 'PO', 'Stakeholder Commitment', 'mapped', NULL, NULL, 70, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.5', 'PO', 'Project Scope Statement', 'mapped', NULL, NULL, 71, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.6', 'PO', 'Project Phase Initiation', 'mapped', NULL, NULL, 72, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.7', 'PO', 'Integrated Project Plan', 'mapped', NULL, NULL, 73, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.8', 'PO', 'Project Resources', 'mapped', NULL, NULL, 74, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.9', 'PO', 'Project Risk Management', 'mapped', NULL, NULL, 75, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.10', 'PO', 'Project Quality Plan', 'mapped', NULL, NULL, 76, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.11', 'PO', 'Project Change Control', 'mapped', NULL, NULL, 77, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.12', 'PO', 'Project Planning of Assurance Methods', 'mapped', NULL, NULL, 78, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.13', 'PO', 'Project Performance Measurement, Reporting and Monitoring', 'mapped', NULL, NULL, 79, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.14', 'PO', 'Project Closure', 'mapped', NULL, NULL, 80, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.1', 'AI', 'Definition and Maintenance of Business Functional and Technical Requirements', 'mapped', NULL, NULL, 81, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.2', 'AI', 'Risk Analysis Report', 'mapped', NULL, NULL, 82, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.3', 'AI', 'Feasibility Study and Formulation of Alternative Courses of Action', 'mapped', NULL, NULL, 83, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.4', 'AI', 'Requirements and Feasibility Decision and Approval', 'mapped', NULL, NULL, 84, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.1', 'AI', 'High-level Design', 'mapped', NULL, NULL, 85, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.2', 'AI', 'Detailed Design', 'mapped', NULL, NULL, 86, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.3', 'AI', 'Application Control and Auditability', 'mapped', NULL, NULL, 87, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.4', 'AI', 'Application Security and Availability', 'mapped', NULL, NULL, 88, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.5', 'AI', 'Configuration and Implementation of Acquired Application Software', 'mapped', NULL, NULL, 89, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.6', 'AI', 'Major Upgrades to Existing Systems', 'mapped', NULL, NULL, 90, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.7', 'AI', 'Development of Application Software', 'mapped', NULL, NULL, 91, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.8', 'AI', 'Software Quality Assurance', 'mapped', NULL, NULL, 92, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.9', 'AI', 'Applications Requirements Management', 'mapped', NULL, NULL, 93, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.10', 'AI', 'Application Software Maintenance', 'mapped', NULL, NULL, 94, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.1', 'AI', 'Technological Infrastructure Acquisition Plan', 'mapped', NULL, NULL, 95, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.2', 'AI', 'Infrastructure Resource Protection and Availability', 'mapped', NULL, NULL, 96, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.3', 'AI', 'Infrastructure Maintenance', 'mapped', NULL, NULL, 97, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.4', 'AI', 'Feasibility Test Environment', 'mapped', NULL, NULL, 98, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.1', 'AI', 'Planning for Operational Solutions', 'mapped', NULL, NULL, 99, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.2', 'AI', 'Knowledge Transfer to Business Management', 'mapped', NULL, NULL, 100, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.3', 'AI', 'Knowledge Transfer to End Users', 'mapped', NULL, NULL, 101, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.4', 'AI', 'Knowledge Transfer to Operations and Support Staff', 'mapped', NULL, NULL, 102, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.1', 'AI', 'Procurement Control', 'mapped', NULL, NULL, 103, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.2', 'AI', 'Supplier Contract Management', 'mapped', NULL, NULL, 104, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.3', 'AI', 'Supplier Selection', 'mapped', NULL, NULL, 105, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.4', 'AI', 'IT Resources Acquisition', 'mapped', NULL, NULL, 106, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.1', 'AI', 'Change Standards and Procedures', 'mapped', NULL, NULL, 107, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.2', 'AI', 'Impact Assessment, Prioritisation and Authorisation', 'mapped', NULL, NULL, 108, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.3', 'AI', 'Emergency Changes', 'mapped', NULL, NULL, 109, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.4', 'AI', 'Change Status Tracking and Reporting', 'mapped', NULL, NULL, 110, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.5', 'AI', 'Change Closure and Documentation', 'mapped', NULL, NULL, 111, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.1', 'AI', 'Training', 'mapped', NULL, NULL, 112, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.2', 'AI', 'Test Plan', 'mapped', NULL, NULL, 113, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.3', 'AI', 'Implementation Plan', 'mapped', NULL, NULL, 114, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.4', 'AI', 'Test Environment', 'mapped', NULL, NULL, 115, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.5', 'AI', 'System and Data Conversion', 'mapped', NULL, NULL, 116, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.6', 'AI', 'Testing of Changes', 'mapped', NULL, NULL, 117, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.7', 'AI', 'Final Acceptance Test', 'mapped', NULL, NULL, 118, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.8', 'AI', 'Promotion to Production', 'mapped', NULL, NULL, 119, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.9', 'AI', 'Post-implementation Review', 'mapped', NULL, NULL, 120, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.1', 'DS', 'Service Level Management Framework', 'mapped', NULL, NULL, 121, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.2', 'DS', 'Definition of Services', 'mapped', NULL, 'The supplied reverse table shows APO09.01 for DS1.2; repeated identical rows are represented once.', 122, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.3', 'DS', 'Service Level Agreements', 'mapped', NULL, NULL, 123, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.4', 'DS', 'Operating Level Agreements', 'mapped', NULL, NULL, 124, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.5', 'DS', 'Monitoring and Reporting of Service Level Achievements', 'mapped', NULL, NULL, 125, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.6', 'DS', 'Review of Service Level Agreements and Contracts', 'mapped', NULL, NULL, 126, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.1', 'DS', 'Identification of All Supplier Relationships', 'mapped', NULL, NULL, 127, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.2', 'DS', 'Supplier Relationship Management', 'mapped', NULL, NULL, 128, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.3', 'DS', 'Supplier Risk Management', 'mapped', NULL, NULL, 129, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.4', 'DS', 'Supplier Performance Monitoring', 'mapped', NULL, NULL, 130, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.1', 'DS', 'Performance and Capacity Planning', 'mapped', NULL, NULL, 131, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.2', 'DS', 'Current Performance and Capacity', 'mapped', NULL, NULL, 132, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.3', 'DS', 'Future Performance and Capacity', 'mapped', NULL, NULL, 133, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.4', 'DS', 'IT Resources Availability', 'mapped', NULL, NULL, 134, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.5', 'DS', 'Monitoring and Reporting', 'mapped', NULL, NULL, 135, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.1', 'DS', 'IT Continuity Framework', 'mapped', NULL, NULL, 136, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.2', 'DS', 'IT Continuity Plans', 'mapped', NULL, NULL, 137, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.3', 'DS', 'Critical IT Resources', 'mapped', NULL, NULL, 138, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.4', 'DS', 'Maintenance of the IT Continuity Plan', 'mapped', NULL, NULL, 139, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.5', 'DS', 'Testing of the IT Continuity Plan', 'mapped', NULL, NULL, 140, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.6', 'DS', 'IT Continuity Plan Training', 'mapped', NULL, NULL, 141, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.7', 'DS', 'Distribution of the IT Continuity Plan', 'mapped', NULL, NULL, 142, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.8', 'DS', 'IT Services Recovery and Resumption', 'mapped', NULL, NULL, 143, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.9', 'DS', 'Offsite Backup Storage', 'mapped', NULL, NULL, 144, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.10', 'DS', 'Post-resumption Review', 'mapped', NULL, NULL, 145, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.1', 'DS', 'Management of IT Security', 'mapped', NULL, NULL, 146, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.2', 'DS', 'IT Security Plan', 'mapped', NULL, NULL, 147, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.3', 'DS', 'Identity Management', 'mapped', NULL, NULL, 148, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.4', 'DS', 'User Account Management', 'mapped', NULL, NULL, 149, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.5', 'DS', 'Security Testing, Surveillance and Monitoring', 'mapped', NULL, NULL, 150, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.6', 'DS', 'Security Incident Definition', 'mapped', NULL, NULL, 151, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.7', 'DS', 'Protection of Security Technology', 'mapped', NULL, NULL, 152, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.8', 'DS', 'Cryptographic Key Management', 'mapped', NULL, NULL, 153, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.9', 'DS', 'Malicious Software Prevention, Detection and Correction', 'mapped', NULL, NULL, 154, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.10', 'DS', 'Network Security', 'mapped', NULL, NULL, 155, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.11', 'DS', 'Exchange of Sensitive Data', 'mapped', NULL, NULL, 156, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.1', 'DS', 'Definition of Services', 'mapped', NULL, NULL, 157, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.2', 'DS', 'IT Accounting', 'mapped', NULL, NULL, 158, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.3', 'DS', 'Cost Modelling and Charging', 'mapped', NULL, NULL, 159, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.4', 'DS', 'Cost Model Maintenance', 'mapped', NULL, NULL, 160, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS7.1', 'DS', 'Identification of Education and Training Needs', 'mapped', NULL, NULL, 161, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS7.2', 'DS', 'Delivery of Training and Education', 'mapped', NULL, NULL, 162, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS7.3', 'DS', 'Evaluation of Training Received', 'mapped', NULL, NULL, 163, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.1', 'DS', 'Service Desk', 'deleted', 'ITIL 3 does not refer to Service Desk as a process.', NULL, 164, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.2', 'DS', 'Registration of Customer Queries', 'mapped', NULL, NULL, 165, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.3', 'DS', 'Incident Escalation', 'mapped', NULL, NULL, 166, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.4', 'DS', 'Incident Closure', 'mapped', NULL, NULL, 167, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.5', 'DS', 'Reporting and Trend Analysis', 'mapped', NULL, NULL, 168, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS9.1', 'DS', 'Configuration Repository and Baseline', 'mapped', NULL, NULL, 169, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS9.2', 'DS', 'Identification and Maintenance of Configuration Items', 'mapped', NULL, NULL, 170, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS9.3', 'DS', 'Configuration Integrity Review', 'mapped', NULL, NULL, 171, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.1', 'DS', 'Identification and Classification of Problems', 'mapped', NULL, NULL, 172, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.2', 'DS', 'Problem Tracking and Resolution', 'mapped', NULL, NULL, 173, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.3', 'DS', 'Problem Closure', 'mapped', NULL, NULL, 174, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.4', 'DS', 'Integration of Configuration, Incident and Problem Management', 'mapped', NULL, NULL, 175, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.1', 'DS', 'Business Requirements for Data Management', 'mapped', NULL, NULL, 176, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.2', 'DS', 'Storage and Retention Arrangements', 'mapped', NULL, NULL, 177, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.3', 'DS', 'Media Library Management System', 'mapped', NULL, NULL, 178, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.4', 'DS', 'Disposal', 'mapped', NULL, NULL, 179, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.5', 'DS', 'Backup and Restoration', 'mapped', NULL, NULL, 180, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.6', 'DS', 'Security Requirements for Data Management', 'mapped', NULL, NULL, 181, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.1', 'DS', 'Site Selection and Layout', 'mapped', NULL, NULL, 182, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.2', 'DS', 'Physical Security Measures', 'mapped', NULL, NULL, 183, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.3', 'DS', 'Physical Access', 'mapped', NULL, NULL, 184, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.4', 'DS', 'Protection Against Environmental Factors', 'mapped', NULL, NULL, 185, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.5', 'DS', 'Physical Facilities Management', 'mapped', NULL, NULL, 186, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.1', 'DS', 'Operations Procedures and Instructions', 'mapped', NULL, NULL, 187, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.2', 'DS', 'Job Scheduling', 'mapped', NULL, NULL, 188, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.3', 'DS', 'IT Infrastructure Monitoring', 'mapped', NULL, NULL, 189, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.4', 'DS', 'Sensitive Documents and Output Devices', 'mapped', NULL, NULL, 190, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.5', 'DS', 'Preventive Maintenance for Hardware', 'mapped', NULL, NULL, 191, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.1', 'ME', 'Monitoring Approach', 'mapped', NULL, NULL, 192, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.2', 'ME', 'Definition and Collection of Monitoring Data', 'mapped', NULL, NULL, 193, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.3', 'ME', 'Monitoring Method', 'mapped', NULL, NULL, 194, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.4', 'ME', 'Performance Assessment', 'mapped', NULL, NULL, 195, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.5', 'ME', 'Board and Executive Reporting', 'mapped', NULL, NULL, 196, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.6', 'ME', 'Remedial Actions', 'mapped', NULL, NULL, 197, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.1', 'ME', 'Monitoring of Internal Control Framework', 'mapped', NULL, NULL, 198, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.2', 'ME', 'Supervisory Review', 'mapped', NULL, NULL, 199, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.3', 'ME', 'Control Exceptions', 'mapped', NULL, NULL, 200, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.4', 'ME', 'Control Self-assessment', 'mapped', NULL, NULL, 201, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.5', 'ME', 'Assurance of Internal Control', 'mapped', NULL, NULL, 202, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.6', 'ME', 'Internal Control at Third Parties', 'mapped', NULL, NULL, 203, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.7', 'ME', 'Remedial Actions', 'mapped', NULL, NULL, 204, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.1', 'ME', 'Identification of External Legal, Regulatory and Contractual Compliance Requirements', 'mapped', NULL, NULL, 205, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.2', 'ME', 'Optimisation of Response to External Requirements', 'mapped', NULL, NULL, 206, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.3', 'ME', 'Evaluation of Compliance With External Requirements', 'mapped', NULL, NULL, 207, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.4', 'ME', 'Positive Assurance of Compliance', 'mapped', NULL, NULL, 208, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.5', 'ME', 'Integrated Reporting', 'mapped', NULL, NULL, 209, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.1', 'ME', 'Establishment of an IT Governance Framework', 'mapped', NULL, NULL, 210, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.2', 'ME', 'Strategic Alignment', 'deleted', 'In COBIT 5, alignment is considered the result of all governance and management activities.', NULL, 211, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.3', 'ME', 'Value Delivery', 'mapped', NULL, NULL, 212, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.4', 'ME', 'Resource Management', 'mapped', NULL, NULL, 213, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.5', 'ME', 'Risk Management', 'mapped', NULL, NULL, 214, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.6', 'ME', 'Performance Measurement', 'mapped', NULL, NULL, 215, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.7', 'ME', 'Independent Assurance', 'mapped', NULL, 'The supplied image renders the last code as MEA02-08; Figure 14 gives the MEA02.08 practice.', 216, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
    `domain` = VALUES(`domain`),
    `title` = VALUES(`title`),
    `mapping_status` = VALUES(`mapping_status`),
    `mapping_note` = VALUES(`mapping_note`),
    `review_note` = VALUES(`review_note`),
    `order_no` = VALUES(`order_no`),
    `updated_at` = CURRENT_TIMESTAMP;

INSERT INTO `trs_cobit4_cobit5_mappings`
    (`source_id`, `target_code`, `target_level`, `order_no`, `created_at`, `updated_at`)
SELECT
    `source`.`id`,
    `mapping_rows`.`target_code`,
    `mapping_rows`.`target_level`,
    `mapping_rows`.`order_no`,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM `mst_cobit4_cobit5_mapping_sources` AS `source`
INNER JOIN (
    SELECT 'AC1' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'DSS06.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.02' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 5 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.07' AS `target_code`, 'practice' AS `target_level`, 6 AS `order_no`
    UNION ALL SELECT 'AC2' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC3' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC4' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC5' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC6' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.1' AS `source_code`, 'EDM02' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.2' AS `source_code`, 'APO02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.3' AS `source_code`, 'APO02.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.4' AS `source_code`, 'APO02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.4' AS `source_code`, 'APO02.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO1.4' AS `source_code`, 'APO02.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'PO1.5' AS `source_code`, 'APO02.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.6' AS `source_code`, 'APO05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.1' AS `source_code`, 'APO03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.2' AS `source_code`, 'APO03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.3' AS `source_code`, 'APO03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.4' AS `source_code`, 'APO01.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.1' AS `source_code`, 'APO02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.1' AS `source_code`, 'APO04.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO02.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO02.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO04.03' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO04.04' AS `target_code`, 'practice' AS `target_level`, 5 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO04.05' AS `target_code`, 'practice' AS `target_level`, 6 AS `order_no`
    UNION ALL SELECT 'PO3.3' AS `source_code`, 'EDM01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.3' AS `source_code`, 'APO04.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO3.4' AS `source_code`, 'APO03.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.5' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.1' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.1' AS `source_code`, 'APO01.07' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO4.2' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.3' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.4' AS `source_code`, 'APO01.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.5' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.6' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.7' AS `source_code`, 'APO11.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.9' AS `source_code`, 'APO01.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.10' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.11' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.11' AS `source_code`, 'DSS08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO4.12' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.13' AS `source_code`, 'APO07.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.14' AS `source_code`, 'APO07.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.15' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.1' AS `source_code`, 'APO06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.2' AS `source_code`, 'APO06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.3' AS `source_code`, 'APO06.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.4' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.4' AS `source_code`, 'APO06.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO5.5' AS `source_code`, 'APO05.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.1' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.2' AS `source_code`, 'EDM03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.2' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO6.3' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.3' AS `source_code`, 'APO01.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO6.4' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.4' AS `source_code`, 'APO01.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO6.5' AS `source_code`, 'APO01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.1' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.1' AS `source_code`, 'APO07.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO7.2' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.3' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.3' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO7.4' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.5' AS `source_code`, 'APO07.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.6' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.6' AS `source_code`, 'APO07.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO7.7' AS `source_code`, 'APO07.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.8' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.1' AS `source_code`, 'APO11.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.2' AS `source_code`, 'APO11.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.3' AS `source_code`, 'APO11.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.3' AS `source_code`, 'APO11.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO8.4' AS `source_code`, 'APO11.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.5' AS `source_code`, 'APO11.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.6' AS `source_code`, 'APO11.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.1' AS `source_code`, 'EDM03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.1' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO9.2' AS `source_code`, 'APO12.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.3' AS `source_code`, 'APO12.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.3' AS `source_code`, 'APO12.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO9.4' AS `source_code`, 'APO12.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.4' AS `source_code`, 'APO12.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO9.5' AS `source_code`, 'APO12.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.6' AS `source_code`, 'APO12.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.6' AS `source_code`, 'APO12.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO10.1' AS `source_code`, 'BAI01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.2' AS `source_code`, 'BAI01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.3' AS `source_code`, 'BAI01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.4' AS `source_code`, 'BAI01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.5' AS `source_code`, 'BAI01.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.6' AS `source_code`, 'BAI01.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.7' AS `source_code`, 'BAI01.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.8' AS `source_code`, 'BAI01.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.9' AS `source_code`, 'BAI01.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.10' AS `source_code`, 'BAI01.09' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.11' AS `source_code`, 'BAI01.11' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.12' AS `source_code`, 'BAI01.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.13' AS `source_code`, 'BAI01.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.13' AS `source_code`, 'BAI01.11' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO10.14' AS `source_code`, 'BAI01.13' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.1' AS `source_code`, 'BAI02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.2' AS `source_code`, 'BAI02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.3' AS `source_code`, 'BAI02.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.4' AS `source_code`, 'BAI02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.1' AS `source_code`, 'BAI03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.2' AS `source_code`, 'BAI03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.3' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI2.5' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.5' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI2.6' AS `source_code`, 'BAI03.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.7' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.7' AS `source_code`, 'BAI03.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI2.8' AS `source_code`, 'BAI03.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.9' AS `source_code`, 'BAI03.09' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.10' AS `source_code`, 'BAI03.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.1' AS `source_code`, 'BAI03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.2' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.2' AS `source_code`, 'DSS02.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI3.3' AS `source_code`, 'BAI03.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.4' AS `source_code`, 'BAI03.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.4' AS `source_code`, 'BAI03.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.1' AS `source_code`, 'BAI05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI5.1' AS `source_code`, 'BAI03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI5.2' AS `source_code`, 'APO10.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI5.2' AS `source_code`, 'APO10.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI5.3' AS `source_code`, 'APO10.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI5.4' AS `source_code`, 'APO10.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI6.2' AS `source_code`, 'BAI06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.3' AS `source_code`, 'BAI06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.4' AS `source_code`, 'BAI06.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.5' AS `source_code`, 'BAI06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.1' AS `source_code`, 'BAI05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.2' AS `source_code`, 'BAI07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.2' AS `source_code`, 'BAI07.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI7.3' AS `source_code`, 'BAI07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.4' AS `source_code`, 'BAI07.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.5' AS `source_code`, 'BAI07.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.6' AS `source_code`, 'BAI07.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.7' AS `source_code`, 'BAI07.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.8' AS `source_code`, 'BAI07.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.9' AS `source_code`, 'BAI07.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.05' AS `target_code`, 'practice' AS `target_level`, 5 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.06' AS `target_code`, 'practice' AS `target_level`, 6 AS `order_no`
    UNION ALL SELECT 'DS1.2' AS `source_code`, 'APO09.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.3' AS `source_code`, 'APO09.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.4' AS `source_code`, 'APO09.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.5' AS `source_code`, 'APO09.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.6' AS `source_code`, 'APO09.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.1' AS `source_code`, 'APO10.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.2' AS `source_code`, 'APO10.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.3' AS `source_code`, 'APO10.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.4' AS `source_code`, 'APO10.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.1' AS `source_code`, 'BAI04.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.2' AS `source_code`, 'BAI04.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.2' AS `source_code`, 'BAI04.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS3.3' AS `source_code`, 'BAI04.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.4' AS `source_code`, 'BAI04.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.5' AS `source_code`, 'BAI04.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.1' AS `source_code`, 'DSS04.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.1' AS `source_code`, 'DSS04.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS4.2' AS `source_code`, 'DSS04.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.3' AS `source_code`, 'DSS04.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.4' AS `source_code`, 'DSS04.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.4' AS `source_code`, 'DSS04.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS4.5' AS `source_code`, 'DSS04.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.6' AS `source_code`, 'DSS04.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.7' AS `source_code`, 'DSS04.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.8' AS `source_code`, 'DSS04.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.9' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.10' AS `source_code`, 'DSS04.09' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.1' AS `source_code`, 'APO13.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.1' AS `source_code`, 'APO13.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS5.2' AS `source_code`, 'APO13.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.3' AS `source_code`, 'DSS05.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.4' AS `source_code`, 'DSS05.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.5' AS `source_code`, 'DSS05.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.6' AS `source_code`, 'DSS02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.7' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.8' AS `source_code`, 'DSS05.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.9' AS `source_code`, 'DSS05.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.10' AS `source_code`, 'DSS05.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.11' AS `source_code`, 'DSS05.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.1' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.2' AS `source_code`, 'APO06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.3' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.4' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS7.1' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS7.2' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS7.3' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.2' AS `source_code`, 'DSS02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.2' AS `source_code`, 'DSS02.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS8.2' AS `source_code`, 'DSS02.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS8.3' AS `source_code`, 'DSS02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.4' AS `source_code`, 'DSS02.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.4' AS `source_code`, 'DSS02.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS8.5' AS `source_code`, 'DSS02.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'BAI10.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'BAI10.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'BAI10.04' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'DSS02.01' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'DS9.2' AS `source_code`, 'BAI10.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.3' AS `source_code`, 'BAI10.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.3' AS `source_code`, 'BAI10.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS9.3' AS `source_code`, 'DSS02.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS10.1' AS `source_code`, 'DSS03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS10.2' AS `source_code`, 'DSS03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS10.3' AS `source_code`, 'DSS03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS10.3' AS `source_code`, 'DSS03.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS10.4' AS `source_code`, 'DSS03.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.1' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.2' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.2' AS `source_code`, 'DSS06.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS11.3' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.4' AS `source_code`, 'DSS05.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.5' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.6' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.6' AS `source_code`, 'DSS05.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS11.6' AS `source_code`, 'DSS06.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS12.1' AS `source_code`, 'DSS01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.1' AS `source_code`, 'DSS01.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS12.1' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS12.2' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.3' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.4' AS `source_code`, 'DSS01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.5' AS `source_code`, 'DSS01.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.1' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.2' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.3' AS `source_code`, 'DSS01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.4' AS `source_code`, 'DSS05.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.5' AS `source_code`, 'BAI09.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.1' AS `source_code`, 'MEA01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.2' AS `source_code`, 'MEA01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.2' AS `source_code`, 'MEA01.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME1.3' AS `source_code`, 'MEA01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.4' AS `source_code`, 'MEA01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.5' AS `source_code`, 'MEA01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.6' AS `source_code`, 'MEA01.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.1' AS `source_code`, 'MEA02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.1' AS `source_code`, 'MEA02.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME2.2' AS `source_code`, 'MEA02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.3' AS `source_code`, 'MEA02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.4' AS `source_code`, 'MEA02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.5' AS `source_code`, 'MEA02.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.5' AS `source_code`, 'MEA02.07' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME2.5' AS `source_code`, 'MEA02.08' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'ME2.6' AS `source_code`, 'MEA02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.7' AS `source_code`, 'MEA02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.1' AS `source_code`, 'MEA03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.2' AS `source_code`, 'MEA03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.3' AS `source_code`, 'MEA03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.4' AS `source_code`, 'MEA03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.5' AS `source_code`, 'MEA03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.1' AS `source_code`, 'EDM01' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.3' AS `source_code`, 'EDM02' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.4' AS `source_code`, 'EDM04' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.5' AS `source_code`, 'EDM03' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM02.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM03.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM04.03' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.07' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.08' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
) AS `mapping_rows` ON `mapping_rows`.`source_code` = `source`.`code`
ON DUPLICATE KEY UPDATE
    `target_level` = VALUES(`target_level`),
    `order_no` = VALUES(`order_no`),
    `updated_at` = CURRENT_TIMESTAMP;

COMMIT;

-- Register the migration so 'php artisan migrate' does not run it again (it would re-seed from config)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.name, 99 FROM (SELECT '2026_11_06_000001_create_cobit4_cobit5_mapping_tables' AS name) m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = m.name);

-- --------------------------------------------------------------------------------------------
-- BAGIAN 3. Mapping practice COBIT 2019 -> COBIT 5 yang diedit user: trs_cobit2019_cobit5_mappings (tabel kosong)
-- File asal: database/sql/cobit2019_cobit5_mapping_cpanel.sql
-- --------------------------------------------------------------------------------------------

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

-- --------------------------------------------------------------------------------------------
-- BAGIAN 4. Katalog COBIT 5 (GAMO + practice): mst_cobit5_catalog_gamos + mst_cobit5_catalog_practices dan datanya
-- File asal: database/sql/cobit5_practice_catalog_cpanel.sql
-- --------------------------------------------------------------------------------------------

-- COBIT 5 GAMO and practice catalog for the GAMO mapping pages (page: Evolusi GAMO 4.1 -> 5 -> 2019, tab COBIT 5 -> COBIT 4.1).
-- Import once in phpMyAdmin. Creates the two tables if needed, then upserts every row (matched on code).
-- Until these tables exist the app falls back to config/cobit5-practice-catalog.php.
-- is_verified = 0: code shown in the supplied mapping source but not in the COBIT 5 framework (DSS08 / DSS08.02).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `mst_cobit5_catalog_gamos` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(10) NOT NULL,
    `domain_code` VARCHAR(10) NOT NULL,
    `domain_title` VARCHAR(100) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `mst_cobit5_catalog_gamos_code_unique` (`code`),
    KEY `mst_cobit5_catalog_gamos_domain_code_index` (`domain_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mst_cobit5_catalog_practices` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL,
    `gamo_code` VARCHAR(10) NOT NULL,
    `title` VARCHAR(500) NOT NULL,
    `review_note` TEXT NULL,
    `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `mst_cobit5_catalog_practices_code_unique` (`code`),
    KEY `mst_cobit5_catalog_practices_gamo_code_index` (`gamo_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mst_cobit5_catalog_gamos` (`code`, `domain_code`, `domain_title`, `title`, `is_verified`, `order_no`, `created_at`, `updated_at`) VALUES
('EDM01', 'EDM', 'Evaluate, Direct and Monitor', 'Ensure Governance Framework Setting and Maintenance', 1, 1, NOW(), NOW()),
('EDM02', 'EDM', 'Evaluate, Direct and Monitor', 'Ensure Benefits Delivery', 1, 2, NOW(), NOW()),
('EDM03', 'EDM', 'Evaluate, Direct and Monitor', 'Ensure Risk Optimisation', 1, 3, NOW(), NOW()),
('EDM04', 'EDM', 'Evaluate, Direct and Monitor', 'Ensure Resource Optimisation', 1, 4, NOW(), NOW()),
('EDM05', 'EDM', 'Evaluate, Direct and Monitor', 'Ensure Stakeholder Transparency', 1, 5, NOW(), NOW()),
('APO01', 'APO', 'Align, Plan and Organise', 'Manage the IT Management Framework', 1, 6, NOW(), NOW()),
('APO02', 'APO', 'Align, Plan and Organise', 'Manage Strategy', 1, 7, NOW(), NOW()),
('APO03', 'APO', 'Align, Plan and Organise', 'Manage Enterprise Architecture', 1, 8, NOW(), NOW()),
('APO04', 'APO', 'Align, Plan and Organise', 'Manage Innovation', 1, 9, NOW(), NOW()),
('APO05', 'APO', 'Align, Plan and Organise', 'Manage Portfolio', 1, 10, NOW(), NOW()),
('APO06', 'APO', 'Align, Plan and Organise', 'Manage Budget and Costs', 1, 11, NOW(), NOW()),
('APO07', 'APO', 'Align, Plan and Organise', 'Manage Human Resources', 1, 12, NOW(), NOW()),
('APO08', 'APO', 'Align, Plan and Organise', 'Manage Relationships', 1, 13, NOW(), NOW()),
('APO09', 'APO', 'Align, Plan and Organise', 'Manage Service Agreements', 1, 14, NOW(), NOW()),
('APO10', 'APO', 'Align, Plan and Organise', 'Manage Suppliers', 1, 15, NOW(), NOW()),
('APO11', 'APO', 'Align, Plan and Organise', 'Manage Quality', 1, 16, NOW(), NOW()),
('APO12', 'APO', 'Align, Plan and Organise', 'Manage Risk', 1, 17, NOW(), NOW()),
('APO13', 'APO', 'Align, Plan and Organise', 'Manage Security', 1, 18, NOW(), NOW()),
('BAI01', 'BAI', 'Build, Acquire and Implement', 'Manage Programmes and Projects', 1, 19, NOW(), NOW()),
('BAI02', 'BAI', 'Build, Acquire and Implement', 'Manage Requirements Definition', 1, 20, NOW(), NOW()),
('BAI03', 'BAI', 'Build, Acquire and Implement', 'Manage Solutions Identification and Build', 1, 21, NOW(), NOW()),
('BAI04', 'BAI', 'Build, Acquire and Implement', 'Manage Availability and Capacity', 1, 22, NOW(), NOW()),
('BAI05', 'BAI', 'Build, Acquire and Implement', 'Manage Organisational Change Enablement', 1, 23, NOW(), NOW()),
('BAI06', 'BAI', 'Build, Acquire and Implement', 'Manage Changes', 1, 24, NOW(), NOW()),
('BAI07', 'BAI', 'Build, Acquire and Implement', 'Manage Change Acceptance and Transitioning', 1, 25, NOW(), NOW()),
('BAI08', 'BAI', 'Build, Acquire and Implement', 'Manage Knowledge', 1, 26, NOW(), NOW()),
('BAI09', 'BAI', 'Build, Acquire and Implement', 'Manage Assets', 1, 27, NOW(), NOW()),
('BAI10', 'BAI', 'Build, Acquire and Implement', 'Manage Configuration', 1, 28, NOW(), NOW()),
('DSS01', 'DSS', 'Deliver, Service and Support', 'Manage Operations', 1, 29, NOW(), NOW()),
('DSS02', 'DSS', 'Deliver, Service and Support', 'Manage Service Requests and Incidents', 1, 30, NOW(), NOW()),
('DSS03', 'DSS', 'Deliver, Service and Support', 'Manage Problems', 1, 31, NOW(), NOW()),
('DSS04', 'DSS', 'Deliver, Service and Support', 'Manage Continuity', 1, 32, NOW(), NOW()),
('DSS05', 'DSS', 'Deliver, Service and Support', 'Manage Security Services', 1, 33, NOW(), NOW()),
('DSS06', 'DSS', 'Deliver, Service and Support', 'Manage Business Process Controls', 1, 34, NOW(), NOW()),
('MEA01', 'MEA', 'Monitor, Evaluate and Assess', 'Monitor, Evaluate and Assess Performance and Conformance', 1, 35, NOW(), NOW()),
('MEA02', 'MEA', 'Monitor, Evaluate and Assess', 'Monitor, Evaluate and Assess the System of Internal Control', 1, 36, NOW(), NOW()),
('MEA03', 'MEA', 'Monitor, Evaluate and Assess', 'Monitor, Evaluate and Assess Compliance with External Requirements', 1, 37, NOW(), NOW()),
('DSS08', 'DSS', 'Deliver, Service and Support', 'Code shown in the supplied source; GAMO not verified.', 0, 38, NOW(), NOW())
ON DUPLICATE KEY UPDATE `domain_code`=VALUES(`domain_code`), `domain_title`=VALUES(`domain_title`), `title`=VALUES(`title`), `is_verified`=VALUES(`is_verified`), `order_no`=VALUES(`order_no`), `updated_at`=NOW();

INSERT INTO `mst_cobit5_catalog_practices` (`code`, `gamo_code`, `title`, `review_note`, `is_verified`, `order_no`, `created_at`, `updated_at`) VALUES
('EDM01.01', 'EDM01', 'Evaluate the governance system.', NULL, 1, 1, NOW(), NOW()),
('EDM01.02', 'EDM01', 'Direct the governance system.', NULL, 1, 2, NOW(), NOW()),
('EDM01.03', 'EDM01', 'Monitor the governance system.', NULL, 1, 3, NOW(), NOW()),
('EDM02.01', 'EDM02', 'Evaluate value optimisation.', NULL, 1, 4, NOW(), NOW()),
('EDM02.02', 'EDM02', 'Direct value optimisation.', NULL, 1, 5, NOW(), NOW()),
('EDM02.03', 'EDM02', 'Monitor value optimisation.', NULL, 1, 6, NOW(), NOW()),
('EDM03.01', 'EDM03', 'Evaluate risk management.', NULL, 1, 7, NOW(), NOW()),
('EDM03.02', 'EDM03', 'Direct risk management.', NULL, 1, 8, NOW(), NOW()),
('EDM03.03', 'EDM03', 'Monitor risk management.', NULL, 1, 9, NOW(), NOW()),
('EDM04.01', 'EDM04', 'Evaluate resource management.', NULL, 1, 10, NOW(), NOW()),
('EDM04.02', 'EDM04', 'Direct resource management.', NULL, 1, 11, NOW(), NOW()),
('EDM04.03', 'EDM04', 'Monitor resource management.', NULL, 1, 12, NOW(), NOW()),
('EDM05.01', 'EDM05', 'Evaluate stakeholder reporting requirements.', NULL, 1, 13, NOW(), NOW()),
('EDM05.02', 'EDM05', 'Direct stakeholder communication and reporting.', NULL, 1, 14, NOW(), NOW()),
('EDM05.03', 'EDM05', 'Monitor stakeholder communication.', NULL, 1, 15, NOW(), NOW()),
('APO01.01', 'APO01', 'Define the organisational structure.', NULL, 1, 16, NOW(), NOW()),
('APO01.02', 'APO01', 'Establish roles and responsibilities.', NULL, 1, 17, NOW(), NOW()),
('APO01.03', 'APO01', 'Maintain the enablers of the management system.', NULL, 1, 18, NOW(), NOW()),
('APO01.04', 'APO01', 'Communicate management objectives and direction.', NULL, 1, 19, NOW(), NOW()),
('APO01.05', 'APO01', 'Optimise the placement of the IT function.', NULL, 1, 20, NOW(), NOW()),
('APO01.06', 'APO01', 'Define information (data) and system ownership.', NULL, 1, 21, NOW(), NOW()),
('APO01.07', 'APO01', 'Manage continual improvement of processes.', NULL, 1, 22, NOW(), NOW()),
('APO01.08', 'APO01', 'Maintain compliance with policies and procedures.', NULL, 1, 23, NOW(), NOW()),
('APO02.01', 'APO02', 'Understand enterprise direction.', NULL, 1, 24, NOW(), NOW()),
('APO02.02', 'APO02', 'Assess the current environment, capabilities and performance.', NULL, 1, 25, NOW(), NOW()),
('APO02.03', 'APO02', 'Define the target IT capabilities.', NULL, 1, 26, NOW(), NOW()),
('APO02.04', 'APO02', 'Conduct a gap analysis.', NULL, 1, 27, NOW(), NOW()),
('APO02.05', 'APO02', 'Define the strategic plan and road map.', NULL, 1, 28, NOW(), NOW()),
('APO02.06', 'APO02', 'Communicate the IT strategy and direction.', NULL, 1, 29, NOW(), NOW()),
('APO03.01', 'APO03', 'Develop the enterprise architecture vision.', NULL, 1, 30, NOW(), NOW()),
('APO03.02', 'APO03', 'Define reference architecture.', NULL, 1, 31, NOW(), NOW()),
('APO03.03', 'APO03', 'Select opportunities and solutions.', NULL, 1, 32, NOW(), NOW()),
('APO03.04', 'APO03', 'Define architecture implementation.', NULL, 1, 33, NOW(), NOW()),
('APO03.05', 'APO03', 'Provide enterprise architecture services.', NULL, 1, 34, NOW(), NOW()),
('APO04.01', 'APO04', 'Create an environment conducive to innovation.', NULL, 1, 35, NOW(), NOW()),
('APO04.02', 'APO04', 'Maintain an understanding of the enterprise environment.', NULL, 1, 36, NOW(), NOW()),
('APO04.03', 'APO04', 'Monitor and scan the technology environment.', NULL, 1, 37, NOW(), NOW()),
('APO04.04', 'APO04', 'Assess the potential of emerging technologies and innovation ideas.', NULL, 1, 38, NOW(), NOW()),
('APO04.05', 'APO04', 'Recommend appropriate further initiatives.', NULL, 1, 39, NOW(), NOW()),
('APO04.06', 'APO04', 'Monitor the implementation and use of innovation.', NULL, 1, 40, NOW(), NOW()),
('APO05.01', 'APO05', 'Establish the target investment mix.', NULL, 1, 41, NOW(), NOW()),
('APO05.02', 'APO05', 'Determine the availability and sources of funds.', NULL, 1, 42, NOW(), NOW()),
('APO05.03', 'APO05', 'Evaluate and select programmes to fund.', NULL, 1, 43, NOW(), NOW()),
('APO05.04', 'APO05', 'Monitor, optimise and report on investment portfolio performance.', NULL, 1, 44, NOW(), NOW()),
('APO05.05', 'APO05', 'Maintain portfolios.', NULL, 1, 45, NOW(), NOW()),
('APO05.06', 'APO05', 'Manage benefits achievement.', NULL, 1, 46, NOW(), NOW()),
('APO06.01', 'APO06', 'Manage finance and accounting.', NULL, 1, 47, NOW(), NOW()),
('APO06.02', 'APO06', 'Prioritise resource allocation.', NULL, 1, 48, NOW(), NOW()),
('APO06.03', 'APO06', 'Create and maintain budgets.', NULL, 1, 49, NOW(), NOW()),
('APO06.04', 'APO06', 'Model and allocate costs.', NULL, 1, 50, NOW(), NOW()),
('APO06.05', 'APO06', 'Manage costs.', NULL, 1, 51, NOW(), NOW()),
('APO07.01', 'APO07', 'Maintain adequate and appropriate staffing.', NULL, 1, 52, NOW(), NOW()),
('APO07.02', 'APO07', 'Identify key IT personnel.', NULL, 1, 53, NOW(), NOW()),
('APO07.03', 'APO07', 'Maintain the skills and competencies of personnel.', NULL, 1, 54, NOW(), NOW()),
('APO07.04', 'APO07', 'Evaluate employee job performance.', NULL, 1, 55, NOW(), NOW()),
('APO07.05', 'APO07', 'Plan and track the usage of IT and business human resources.', NULL, 1, 56, NOW(), NOW()),
('APO07.06', 'APO07', 'Manage contract staff.', NULL, 1, 57, NOW(), NOW()),
('APO08.01', 'APO08', 'Understand business expectations.', NULL, 1, 58, NOW(), NOW()),
('APO08.02', 'APO08', 'Identify opportunities, risk and constraints for IT to enhance the business.', NULL, 1, 59, NOW(), NOW()),
('APO08.03', 'APO08', 'Manage the business relationship.', NULL, 1, 60, NOW(), NOW()),
('APO08.04', 'APO08', 'Co-ordinate and communicate.', NULL, 1, 61, NOW(), NOW()),
('APO08.05', 'APO08', 'Provide input to the continual improvement of services.', NULL, 1, 62, NOW(), NOW()),
('APO09.01', 'APO09', 'Identify IT services.', NULL, 1, 63, NOW(), NOW()),
('APO09.02', 'APO09', 'Catalogue IT-enabled services.', NULL, 1, 64, NOW(), NOW()),
('APO09.03', 'APO09', 'Define and prepare service agreements.', NULL, 1, 65, NOW(), NOW()),
('APO09.04', 'APO09', 'Monitor and report service levels.', NULL, 1, 66, NOW(), NOW()),
('APO09.05', 'APO09', 'Review service agreements and contracts.', NULL, 1, 67, NOW(), NOW()),
('APO09.06', 'APO09', 'Code shown in the supplied source; verify against COBIT 5.', 'APO09.06 is shown in the supplied mapping image but is not listed as a COBIT 5 APO09 practice.', 1, 68, NOW(), NOW()),
('APO10.01', 'APO10', 'Identify and evaluate supplier relationships and contracts.', NULL, 1, 69, NOW(), NOW()),
('APO10.02', 'APO10', 'Select suppliers.', NULL, 1, 70, NOW(), NOW()),
('APO10.03', 'APO10', 'Manage supplier relationships and contracts.', NULL, 1, 71, NOW(), NOW()),
('APO10.04', 'APO10', 'Manage supplier risk.', NULL, 1, 72, NOW(), NOW()),
('APO10.05', 'APO10', 'Monitor supplier performance and compliance.', NULL, 1, 73, NOW(), NOW()),
('APO11.01', 'APO11', 'Establish a quality management system (QMS).', NULL, 1, 74, NOW(), NOW()),
('APO11.02', 'APO11', 'Define and manage quality standards, practices and procedures.', NULL, 1, 75, NOW(), NOW()),
('APO11.03', 'APO11', 'Focus quality management on customers.', NULL, 1, 76, NOW(), NOW()),
('APO11.04', 'APO11', 'Perform quality monitoring, control and reviews.', NULL, 1, 77, NOW(), NOW()),
('APO11.05', 'APO11', 'Integrate quality management into solutions for development and service delivery.', NULL, 1, 78, NOW(), NOW()),
('APO11.06', 'APO11', 'Maintain continuous improvement.', NULL, 1, 79, NOW(), NOW()),
('APO12.01', 'APO12', 'Collect data.', NULL, 1, 80, NOW(), NOW()),
('APO12.02', 'APO12', 'Analyse risk.', NULL, 1, 81, NOW(), NOW()),
('APO12.03', 'APO12', 'Maintain a risk profile.', NULL, 1, 82, NOW(), NOW()),
('APO12.04', 'APO12', 'Articulate risk.', NULL, 1, 83, NOW(), NOW()),
('APO12.05', 'APO12', 'Define a risk management action portfolio.', NULL, 1, 84, NOW(), NOW()),
('APO12.06', 'APO12', 'Respond to risk.', NULL, 1, 85, NOW(), NOW()),
('APO13.01', 'APO13', 'Establish and maintain an ISMS.', NULL, 1, 86, NOW(), NOW()),
('APO13.02', 'APO13', 'Define and manage an information security risk treatment plan.', NULL, 1, 87, NOW(), NOW()),
('APO13.03', 'APO13', 'Monitor and review the ISMS.', NULL, 1, 88, NOW(), NOW()),
('BAI01.01', 'BAI01', 'Maintain a standard approach for programme and project management.', NULL, 1, 89, NOW(), NOW()),
('BAI01.02', 'BAI01', 'Initiate a programme.', NULL, 1, 90, NOW(), NOW()),
('BAI01.03', 'BAI01', 'Manage stakeholder engagement.', NULL, 1, 91, NOW(), NOW()),
('BAI01.04', 'BAI01', 'Develop and maintain the programme plan.', NULL, 1, 92, NOW(), NOW()),
('BAI01.05', 'BAI01', 'Launch and execute the programme.', NULL, 1, 93, NOW(), NOW()),
('BAI01.06', 'BAI01', 'Monitor, control and report on the programme outcomes.', NULL, 1, 94, NOW(), NOW()),
('BAI01.07', 'BAI01', 'Start up and initiate projects within a programme.', NULL, 1, 95, NOW(), NOW()),
('BAI01.08', 'BAI01', 'Plan projects.', NULL, 1, 96, NOW(), NOW()),
('BAI01.09', 'BAI01', 'Manage programme and project quality.', NULL, 1, 97, NOW(), NOW()),
('BAI01.10', 'BAI01', 'Manage programme and project risk.', NULL, 1, 98, NOW(), NOW()),
('BAI01.11', 'BAI01', 'Monitor and control projects.', NULL, 1, 99, NOW(), NOW()),
('BAI01.12', 'BAI01', 'Manage project resources and work packages.', NULL, 1, 100, NOW(), NOW()),
('BAI01.13', 'BAI01', 'Close a project or iteration.', NULL, 1, 101, NOW(), NOW()),
('BAI01.14', 'BAI01', 'Close a programme.', NULL, 1, 102, NOW(), NOW()),
('BAI02.01', 'BAI02', 'Define and maintain business functional and technical requirements.', NULL, 1, 103, NOW(), NOW()),
('BAI02.02', 'BAI02', 'Perform a feasibility study and formulate alternative solutions.', NULL, 1, 104, NOW(), NOW()),
('BAI02.03', 'BAI02', 'Manage requirements risk.', NULL, 1, 105, NOW(), NOW()),
('BAI02.04', 'BAI02', 'Obtain approval of requirements and solutions.', NULL, 1, 106, NOW(), NOW()),
('BAI03.01', 'BAI03', 'Design high-level solutions.', NULL, 1, 107, NOW(), NOW()),
('BAI03.02', 'BAI03', 'Design detailed solution components.', NULL, 1, 108, NOW(), NOW()),
('BAI03.03', 'BAI03', 'Develop solution components.', NULL, 1, 109, NOW(), NOW()),
('BAI03.04', 'BAI03', 'Procure solution components.', NULL, 1, 110, NOW(), NOW()),
('BAI03.05', 'BAI03', 'Build solutions.', NULL, 1, 111, NOW(), NOW()),
('BAI03.06', 'BAI03', 'Perform quality assurance.', NULL, 1, 112, NOW(), NOW()),
('BAI03.07', 'BAI03', 'Prepare for solution testing.', NULL, 1, 113, NOW(), NOW()),
('BAI03.08', 'BAI03', 'Execute solution testing.', NULL, 1, 114, NOW(), NOW()),
('BAI03.09', 'BAI03', 'Manage changes to requirements.', NULL, 1, 115, NOW(), NOW()),
('BAI03.10', 'BAI03', 'Maintain solutions.', NULL, 1, 116, NOW(), NOW()),
('BAI03.11', 'BAI03', 'Define IT services and maintain the service portfolio.', NULL, 1, 117, NOW(), NOW()),
('BAI04.01', 'BAI04', 'Assess current availability, performance and capacity and create a baseline.', NULL, 1, 118, NOW(), NOW()),
('BAI04.02', 'BAI04', 'Assess business impact.', NULL, 1, 119, NOW(), NOW()),
('BAI04.03', 'BAI04', 'Plan for new or changed service requirements.', NULL, 1, 120, NOW(), NOW()),
('BAI04.04', 'BAI04', 'Monitor and review availability and capacity.', NULL, 1, 121, NOW(), NOW()),
('BAI04.05', 'BAI04', 'Investigate and address availability, performance and capacity issues.', NULL, 1, 122, NOW(), NOW()),
('BAI05.01', 'BAI05', 'Establish the desire to change.', NULL, 1, 123, NOW(), NOW()),
('BAI05.02', 'BAI05', 'Form an effective implementation team.', NULL, 1, 124, NOW(), NOW()),
('BAI05.03', 'BAI05', 'Communicate the desired vision.', NULL, 1, 125, NOW(), NOW()),
('BAI05.04', 'BAI05', 'Empower role players and identify short-term wins.', NULL, 1, 126, NOW(), NOW()),
('BAI05.05', 'BAI05', 'Enable operation and use.', NULL, 1, 127, NOW(), NOW()),
('BAI05.06', 'BAI05', 'Embed new approaches.', NULL, 1, 128, NOW(), NOW()),
('BAI05.07', 'BAI05', 'Sustain changes.', NULL, 1, 129, NOW(), NOW()),
('BAI06.01', 'BAI06', 'Evaluate, prioritise and authorise change requests.', NULL, 1, 130, NOW(), NOW()),
('BAI06.02', 'BAI06', 'Manage emergency changes.', NULL, 1, 131, NOW(), NOW()),
('BAI06.03', 'BAI06', 'Track and report change status.', NULL, 1, 132, NOW(), NOW()),
('BAI06.04', 'BAI06', 'Close and document the changes.', NULL, 1, 133, NOW(), NOW()),
('BAI07.01', 'BAI07', 'Establish an implementation plan.', NULL, 1, 134, NOW(), NOW()),
('BAI07.02', 'BAI07', 'Plan business process, system and data conversion.', NULL, 1, 135, NOW(), NOW()),
('BAI07.03', 'BAI07', 'Plan acceptance tests.', NULL, 1, 136, NOW(), NOW()),
('BAI07.04', 'BAI07', 'Establish a test environment.', NULL, 1, 137, NOW(), NOW()),
('BAI07.05', 'BAI07', 'Perform acceptance tests.', NULL, 1, 138, NOW(), NOW()),
('BAI07.06', 'BAI07', 'Promote to production and manage releases.', NULL, 1, 139, NOW(), NOW()),
('BAI07.07', 'BAI07', 'Provide early production support.', NULL, 1, 140, NOW(), NOW()),
('BAI07.08', 'BAI07', 'Perform a post-implementation review.', NULL, 1, 141, NOW(), NOW()),
('BAI08.01', 'BAI08', 'Nurture and facilitate a knowledge-sharing culture.', NULL, 1, 142, NOW(), NOW()),
('BAI08.02', 'BAI08', 'Identify and classify sources of information.', NULL, 1, 143, NOW(), NOW()),
('BAI08.03', 'BAI08', 'Organise and contextualise information into knowledge.', NULL, 1, 144, NOW(), NOW()),
('BAI08.04', 'BAI08', 'Use and share knowledge.', NULL, 1, 145, NOW(), NOW()),
('BAI08.05', 'BAI08', 'Evaluate and retire information.', NULL, 1, 146, NOW(), NOW()),
('BAI09.01', 'BAI09', 'Identify and record current assets.', NULL, 1, 147, NOW(), NOW()),
('BAI09.02', 'BAI09', 'Manage critical assets.', NULL, 1, 148, NOW(), NOW()),
('BAI09.03', 'BAI09', 'Manage the asset life cycle.', NULL, 1, 149, NOW(), NOW()),
('BAI09.04', 'BAI09', 'Optimise asset costs.', NULL, 1, 150, NOW(), NOW()),
('BAI09.05', 'BAI09', 'Manage licences.', NULL, 1, 151, NOW(), NOW()),
('BAI10.01', 'BAI10', 'Establish and maintain a configuration model.', NULL, 1, 152, NOW(), NOW()),
('BAI10.02', 'BAI10', 'Establish and maintain a configuration repository and baseline.', NULL, 1, 153, NOW(), NOW()),
('BAI10.03', 'BAI10', 'Maintain and control configuration items.', NULL, 1, 154, NOW(), NOW()),
('BAI10.04', 'BAI10', 'Produce status and configuration reports.', NULL, 1, 155, NOW(), NOW()),
('BAI10.05', 'BAI10', 'Verify and review integrity of the configuration repository.', NULL, 1, 156, NOW(), NOW()),
('DSS01.01', 'DSS01', 'Perform operational procedures.', NULL, 1, 157, NOW(), NOW()),
('DSS01.02', 'DSS01', 'Manage outsourced IT services.', NULL, 1, 158, NOW(), NOW()),
('DSS01.03', 'DSS01', 'Monitor IT infrastructure.', NULL, 1, 159, NOW(), NOW()),
('DSS01.04', 'DSS01', 'Manage the environment.', NULL, 1, 160, NOW(), NOW()),
('DSS01.05', 'DSS01', 'Manage facilities.', NULL, 1, 161, NOW(), NOW()),
('DSS02.01', 'DSS02', 'Define incident and service request classification schemes.', NULL, 1, 162, NOW(), NOW()),
('DSS02.02', 'DSS02', 'Record, classify and prioritise requests and incidents.', NULL, 1, 163, NOW(), NOW()),
('DSS02.03', 'DSS02', 'Verify, approve and fulfil service requests.', NULL, 1, 164, NOW(), NOW()),
('DSS02.04', 'DSS02', 'Investigate, diagnose and allocate incidents.', NULL, 1, 165, NOW(), NOW()),
('DSS02.05', 'DSS02', 'Resolve and recover from incidents.', NULL, 1, 166, NOW(), NOW()),
('DSS02.06', 'DSS02', 'Close service requests and incidents.', NULL, 1, 167, NOW(), NOW()),
('DSS02.07', 'DSS02', 'Track status and produce reports.', NULL, 1, 168, NOW(), NOW()),
('DSS03.01', 'DSS03', 'Identify and classify problems.', NULL, 1, 169, NOW(), NOW()),
('DSS03.02', 'DSS03', 'Investigate and diagnose problems.', NULL, 1, 170, NOW(), NOW()),
('DSS03.03', 'DSS03', 'Raise known errors.', NULL, 1, 171, NOW(), NOW()),
('DSS03.04', 'DSS03', 'Resolve and close problems.', NULL, 1, 172, NOW(), NOW()),
('DSS03.05', 'DSS03', 'Perform proactive problem management.', NULL, 1, 173, NOW(), NOW()),
('DSS04.01', 'DSS04', 'Define the business continuity policy, objectives and scope.', NULL, 1, 174, NOW(), NOW()),
('DSS04.02', 'DSS04', 'Maintain a continuity strategy.', NULL, 1, 175, NOW(), NOW()),
('DSS04.03', 'DSS04', 'Develop and implement a business continuity response.', NULL, 1, 176, NOW(), NOW()),
('DSS04.04', 'DSS04', 'Exercise, test and review the BCP.', NULL, 1, 177, NOW(), NOW()),
('DSS04.05', 'DSS04', 'Review, maintain and improve the continuity plan.', NULL, 1, 178, NOW(), NOW()),
('DSS04.06', 'DSS04', 'Conduct continuity plan training.', NULL, 1, 179, NOW(), NOW()),
('DSS04.07', 'DSS04', 'Manage backup arrangements.', NULL, 1, 180, NOW(), NOW()),
('DSS04.08', 'DSS04', 'Conduct post-resumption review.', NULL, 1, 181, NOW(), NOW()),
('DSS04.09', 'DSS04', 'Code shown in the supplied source; verify against COBIT 5.', 'DSS04.09 is shown in the supplied mapping image but is not listed as a COBIT 5 DSS04 practice.', 1, 182, NOW(), NOW()),
('DSS05.01', 'DSS05', 'Protect against malware.', NULL, 1, 183, NOW(), NOW()),
('DSS05.02', 'DSS05', 'Manage network and connectivity security.', NULL, 1, 184, NOW(), NOW()),
('DSS05.03', 'DSS05', 'Manage endpoint security.', NULL, 1, 185, NOW(), NOW()),
('DSS05.04', 'DSS05', 'Manage user identity and logical access.', NULL, 1, 186, NOW(), NOW()),
('DSS05.05', 'DSS05', 'Manage physical access to IT assets.', NULL, 1, 187, NOW(), NOW()),
('DSS05.06', 'DSS05', 'Manage sensitive documents and output devices.', NULL, 1, 188, NOW(), NOW()),
('DSS05.07', 'DSS05', 'Monitor the infrastructure for security-related events.', NULL, 1, 189, NOW(), NOW()),
('DSS05.08', 'DSS05', 'Code shown in the supplied source; verify against COBIT 5.', 'DSS05.08 is shown in the supplied mapping image but is not listed as a COBIT 5 DSS05 practice.', 1, 190, NOW(), NOW()),
('DSS06.01', 'DSS06', 'Align control activities embedded in business processes with enterprise objectives.', NULL, 1, 191, NOW(), NOW()),
('DSS06.02', 'DSS06', 'Control the processing of information.', NULL, 1, 192, NOW(), NOW()),
('DSS06.03', 'DSS06', 'Manage roles, responsibilities, access privileges and levels of authority.', NULL, 1, 193, NOW(), NOW()),
('DSS06.04', 'DSS06', 'Manage errors and exceptions.', NULL, 1, 194, NOW(), NOW()),
('DSS06.05', 'DSS06', 'Ensure traceability of information events.', NULL, 1, 195, NOW(), NOW()),
('DSS06.06', 'DSS06', 'Secure information assets.', NULL, 1, 196, NOW(), NOW()),
('MEA01.01', 'MEA01', 'Establish a monitoring approach.', NULL, 1, 197, NOW(), NOW()),
('MEA01.02', 'MEA01', 'Set performance and conformance targets.', NULL, 1, 198, NOW(), NOW()),
('MEA01.03', 'MEA01', 'Collect and process performance and conformance data.', NULL, 1, 199, NOW(), NOW()),
('MEA01.04', 'MEA01', 'Analyse and report performance.', NULL, 1, 200, NOW(), NOW()),
('MEA01.05', 'MEA01', 'Ensure the implementation of corrective actions.', NULL, 1, 201, NOW(), NOW()),
('MEA02.01', 'MEA02', 'Monitor internal controls.', NULL, 1, 202, NOW(), NOW()),
('MEA02.02', 'MEA02', 'Review business process controls effectiveness.', NULL, 1, 203, NOW(), NOW()),
('MEA02.03', 'MEA02', 'Perform control self-assessments.', NULL, 1, 204, NOW(), NOW()),
('MEA02.04', 'MEA02', 'Identify and report control deficiencies.', NULL, 1, 205, NOW(), NOW()),
('MEA02.05', 'MEA02', 'Ensure that assurance providers are independent and qualified.', NULL, 1, 206, NOW(), NOW()),
('MEA02.06', 'MEA02', 'Plan assurance initiatives.', NULL, 1, 207, NOW(), NOW()),
('MEA02.07', 'MEA02', 'Scope assurance initiatives.', NULL, 1, 208, NOW(), NOW()),
('MEA02.08', 'MEA02', 'Execute assurance initiatives.', NULL, 1, 209, NOW(), NOW()),
('MEA03.01', 'MEA03', 'Identify external compliance requirements.', NULL, 1, 210, NOW(), NOW()),
('MEA03.02', 'MEA03', 'Optimise response to external requirements.', NULL, 1, 211, NOW(), NOW()),
('MEA03.03', 'MEA03', 'Confirm external compliance.', NULL, 1, 212, NOW(), NOW()),
('MEA03.04', 'MEA03', 'Obtain assurance of external compliance.', NULL, 1, 213, NOW(), NOW()),
('DSS08.02', 'DSS08', 'Code shown in the supplied source; verify against COBIT 5.', 'DSS08.02 is shown in the supplied mapping image but DSS08 is not listed among COBIT 5 DSS processes.', 0, 214, NOW(), NOW())
ON DUPLICATE KEY UPDATE `gamo_code`=VALUES(`gamo_code`), `title`=VALUES(`title`), `review_note`=VALUES(`review_note`), `is_verified`=VALUES(`is_verified`), `order_no`=VALUES(`order_no`), `updated_at`=NOW();

-- Register the migration so 'php artisan migrate' does not try to create the tables again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.name, 99 FROM (SELECT '2026_11_16_000002_create_cobit5_practice_catalog_tables' AS name) m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = m.name);

