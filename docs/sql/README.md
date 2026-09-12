# SQL Remediasi Preflight

Folder ini berisi script SQL remediasi terkontrol (PostgreSQL) untuk temuan preflight identitas perawat.

## File
- `remediation-orphan-perawat-id.sql`
- `remediation-orphan-perawat-nik.sql`
- `remediation-mismatched-pair.sql`
- `remediation-incomplete-pair.sql`

## Pola eksekusi aman
1. Ganti placeholder `<TABLE_NAME>`.
2. Jalankan script dalam mode default (akhir script `ROLLBACK`) untuk dry-run.
3. Review output `candidates`, `fixed_rows`, dan `requires_manual_action`.
4. Jika hasil valid, eksekusi ulang dengan mengganti `ROLLBACK` menjadi `COMMIT`.

## Tabel yang umum diperbaiki
- `riwayat_pendidikan`
- `sertifikasi_kompetensi`
- `data_legalitas`
- `riwayat_pekerjaan`
- `lamaran`
- `rekrutmen`
- `pelatihan_peserta`
- `tracing_alumni`
- `str_sync_requests`

Catatan: jalankan satu tabel per eksekusi agar audit trail jelas.
