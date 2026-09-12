# Template SQL Investigasi Temuan Preflight

Dokumen ini berisi template SQL **read-only** untuk investigasi hasil `preflight:perawat-identity`.

## Cara pakai
- Ganti `<TABLE_NAME>` dengan tabel yang ingin diperiksa.
- Pastikan tabel tersebut punya kolom `id`, `perawat_id`, `perawat_nik`.
- Jalankan query `COUNT` dulu, lalu query detail (`LIMIT`) untuk sampling.

Contoh tabel strict:
- `riwayat_pendidikan`
- `sertifikasi_kompetensi`
- `data_legalitas`
- `riwayat_pekerjaan`
- `lamaran`
- `rekrutmen`
- `pelatihan_peserta`
- `tracing_alumni`
- `str_sync_requests`

## 1) orphan_perawat_id
`perawat_id` terisi tetapi tidak ada di tabel `perawat`.

```sql
-- COUNT
SELECT COUNT(*) AS orphan_perawat_id
FROM <TABLE_NAME> t
LEFT JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_id IS NOT NULL
  AND p.id IS NULL;
```

```sql
-- DETAIL SAMPLE
SELECT t.*
FROM <TABLE_NAME> t
LEFT JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_id IS NOT NULL
  AND p.id IS NULL
ORDER BY t.updated_at DESC
LIMIT 200;
```

## 2) orphan_perawat_nik
`perawat_nik` terisi tetapi tidak ada di tabel `perawat`.

```sql
-- COUNT
SELECT COUNT(*) AS orphan_perawat_nik
FROM <TABLE_NAME> t
LEFT JOIN perawat p ON p.nik = t.perawat_nik
WHERE t.perawat_nik IS NOT NULL
  AND p.nik IS NULL;
```

```sql
-- DETAIL SAMPLE
SELECT t.*
FROM <TABLE_NAME> t
LEFT JOIN perawat p ON p.nik = t.perawat_nik
WHERE t.perawat_nik IS NOT NULL
  AND p.nik IS NULL
ORDER BY t.updated_at DESC
LIMIT 200;
```

## 3) mismatched_pair
`perawat_id` dan `perawat_nik` sama-sama terisi, tetapi tidak menunjuk perawat yang sama.

```sql
-- COUNT
SELECT COUNT(*) AS mismatched_pair
FROM <TABLE_NAME> t
JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_nik IS NOT NULL
  AND t.perawat_nik <> p.nik;
```

```sql
-- DETAIL SAMPLE + expected_nik
SELECT
  t.id,
  t.perawat_id,
  t.perawat_nik AS current_perawat_nik,
  p.nik AS expected_perawat_nik,
  t.created_at,
  t.updated_at
FROM <TABLE_NAME> t
JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_nik IS NOT NULL
  AND t.perawat_nik <> p.nik
ORDER BY t.updated_at DESC
LIMIT 200;
```

## 4) incomplete_pair
Salah satu kolom terisi, kolom pasangan kosong.

```sql
-- COUNT
SELECT COUNT(*) AS incomplete_pair
FROM <TABLE_NAME> t
WHERE (t.perawat_id IS NULL AND t.perawat_nik IS NOT NULL)
   OR (t.perawat_id IS NOT NULL AND t.perawat_nik IS NULL);
```

```sql
-- DETAIL SAMPLE
SELECT
  t.id,
  t.perawat_id,
  t.perawat_nik,
  t.created_at,
  t.updated_at
FROM <TABLE_NAME> t
WHERE (t.perawat_id IS NULL AND t.perawat_nik IS NOT NULL)
   OR (t.perawat_id IS NOT NULL AND t.perawat_nik IS NULL)
ORDER BY t.updated_at DESC
LIMIT 200;
```

## Query bantu korelasi (opsional)

```sql
-- Cek apakah perawat_id valid dan nik yang seharusnya
SELECT
  t.id,
  t.perawat_id,
  p.nik AS expected_nik
FROM <TABLE_NAME> t
LEFT JOIN perawat p ON p.id = t.perawat_id
WHERE t.perawat_id IS NOT NULL
ORDER BY t.updated_at DESC
LIMIT 200;
```

```sql
-- Cek apakah perawat_nik valid dan id yang seharusnya
SELECT
  t.id,
  t.perawat_nik,
  p.id AS expected_perawat_id
FROM <TABLE_NAME> t
LEFT JOIN perawat p ON p.nik = t.perawat_nik
WHERE t.perawat_nik IS NOT NULL
ORDER BY t.updated_at DESC
LIMIT 200;
```

## Catatan operasi
- Jalankan per tabel agar investigasi terkontrol.
- Simpan hasil query sebagai artefak incident/release.
- Koreksi data dilakukan terpisah melalui script SQL ter-review (jangan langsung dari template ini).
