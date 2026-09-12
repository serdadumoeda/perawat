# Demo Data

Gunakan demo data untuk review bisnis end-to-end.

## Menjalankan Demo Seed
1. Set `DEMO_SEED=true` di `.env`.
2. Jalankan:
   - `php artisan db:seed`

Akun demo (password: `password`):
- `admin@demo.test`
- `kampus@demo.test`
- `lembaga@demo.test`
- `mitra@demo.test`
- `faskes@demo.test`
- `regulator@demo.test`
- `perawat@demo.test`

## Refresh Demo Data
Perintah ini akan menghapus data aplikasi dan mengisi ulang demo.

```
php artisan demo:refresh
```

Catatan:
- Tidak bisa dijalankan di `APP_ENV=production`.
- Membutuhkan `DEMO_SEED=true`.
