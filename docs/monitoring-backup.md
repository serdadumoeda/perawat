# Monitoring & Backup

Panduan ringkas untuk operasional.

## Monitoring
- Pastikan error log aplikasi dipantau (Laravel log di `storage/logs`).
- Gunakan APM/monitoring (Sentry/New Relic) untuk error dan performa.
- Pantau queue worker (restart otomatis jika crash).

## Backup
- Backup database harian (minimum) dan simpan minimal 7-14 hari.
- Uji proses restore secara berkala.
- Jika menggunakan storage lokal untuk file sertifikat/transkrip, backup folder `storage/app/public`.

## Alerting
- Pasang alert untuk:
  - Error rate meningkat.
  - Queue backlog tinggi.
  - Disk hampir penuh.
  - Gagal menjalankan scheduler.
