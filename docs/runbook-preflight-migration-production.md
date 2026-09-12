# Runbook Preflight + Migration Production

Runbook ini khusus untuk migrasi schema yang menyentuh identitas perawat (`perawat_id` + `perawat_nik`) dan constraint trust layer.

## Scope
- Validasi data orphan/mismatch sebelum migrate.
- Urutan command production yang aman.
- Keputusan `GO / NO-GO` yang eksplisit.

## Prasyarat
- Backup database bisa dibuat dan di-restore.
- Akses shell ke server aplikasi.
- Versi code sudah dipastikan (tag/commit release).
- Worker queue dikelola supervisor/systemd.

## Ringkas Keputusan GO / NO-GO

### GO jika semua terpenuhi
- `php artisan preflight:perawat-identity` menghasilkan `Blockers: 0`.
- (Opsional ketat) `--fail-on-warning` juga exit code `0`.
- Backup DB berhasil.
- `php artisan migrate --force` sukses.
- Smoke test setelah migrate lolos.

### NO-GO jika salah satu terjadi
- Preflight menghasilkan blocker (`exit code 1`).
- Backup DB gagal.
- Migrasi gagal/timeout.
- Smoke test kritikal gagal (login, dashboard, API inti).

## Tahap 1 — Preflight di Production (tanpa ubah data)

Jalankan dari root project:

```bash
php artisan preflight:perawat-identity --json > storage/logs/preflight-perawat-identity-$(date +%F-%H%M%S).json
php artisan preflight:perawat-identity
```

Interpretasi:
- `Blockers > 0` → **NO-GO** (wajib perbaikan data dulu).
- `Warnings > 0` dan blocker `0` → boleh lanjut dengan approval bisnis/teknis.
- Jika ingin enforce zero-warning:

```bash
php artisan preflight:perawat-identity --fail-on-warning
```

## Tahap 2 — Window Migrasi Production

### 2.1 Persiapan release
```bash
php artisan optimize:clear
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 2.2 Freeze traffic tulis
Gunakan maintenance mode (disarankan):

```bash
php artisan down --render="errors::503"
```

### 2.3 Backup sebelum migrate
Lakukan backup via tooling DB Anda (pg_dump/mysqldump/snapshot).  
Simpan artefak backup dan bukti timestamp.

### 2.4 Jalankan preflight final (wajib)
```bash
php artisan preflight:perawat-identity
```

Jika ada blocker: **STOP** → keluar maintenance mode bila batal:

```bash
php artisan up
```

### 2.5 Migrasi
```bash
php artisan migrate --force
```

### 2.6 Warmup cache & worker
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### 2.7 Buka traffic
```bash
php artisan up
```

## Tahap 3 — Post Migration Verification (Smoke Test)

Minimal verifikasi:
- Login admin/perawat berhasil.
- Halaman regulator legalitas bisa dibuka.
- Tabel kandidat/riwayat pekerjaan terbuka normal.
- Endpoint API utama merespon 2xx/4xx sesuai kontrak (bukan 5xx).
- Tidak ada error kritikal di log:

```bash
tail -n 200 storage/logs/laravel.log
```

Jika gagal:
- **NO-GO / incident mode**.
- Prioritas rollback menggunakan restore backup database.
- Hindari rollback migrasi parsial jika sudah ada perubahan data runtime.

## Runbook Perbaikan Data (saat preflight gagal)

Gunakan output JSON preflight untuk menentukan tabel bermasalah:
- `orphan_perawat_id` → record menunjuk `perawat.id` yang tidak ada.
- `orphan_perawat_nik` → record menunjuk `perawat.nik` yang tidak ada.
- `mismatched_pair` → `perawat_id` dan `perawat_nik` tidak mereferensi perawat yang sama.
- `incomplete_pair` → salah satu kolom kosong.

Langkah umum:
1. Export daftar row bermasalah per tabel.
2. Koreksi dengan update terkontrol (SQL script + peer review).
3. Jalankan ulang preflight sampai blocker `0`.

Template SQL investigasi:
- `docs/preflight-sql-investigation.md`

Template SQL remediasi (dry-run BEGIN/ROLLBACK):
- `docs/sql/README.md`

## Audit Trail Operasional

Simpan artefak berikut per release:
- File output preflight JSON.
- Timestamp backup.
- Log output `migrate --force`.
- Hasil smoke test (checklist + operator).
