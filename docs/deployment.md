# Deployment

Dokumentasi ringkas untuk menjalankan aplikasi di staging/production.

## Prasyarat
- PHP 8.2+
- Composer
- Node.js + npm
- Database (MySQL/PostgreSQL)
- Web server (Nginx/Apache)

## Langkah Deploy
1. Clone repository ke server.
2. Jalankan `composer install`.
3. Salin `.env.example` ke `.env`.
4. Set konfigurasi `.env` utama. Minimal: `APP_NAME`, `APP_ENV`, `APP_DEBUG=false`, `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `QUEUE_CONNECTION` (disarankan `database`), `CACHE_DRIVER`, `SESSION_DRIVER`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` jika ingin membuat admin otomatis saat seeding.
5. Jalankan `php artisan key:generate`.
6. Jalankan `php artisan migrate --force`.
7. Jalankan `php artisan storage:link`.
8. Jalankan `npm install` dan `npm run build`.
9. Cache konfigurasi: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
10. Jalankan queue worker: `php artisan queue:work --tries=3 --timeout=120`.
11. Aktifkan scheduler (cron): `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`.

## Optional
- PDF CV terverifikasi membutuhkan `barryvdh/laravel-dompdf`.
- Demo data: set `DEMO_SEED=true` lalu jalankan `php artisan db:seed`.
- Refresh demo data: `php artisan demo:refresh` (non-production).

## API Keys
- Admin dapat membuat API keys via menu Admin.
- API endpoint tersedia di `/api/v1/*` dengan header `X-API-KEY`.

## Referensi Wajib (Production)
- Runbook preflight + migration: `docs/runbook-preflight-migration-production.md`
