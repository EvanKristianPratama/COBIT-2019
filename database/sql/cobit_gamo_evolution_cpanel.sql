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
