-- COBIT 4.1 - Level 1 Output Work Products (Figure 17).
-- Sumber: gambar dari pengguna. Dua gambar awal memuat baris yang sama.
-- Cakupan: 24 WP unik, PO1-WP1 sampai PO5-WP3 yang terlihat pada gambar.
-- Tiga kolom dokumen: wp_id (WP ID), wp (WP), description (Description).
-- order_no hanya untuk menjaga urutan tampilan seperti dokumen.
-- Spasi pada WP ID dan paragraf Scope note dipertahankan dari gambar.
--
-- Pilih database aplikasi terlebih dahulu di phpMyAdmin/MySQL sebelum impor.
-- Prasyarat: mst_cobit4_processes sudah tersedia, id BIGINT UNSIGNED.
-- process_id mengarah ke master proses PO/AI/DS/ME yang SUDAH ADA;
-- tabel ini tidak membuat master proses baru atau mengubah isi master proses.
-- Relasi dibentuk berdasarkan code (PO1, PO2, dst.), tanpa hardcode ID numerik.
-- Jika ada beberapa focus area COBIT 4.1 dengan kode sama, masing-masing ditautkan.
-- Nama tabel mengandung titik: SELALU apit seluruh nama dengan backtick.
-- Dapat diimpor ulang: baris WP yang sama diperbarui, relasi tidak digandakan.
-- CREATE TABLE IF NOT EXISTS tidak memperbaiki struktur tabel lama yang berbeda.
-- File ini belum dijalankan ke database aplikasi dan tidak menambahkan UI tab 5.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `mst_wp_4.1` (
    `wp_id` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL,
    `wp` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`wp_id`),
    KEY `mst_wp_41_order_index` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trs_wp_to_obj_4.1` (
    `process_id` BIGINT UNSIGNED NOT NULL,
    `wp_id` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`process_id`, `wp_id`),
    KEY `trs_wp_to_obj_41_wp_index` (`wp_id`),
    CONSTRAINT `trs_wp_to_obj_41_process_fk`
        FOREIGN KEY (`process_id`) REFERENCES `mst_cobit4_processes` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `trs_wp_to_obj_41_wp_fk`
        FOREIGN KEY (`wp_id`) REFERENCES `mst_wp_4.1` (`wp_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

