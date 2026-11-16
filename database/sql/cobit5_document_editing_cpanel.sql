-- Aktifkan Edit Dokumen / Information Flow COBIT 5 pada database aplikasi yang ada.
-- Jalankan setelah mst_evidence_ref dan katalog WP COBIT 5 tersedia.
-- Tidak menambah tabel, tidak mengganti isi dokumen/flow yang sudah tersimpan.
-- Kolom xqbaru nullable: mst_objective.cobit5_document, mst_infoflowoutput.work_product_id.
-- work_product_id merujuk ID master WP dalam mst_evidence_ref.
-- Aman dijalankan ulang. PHP deployment juga memerlukan file controller/service/UI editor terbaru.

SET @c5_has_document := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mst_objective' AND COLUMN_NAME = 'cobit5_document');
SET @c5_edit_sql := IF(@c5_has_document = 0,
  'ALTER TABLE mst_objective ADD COLUMN cobit5_document JSON NULL',
  'SELECT ''Kolom dokumen COBIT 5 sudah tersedia'' AS status');
PREPARE c5_edit_statement FROM @c5_edit_sql;
EXECUTE c5_edit_statement;
DEALLOCATE PREPARE c5_edit_statement;

SET @c5_has_wp := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mst_infoflowoutput' AND COLUMN_NAME = 'work_product_id');
SET @c5_edit_sql := IF(@c5_has_wp = 0,
  'ALTER TABLE mst_infoflowoutput ADD COLUMN work_product_id BIGINT UNSIGNED NULL',
  'SELECT ''Kolom relasi output WP sudah tersedia'' AS status');
PREPARE c5_edit_statement FROM @c5_edit_sql;
EXECUTE c5_edit_statement;
DEALLOCATE PREPARE c5_edit_statement;

SET @c5_has_wp_fk := (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mst_infoflowoutput' AND COLUMN_NAME = 'work_product_id'
  AND REFERENCED_TABLE_NAME = 'mst_evidence_ref' AND REFERENCED_COLUMN_NAME = 'id');
SET @c5_edit_sql := IF(@c5_has_wp_fk = 0,
  'ALTER TABLE mst_infoflowoutput ADD CONSTRAINT mst_infoflowoutput_work_product_id_foreign FOREIGN KEY (work_product_id) REFERENCES mst_evidence_ref(id) ON DELETE SET NULL',
  'SELECT ''Relasi output ke Master WP sudah tersedia'' AS status');
PREPARE c5_edit_statement FROM @c5_edit_sql;
EXECUTE c5_edit_statement;
DEALLOCATE PREPARE c5_edit_statement;

SELECT 'Editor dokumen dan output WP COBIT 5 aktif' AS status;
