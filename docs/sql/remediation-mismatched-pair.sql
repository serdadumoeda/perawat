-- Remediation Script (PostgreSQL)
-- Case: mismatched_pair
-- Goal: perbaiki row yang perawat_id dan perawat_nik menunjuk perawat berbeda.
--
-- Prinsip script ini:
-- - perawat_id dijadikan source-of-truth
-- - perawat_nik disinkronkan mengikuti perawat_id
--
-- Cara pakai:
-- 1) Ganti <TABLE_NAME>.
-- 2) Jalankan apa adanya (dry-run, ROLLBACK).
-- 3) Verifikasi output.
-- 4) Untuk eksekusi final, ganti ROLLBACK -> COMMIT.

BEGIN;

-- LOCK TABLE <TABLE_NAME> IN ROW EXCLUSIVE MODE;

CREATE TEMP TABLE tmp_fix_mismatched_pair AS
SELECT
    t.id,
    t.perawat_id,
    t.perawat_nik AS old_perawat_nik,
    p.nik AS expected_perawat_nik
FROM <TABLE_NAME> t
JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_nik IS NOT NULL
  AND t.perawat_nik <> p.nik;

SELECT COUNT(*) AS candidates_auto_fix
FROM tmp_fix_mismatched_pair;

UPDATE <TABLE_NAME> t
SET perawat_nik = c.expected_perawat_nik
FROM tmp_fix_mismatched_pair c
WHERE t.id = c.id;

SELECT COUNT(*) AS fixed_rows
FROM <TABLE_NAME> t
JOIN tmp_fix_mismatched_pair c ON c.id = t.id
WHERE t.perawat_nik = c.expected_perawat_nik;

-- Validasi ulang mismatch setelah update
SELECT COUNT(*) AS remaining_mismatch
FROM <TABLE_NAME> t
JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_nik IS NOT NULL
  AND t.perawat_nik <> p.nik;

SELECT *
FROM tmp_fix_mismatched_pair
ORDER BY id
LIMIT 100;

-- DRY-RUN mode
ROLLBACK;