INSERT INTO `mst_wp_4.1` (`wp_id`, `wp`, `description`, `order_no`, `created_at`, `updated_at`)
VALUES
    ('PO1- WP1', 'Strategic IT plan',
     'A long-term plan, i.e., three- to five-year horizon, in which business and IT management co-operatively describe how IT resources will contribute to the enterprise’s strategic objectives (goals)',
     1, NOW(), NOW()),
    ('PO1- WP2', 'Tactical IT plan',
     'A medium-term plan, i.e., six-month to 18-month horizon, that translates the IT strategic plan direction into required initiatives, resource requirements and ways in which resources and benefits will be monitored and managed',
     2, NOW(), NOW()),
    ('PO1- WP3', 'IT project portfolio',
     'A grouping of IT projects managed and monitored to optimise business value',
     3, NOW(), NOW()),
    ('PO1- WP4', 'IT service portfolio',
     'A grouping of IT services managed and monitored to optimise business value',
     4, NOW(), NOW()),
    ('PO1-WP5', 'IT sourcing strategy',
     'Where to buy and what to buy, considering demand and supply situation while minimising risk and costs',
     5, NOW(), NOW()),
    ('PO1-WP6', 'IT acquisition strategy',
     'An overall strategy for acquisition of IT services based on business objectives.  The strategy should be based on an understanding of future purchasing, market analysis and strategic impact of purchasing decisions.',
     6, NOW(), NOW()),
    ('PO2-WP1', 'Data classification scheme',
     'The assignment of a level of sensitivity to data (or information) that results in the specification of controls for each level of classification. Levels of sensitivity of data are assigned according to pre-defined categories as data are created, amended, enhanced, stored or transmitted. The classification level is an indication of the value or importance of the data to the organisation.',
     7, NOW(), NOW()),
    ('PO2-WP2', 'Optimised business systems plan',
     'An actionable road map that aligns technology investments to business strategy. It is optimised for the management of the data.',
     8, NOW(), NOW()),
    ('PO2-WP3', 'Data dictionary',
     CONCAT('A database that contains the name, type, range of values, source and authorisation for access for each data element in a database. It also indicates which application programs use that data so that when a data structure is contemplated, a list of the affected programs can be generated.',
            CHAR(10), CHAR(10),
            'Scope note:  The data dictionary may be a stand-alone information system used for management or documentation purposes, or it may control the operation of a database.'),
     9, NOW(), NOW()),
    ('PO2-WP4', 'Information architecture',
     'A component of IT architecture (together with applications and technology)',
     10, NOW(), NOW()),
    ('PO2-WP5', 'Assigned data classifications',
     'An enterprise scheme for classifying data by factors such as criticality, sensitivity and ownership',
     11, NOW(), NOW()),
    ('PO2-WP6', 'Classification procedures and tools',
     'Procedures and tools to enable data to be classified, created in line with organisation policies',
     12, NOW(), NOW()),
    ('PO3-WP1', 'Technology opportunities',
     'Identification of technology opportunities for the business',
     13, NOW(), NOW()),
    ('PO3-WP2', 'Technology standards',
     'Organisational standards for infrastructure products and practices together with guidance on the selection of technology',
     14, NOW(), NOW()),
    ('PO3-WP3', 'Regular ‘state of technology’ updates',
     'An assessment of current and future technology trends and the potential opportunities and risks to the organisation',
     15, NOW(), NOW()),
    ('PO3-WP4', 'Technology infrastructure plan',
     'A plan for the technology, human resources and facilities that enables the current and future processing and use of applications',
     16, NOW(), NOW()),
    ('PO3-WP5', 'Infrastructure requirements',
     CONCAT('The required set of hardware, software and facilities that integrates an organisation’s IT assets',
            CHAR(10), CHAR(10),
            'Scope note:  Specifically, this includes the equipment (including servers, routers, switches and cabling), software, services and products used in storing, processing, transmitting and displaying all forms of information for the organisation’s users.'),
     17, NOW(), NOW()),
    ('PO4-WP1', 'IT process framework',
     'A process framework that includes IT process structure and relationships (e.g., to manage process gaps and overlaps), ownership, maturity, performance measurement, improvement, compliance, quality targets, and plans to achieve them. It should provide integration amongst the processes that are specific to IT, enterprise portfolio management, business processes and business change processes. The IT process framework should be integrated into a quality management system (QMS) and the internal control framework.',
     18, NOW(), NOW()),
    ('PO4-WP2', 'Documented system owners',
     'An application system owner is the individual who has been assigned ultimate responsibility for a system.',
     19, NOW(), NOW()),
    ('PO4-WP3', 'IT organisation and relationships',
     'A specification of internal and external IT organisational structure and responsibilities',
     20, NOW(), NOW()),
    ('PO4-WP4', 'Documented roles and responsibilities',
     'Roles and responsibilities for IT personnel and end users that delineate between IT personnel and end-user authority, responsibilities and accountability for meeting the organisation’s needs',
     21, NOW(), NOW()),
    ('PO5-WP1', 'Cost-benefit reports',
     'A report weighing the total expected costs against the total expected benefits of one or more actions to choose the best or most profitable option',
     22, NOW(), NOW()),
    ('PO5-WP2', 'IT budgets',
     'A budget reflecting the priorities established by the enterprise’s portfolio of IT-enabled investment programmes, and including the ongoing costs of operating and maintaining the current infrastructure',
     23, NOW(), NOW()),
    ('PO5-WP3', 'Updated IT service portfolio',
     'A grouping of IT services managed and monitored to optimise business value',
     24, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `wp` = VALUES(`wp`),
    `description` = VALUES(`description`),
    `order_no` = VALUES(`order_no`),
    `updated_at` = NOW();

-- Kaitkan WP hanya ke proses pemiliknya: PO1-WP... -> PO1, dst.
-- Berlaku juga untuk AI/DS/ME ketika WP-nya ditambahkan pada impor berikutnya.
INSERT INTO `trs_wp_to_obj_4.1` (`process_id`, `wp_id`, `created_at`, `updated_at`)
SELECT p.`id`, w.`wp_id`, NOW(), NOW()
FROM `mst_cobit4_processes` AS p
INNER JOIN `mst_wp_4.1` AS w
    ON p.`code` = SUBSTRING_INDEX(w.`wp_id`, '-', 1)
ON DUPLICATE KEY UPDATE
    `wp_id` = VALUES(`wp_id`);

COMMIT;

-- Hasil 1: tiga kolom isi dokumen, sesuai urutan sumber.
SELECT `wp_id` AS `WP ID`, `wp` AS `WP`, `description` AS `Description`
FROM `mst_wp_4.1`
ORDER BY `order_no`, `wp_id`;

-- Hasil 2: periksa WP yang belum tertaut karena master prosesnya belum ada.
-- Hasil kosong berarti semua WP sudah memiliki setidaknya satu relasi proses.
-- Jika tidak kosong, tambahkan master proses yang sesuai lalu impor ulang file ini.
SELECT w.`wp_id` AS `WP belum tertaut`,
       SUBSTRING_INDEX(w.`wp_id`, '-', 1) AS `Kode proses yang diperlukan`
FROM `mst_wp_4.1` AS w
WHERE NOT EXISTS (
    SELECT 1
    FROM `trs_wp_to_obj_4.1` AS r
    WHERE r.`wp_id` = w.`wp_id`
)
ORDER BY w.`order_no`, w.`wp_id`;

-- Contoh query untuk tab 5 nanti (filter per proses dan focus area):
-- SELECT w.wp_id AS `WP ID`, w.wp AS `WP`, w.description AS `Description`
-- FROM `mst_wp_4.1` AS w
-- INNER JOIN `trs_wp_to_obj_4.1` AS r ON r.wp_id = w.wp_id
-- INNER JOIN `mst_cobit4_processes` AS p ON p.id = r.process_id
-- WHERE p.code = 'PO1' AND p.focus_area_id = 27
-- ORDER BY w.order_no, w.wp_id;
