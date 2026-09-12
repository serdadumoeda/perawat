# Production Checklist

Gunakan daftar ini sebelum go-live.

Referensi runbook: `docs/runbook-preflight-migration-production.md`

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `DEMO_SEED` tidak aktif di production.
- [ ] `APP_KEY` sudah di-generate.
- [ ] Konfigurasi database valid dan koneksi berhasil.
- [ ] Preflight identitas perawat lulus (`php artisan preflight:perawat-identity`, blocker = 0).
- [ ] Migrasi dijalankan dengan `php artisan migrate --force`.
- [ ] Storage link aktif: `php artisan storage:link`.
- [ ] Asset build selesai: `npm run build`.
- [ ] Cache aktif: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
- [ ] Queue worker berjalan dan diawasi (systemd/supervisor).
- [ ] Scheduler aktif (cron schedule:run).
- [ ] Backup database terjadwal.
- [ ] Log dan monitoring aktif.
- [ ] API keys dibuat untuk integrasi eksternal.
- [ ] Akses admin diverifikasi (akun admin aktif dan aman).
- [ ] SSL/TLS terpasang dan `APP_URL` sesuai domain.
- [ ] Rate limiting API diuji.
- [ ] Uji alur bisnis utama: lulusan, legalitas, sertifikasi, lowongan, lamaran, hiring.
- [ ] PDF CV diuji jika fitur PDF diaktifkan.
- [ ] Proses audit log diverifikasi.
