# Operasional

## Queue
Default `QUEUE_CONNECTION=sync` akan menjalankan job langsung di request.
Untuk production:

1. Set `.env`:
   - `QUEUE_CONNECTION=database`
2. Jalankan migrasi:
   - `php artisan migrate`
3. Jalankan worker:
   - `php artisan queue:work --tries=3 --timeout=120`

## Scheduler
Legalitas sync dijalankan harian pukul 01:00.
Tambahkan cron:

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Storage
File sertifikat/transkrip disimpan pada disk `public`.
Pastikan `php artisan storage:link` sudah dijalankan.
