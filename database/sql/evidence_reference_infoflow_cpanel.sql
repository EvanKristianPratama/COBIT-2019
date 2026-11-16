-- Pemetaan Asal Referensi ke Information Flow, pada DATABASE APLIKASI yang ada.
-- Jalankan di phpMyAdmin > Import / SQL. Aman dijalankan ulang.
-- Prasyarat: mst_evidence_ref, mst_infoflowinput, mst_infoflowoutput sudah tersedia.
-- Hanya menambah tabel relasi. Tidak mengubah teks referensi, input/output,
-- atau evidence company. Tidak mengisi pemetaan berdasarkan tebakan nama dokumen.
-- Setelah aktivasi, pilih Input/Output dan practice melalui Tambah Ref / Edit Ref.

CREATE TABLE IF NOT EXISTS trs_evidence_ref_infoflow (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    evidence_ref_id BIGINT UNSIGNED NOT NULL,
    input_id BIGINT UNSIGNED NULL,
    output_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY evidence_ref_input_unique (evidence_ref_id, input_id),
    UNIQUE KEY evidence_ref_output_unique (evidence_ref_id, output_id),
    CONSTRAINT trs_evidence_ref_infoflow_evidence_ref_id_foreign FOREIGN KEY (evidence_ref_id) REFERENCES mst_evidence_ref(id) ON DELETE CASCADE,
    CONSTRAINT trs_evidence_ref_infoflow_input_id_foreign FOREIGN KEY (input_id) REFERENCES mst_infoflowinput(input_id) ON DELETE CASCADE,
    CONSTRAINT trs_evidence_ref_infoflow_output_id_foreign FOREIGN KEY (output_id) REFERENCES mst_infoflowoutput(output_id) ON DELETE CASCADE,
    CONSTRAINT evidence_ref_flow_direction_check CHECK ((input_id IS NOT NULL) <> (output_id IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Pemetaan Asal Referensi ke Input/Output siap dipakai' AS status;
