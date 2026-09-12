-- Remediation Script (PostgreSQL)
-- Case: orphan_perawat_nik
-- Goal: perbaiki row yang perawat_nik orphan, tetapi perawat_id valid.
--
-- Cara pakai:
-- 1) Ganti <TABLE_NAME>.
-- 2) Jalankan apa adanya (dry-run, ROLLBACK).
-- 3) Verifikasi output.
-- 4) Untuk eksekusi final, ganti ROLLBACK -> COMMIT.

BEGIN;

-- LOCK TABLE <TABLE_NAME> IN ROW EXCLUSIVE MODE;

-- Kandidat yang bisa auto-fix:
-- - perawat_nik sekarang orphan
-- - perawat_id valid di tabel perawat
CREATE TEMP TABLE tmp_fix_orphan_perawat_nik AS
SELECT
    t.id,
    t.perawat_id,
    t.perawat_nik AS old_perawat_nik,
    p_id.nik AS expected_perawat_nik
FROM <TABLE_NAME> t
LEFT JOIN perawat p_nik ON p_nik.nik = t.perawat_nik
JOIN perawat p_id ON p_id.id = t.perawat_id
WHERE t.perawat_nik IS NOT NULL
  AND p_nik.nik IS NULL
  AND t.perawat_id IS NOT NULL;

SELECT COUNT(*) AS candidates_auto_fix
FROM tmp_fix_orphan_perawat_nik;

-- Remediasi: set perawat_nik berdasar perawat_id
UPDATE <TABLE_NAME> t
SET perawat_nik = c.expected_perawat_nik
FROM tmp_fix_orphan_perawat_nik c
WHERE t.id = c.id;

SELECT COUNT(*) AS fixed_rows
FROM <TABLE_NAME> t
JOIN tmp_fix_orphan_perawat_nik c ON c.id = t.id
WHERE t.perawat_nik = c.expected_perawat_nik;

-- Sisa orphan_perawat_nik yang tidak bisa auto-fix
CREATE TEMP TABLE tmp_fix_orphan_perawat_nik_manual AS
SELECT
    t.id,
    t.perawat_id,
    t.perawat_nik
FROM <TABLE_NAME> t
LEFT JOIN perawat p_nik ON p_nik.nik = t.perawat_nik
LEFT JOIN perawat p_id ON p_id.id = t.perawat_id
WHERE t.perawat_nik IS NOT NULL
  AND p_nik.nik IS NULL
  AND (t.perawat_id IS NULL OR p_id.id IS NULL);

SELECT COUNT(*) AS requires_manual_action
FROM tmp_fix_orphan_perawat_nik_manual;

SELECT *
FROM tmp_fix_orphan_perawat_nik_manual
ORDER BY id
LIMIT 100;

-- DRY-RUN mode
ROLLBACK;
