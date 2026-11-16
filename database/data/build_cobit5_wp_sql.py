"""Rebuild the phpMyAdmin import from the verbatim Figure 19 transcription."""
import csv
import hashlib
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SOURCE = ROOT / 'database/data/cobit5-work-products.tsv'
DESTINATION = ROOT / 'database/sql/cobit5_work_products_full_cpanel.sql'
PREFIX = 'COBIT 5 Figure 19 | '
COUNTS = {'EDM': [6, 6, 8, 8, 5], 'APO': [16, 14, 12, 12, 12, 13, 15, 9, 10, 13, 14, 15, 6],
          'BAI': [33, 9, 18, 10, 17, 6, 13, 7, 12, 10], 'DSS': [10, 14, 11, 18, 14, 11], 'MEA': [7, 12, 8]}


def quoted(value):
    # SQL standard quote escaping; the transcription deliberately has no backslashes.
    assert '\\' not in value
    return "'" + value.replace("'", "''") + "'"


def build():
    with SOURCE.open(encoding='utf-8', newline='') as stream:
        rows = list(csv.DictReader(stream, delimiter='\t'))
    assert len(rows) == len({row['wp_id'] for row in rows}) == 434
    assert all(set(row) == {'wp_id', 'wp', 'description'} and all(row.values()) for row in rows)
    assert max(len(row['wp']) for row in rows) <= 255
    for domain, counts in COUNTS.items():
        for index, count in enumerate(counts, 1):
            code = f'{domain}{index:02}'
            assert [row['wp_id'] for row in rows if row['wp_id'].split('-')[0] == code] == [f'{code}-WP{n}' for n in range(1, count + 1)], code
    assert len({(row['wp_id'].split('-')[0], row['wp']) for row in rows}) == 434
    values = []
    for order, row in enumerate(rows, 1):
        values.append('    (' + ', '.join(quoted(value) for value in [row['wp_id'], row['wp_id'].split('-')[0], row['wp'], row['description']]) + f', {order})')
    sql = """-- COBIT 5: 434 LEVEL 1 OUTPUT WORK PRODUCTS, FIGURE 19, 37 PROSES.
-- Transkripsi gambar pengguna: EDM=33, APO=161, BAI=135, DSS=78, MEA=27.
-- Teks sumber dipertahankan, termasuk ejaan dan tanda baca; baris visual disatukan.
-- Source SHA256: SOURCE_HASH
--
-- Pilih database aplikasi di phpMyAdmin > Import > pilih file ini > SQL / UTF-8.
-- TABEL YANG DIISI: mst_evidence_ref (tabel yang SUDAH ADA).
-- nama_dokumen=WP, keterangan=Description, referensi_sumber=provenance + WP ID.
-- objective_id dan focus_area_id memakai relasi yang sudah ada.
-- mst_objective dan mst_focusarea HANYA DIBACA; tidak mengubah COBIT 2019 / 4.1,
-- mst_infoflowoutput, mst_wp_4.1, trs_wp_to_obj_4.1 atau evidence perusahaan.
-- Tidak perlu menjalankan SQL COBIT 4.1 untuk impor ini. Tidak ada migrasi baru.
--
-- PRASYARAT: mst_evidence_ref memakai InnoDB dan struktur aplikasi yang ada:
-- id AUTO_INCREMENT, focus_area_id, objective_id, nama_dokumen, keterangan,
-- referensi_sumber, is_active, created_at, updated_at.
-- UNIQUE (focus_area_id, objective_id, nama_dokumen).
-- Ke-37 objective model COBIT 5 harus ada, version='5'. ID .M25 hanyalah contoh:
-- pencocokan menggunakan kode proses (EDM01, APO01, dst.), BUKAN ID model tetap.
-- Aman diimpor ulang: WP dengan kode sama diperbarui, ID referensi tetap dipakai.
-- Data lain tidak dihapus. Impor ulang mengembalikan teks WP sumber ini.
-- Tabel TEMPORARY hanya untuk validasi sesi dan dibuang di akhir, bukan tabel master.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
-- NULL = semua model version='5'. Untuk satu model, ganti NULL dengan ID model tersebut.
SET @c5_wp_focus_area_id := NULL;
SET @c5_wp_prefix := PREFIX_VALUE;

DROP TEMPORARY TABLE IF EXISTS tmp_cbioo_c5_wp_seed;
DROP TEMPORARY TABLE IF EXISTS tmp_cbioo_c5_wp_targets;
CREATE TEMPORARY TABLE tmp_cbioo_c5_wp_seed (
    wp_id VARCHAR(30) NOT NULL PRIMARY KEY,
    process_code VARCHAR(5) NOT NULL,
    wp VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    order_no INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_cbioo_c5_wp_seed (wp_id, process_code, wp, description, order_no) VALUES
SEED_VALUES;

START TRANSACTION;
CREATE TEMPORARY TABLE tmp_cbioo_c5_wp_targets ENGINE=InnoDB AS
SELECT f.id AS focus_area_id, o.objective_id, s.wp_id, s.process_code, s.wp, s.description, s.order_no
FROM tmp_cbioo_c5_wp_seed AS s
JOIN mst_objective AS o
  ON UPPER(SUBSTRING_INDEX(REPLACE(TRIM(o.objective_id), '"', ''), '.', 1)) = s.process_code
JOIN mst_focusarea AS f ON f.id = o.focus_area_id AND f.version = '5'
WHERE @c5_wp_focus_area_id IS NULL OR f.id = @c5_wp_focus_area_id;

SET @c5_wp_model_count := (
    SELECT COUNT(*) FROM mst_focusarea
    WHERE version = '5' AND (@c5_wp_focus_area_id IS NULL OR id = @c5_wp_focus_area_id)
);
SET @c5_wp_expected := 434 * @c5_wp_model_count;
SET @c5_wp_target_count := (SELECT COUNT(*) FROM tmp_cbioo_c5_wp_targets);
SET @c5_wp_duplicate_mapping := (
    SELECT COUNT(*) FROM (
        SELECT focus_area_id, wp_id FROM tmp_cbioo_c5_wp_targets
        GROUP BY focus_area_id, wp_id HAVING COUNT(*) <> 1
    ) AS duplicate_mapping
);
SET @c5_wp_duplicate_source := (
    SELECT COUNT(*) FROM (
        SELECT t.focus_area_id, t.objective_id, t.wp_id
        FROM tmp_cbioo_c5_wp_targets AS t
        JOIN mst_evidence_ref AS r ON r.focus_area_id = t.focus_area_id AND r.objective_id = t.objective_id
          AND r.referensi_sumber = CONCAT(@c5_wp_prefix, t.wp_id)
        GROUP BY t.focus_area_id, t.objective_id, t.wp_id HAVING COUNT(*) > 1
    ) AS duplicate_source
);
SET @c5_wp_name_conflicts := (
    SELECT COUNT(*) FROM tmp_cbioo_c5_wp_targets AS t
    JOIN mst_evidence_ref AS r ON r.focus_area_id = t.focus_area_id AND r.objective_id = t.objective_id
      AND r.nama_dokumen = t.wp
    WHERE COALESCE(r.referensi_sumber, '') <> CONCAT(@c5_wp_prefix, t.wp_id)
);
SET @c5_wp_import_allowed := (
    @c5_wp_model_count > 0 AND @c5_wp_target_count = @c5_wp_expected
    AND @c5_wp_duplicate_mapping = 0 AND @c5_wp_duplicate_source = 0 AND @c5_wp_name_conflicts = 0
);

-- Kedua penulisan memakai gerbang validasi yang sama. Jika prasyarat gagal,
-- tidak ada baris aplikasi yang ditulis. Lihat status/diagnostik pada hasil akhir.
UPDATE mst_evidence_ref AS r
JOIN tmp_cbioo_c5_wp_targets AS t ON r.focus_area_id = t.focus_area_id AND r.objective_id = t.objective_id
  AND r.referensi_sumber = CONCAT(@c5_wp_prefix, t.wp_id)
SET r.nama_dokumen = t.wp, r.keterangan = t.description, r.is_active = 1, r.updated_at = NOW()
WHERE @c5_wp_import_allowed = 1
  AND (BINARY r.nama_dokumen <> BINARY t.wp OR NOT (BINARY r.keterangan <=> BINARY t.description) OR r.is_active <> 1);

INSERT INTO mst_evidence_ref
    (focus_area_id, objective_id, nama_dokumen, keterangan, referensi_sumber, is_active, created_at, updated_at)
SELECT t.focus_area_id, t.objective_id, t.wp, t.description, CONCAT(@c5_wp_prefix, t.wp_id), 1, NOW(), NOW()
FROM tmp_cbioo_c5_wp_targets AS t
LEFT JOIN mst_evidence_ref AS r ON r.focus_area_id = t.focus_area_id AND r.objective_id = t.objective_id
  AND r.referensi_sumber = CONCAT(@c5_wp_prefix, t.wp_id)
WHERE @c5_wp_import_allowed = 1 AND r.id IS NULL;
COMMIT;

SELECT IF(@c5_wp_import_allowed = 1, 'BERHASIL: katalog WP COBIT 5 tersimpan',
    'DIBATALKAN: periksa model/proses yang belum lengkap atau konflik di bawah') AS status,
    @c5_wp_model_count AS jumlah_model_cobit5, @c5_wp_expected AS relasi_diharapkan,
    @c5_wp_target_count AS relasi_ditemukan, @c5_wp_duplicate_mapping AS kode_proses_ganda,
    @c5_wp_duplicate_source AS wp_id_ganda, @c5_wp_name_conflicts AS konflik_nama_dokumen;

-- Hasil normal per model: 434 WP aktif dan cocok teksnya, 37 proses.
SELECT t.focus_area_id, COUNT(*) AS jumlah_wp_sumber,
    COUNT(r.id) AS jumlah_wp_tersimpan,
    SUM(IF(r.is_active = 1 AND BINARY r.nama_dokumen = BINARY t.wp AND BINARY r.keterangan = BINARY t.description, 1, 0)) AS jumlah_teks_cocok,
    COUNT(DISTINCT t.process_code) AS jumlah_proses
FROM tmp_cbioo_c5_wp_targets AS t
LEFT JOIN mst_evidence_ref AS r ON r.focus_area_id = t.focus_area_id AND r.objective_id = t.objective_id
  AND r.referensi_sumber = CONCAT(@c5_wp_prefix, t.wp_id)
GROUP BY t.focus_area_id;

DROP TEMPORARY TABLE tmp_cbioo_c5_wp_targets;
DROP TEMPORARY TABLE tmp_cbioo_c5_wp_seed;
"""
    sql = sql.replace('SOURCE_HASH', hashlib.sha256(SOURCE.read_bytes()).hexdigest()).replace('PREFIX_VALUE', quoted(PREFIX)).replace('SEED_VALUES', ',\n'.join(values))
    DESTINATION.write_text(sql, encoding='utf-8')
    print(f'{DESTINATION}: {len(rows)} WP / 37 processes / {len(sql.encode())} bytes')


if __name__ == '__main__':
    build()
