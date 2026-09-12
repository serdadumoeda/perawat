-- Remediation Script (PostgreSQL)
-- Case: incomplete_pair
-- Goal: perbaiki row saat salah satu dari (perawat_id, perawat_nik) kosong.
--
-- Cara pakai:
-- 1) Ganti <TABLE_NAME>.
-- 2) Jalankan apa adanya (dry-run, ROLLBACK).
-- 3) Verifikasi output.
-- 4) Untuk eksekusi final, ganti ROLLBACK -> COMMIT.

BEGIN;

-- LOCK TABLE <TABLE_NAME> IN ROW EXCLUSIVE MODE;

-- A) perawat_id NULL, perawat_nik terisi dan valid -> isi perawat_id
CREATE TEMP TABLE tmp_fix_incomplete_fill_id AS
SELECT
    t.id,
    p.id AS expected_perawat_id,
    t.perawat_nik
FROM <TABLE_NAME> t
JOIN perawat p ON p.nik = t.perawat_nik
WHERE t.perawat_id IS NULL
  AND t.perawat_nik IS NOT NULL;

SELECT COUNT(*) AS candidates_fill_perawat_id
FROM tmp_fix_incomplete_fill_id;

UPDATE <TABLE_NAME> t
SET perawat_id = c.expected_perawat_id
FROM tmp_fix_incomplete_fill_id c
WHERE t.id = c.id;

-- B) perawat_nik NULL, perawat_id terisi dan valid -> isi perawat_nik
CREATE TEMP TABLE tmp_fix_incomplete_fill_nik AS
SELECT
    t.id,
    p.nik AS expected_perawat_nik,
    t.perawat_id
FROM <TABLE_NAME> t
JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_nik IS NULL
  AND t.perawat_id IS NOT NULL;

SELECT COUNT(*) AS candidates_fill_perawat_nik
FROM tmp_fix_incomplete_fill_nik;

UPDATE <TABLE_NAME> t
SET perawat_nik = c.expected_perawat_nik
FROM tmp_fix_incomplete_fill_nik c
WHERE t.id = c.id;

-- Validasi hasil
SELECT COUNT(*) AS remaining_incomplete_pair
FROM <TABLE_NAME> t
WHERE (t.perawat_id IS NULL AND t.perawat_nik IS NOT NULL)
   OR (t.perawat_id IS NOT NULL AND t.perawat_nik IS NULL);

-- Sisa data yang tetap butuh tindakan manual
CREATE TEMP TABLE tmp_fix_incomplete_pair_manual AS
SELECT
    t.id,
    t.perawat_id,
    t.perawat_nik
FROM <TABLE_NAME> t
LEFT JOIN perawat p_id ON p_id.id = t.perawat_id
LEFT JOIN perawat p_nik ON p_nik.nik = t.perawat_nik
WHERE (t.perawat_id IS NULL AND (t.perawat_nik IS NULL OR p_nik.nik IS NULL))
   OR (t.perawat_nik IS NULL AND (t.perawat_id IS NULL OR p_id.id IS NULL));

SELECT COUNT(*) AS requires_manual_action
FROM tmp_fix_incomplete_pair_manual;

SELECT *
FROM tmp_fix_incomplete_pair_manual
ORDER BY id
LIMIT 100;

-- DRY-RUN mode
ROLLBACK;
