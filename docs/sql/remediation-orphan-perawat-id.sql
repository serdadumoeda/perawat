-- Remediation Script (PostgreSQL)
-- Case: orphan_perawat_id
-- Goal: perbaiki row yang perawat_id orphan, tetapi perawat_nik valid.
--
-- Cara pakai:
-- 1) Ganti <TABLE_NAME>.
-- 2) Jalankan apa adanya (dry-run, ROLLBACK).
-- 3) Verifikasi output.
-- 4) Untuk eksekusi final, ganti ROLLBACK -> COMMIT.

BEGIN;

-- Opsional: kunci ringan agar data konsisten selama pengecekan + update.
-- LOCK TABLE <TABLE_NAME> IN ROW EXCLUSIVE MODE;

-- Kandidat yang bisa auto-fix:
-- - perawat_id sekarang orphan
-- - perawat_nik valid di tabel perawat
CREATE TEMP TABLE tmp_fix_orphan_perawat_id AS
SELECT
    t.id,
    t.perawat_id AS old_perawat_id,
    t.perawat_nik
FROM <TABLE_NAME> t
LEFT JOIN perawat p_id ON p_id.id = t.perawat_id
JOIN perawat p_nik ON p_nik.nik = t.perawat_nik
WHERE t.perawat_id IS NOT NULL
  AND p_id.id IS NULL
  AND t.perawat_nik IS NOT NULL;

SELECT COUNT(*) AS candidates_auto_fix
FROM tmp_fix_orphan_perawat_id;

-- Remediasi: set perawat_id berdasar perawat_nik
UPDATE <TABLE_NAME> t
SET perawat_id = p.id
FROM tmp_fix_orphan_perawat_id c
JOIN perawat p ON p.nik = c.perawat_nik
WHERE t.id = c.id;

SELECT COUNT(*) AS fixed_rows
FROM <TABLE_NAME> t
JOIN tmp_fix_orphan_perawat_id c ON c.id = t.id
JOIN perawat p ON p.id = t.perawat_id
WHERE p.nik = c.perawat_nik;

-- Sisa orphan_perawat_id yang tidak bisa auto-fix (manual mapping diperlukan)
CREATE TEMP TABLE tmp_fix_orphan_perawat_id_manual AS
SELECT
    t.id,
    t.perawat_id,
    t.perawat_nik
FROM <TABLE_NAME> t
LEFT JOIN perawat p_id ON p_id.id = t.perawat_id
LEFT JOIN perawat p_nik ON p_nik.nik = t.perawat_nik
WHERE t.perawat_id IS NOT NULL
  AND p_id.id IS NULL
  AND (t.perawat_nik IS NULL OR p_nik.nik IS NULL);

SELECT COUNT(*) AS requires_manual_action
FROM tmp_fix_orphan_perawat_id_manual;

SELECT *
FROM tmp_fix_orphan_perawat_id_manual
ORDER BY id
LIMIT 100;

-- DRY-RUN mode
ROLLBACK;
