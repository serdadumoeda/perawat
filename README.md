# 🏥 Platform Keperawatan Terpadu (Nursing Talent & Verification Exchange)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.style=for-the-badge)](#license)
[![Tests Status](https://img.shields.io/badge/Tests-135%20PASSED-success?style=for-the-badge)](#pengujian--kualitas-kode)

Platform Keperawatan Terpadu adalah ekosistem digital nasional untuk verifikasi verifikatif, data bank alumni, sertifikasi kompetensi, serta pertukaran talenta keperawatan (*Talent Pool & Placement*) di Indonesia. Platform ini menghubungkan Perawat, Kampus/Universitas, Lembaga Pelatihan, Rumah Sakit/Faskes (Mitra), dan Regulator (Dinas Kesehatan) secara real-time dan terintegrasi.

---

## 🌟 Fitur Utama Ekosistem (Multi-Actor System)

### 👩‍⚕️ 1. Portal Perawat (Nurse Portal)
* **Profil & CV Digital Terverifikasi**: Menampilkan status verifikasi identitas, pendidikan, legalitas, dan riwayat pekerjaan. Fitur cetak/unduh CV HTML & PDF terverifikasi.
* **Public Profile Sharing**: Bagikan profil publik profesional dengan token aman yang dapat disesuaikan.
* **Pengajuan Sinkronisasi STR**: Upload dokumen STR/SIPP & Izin Luar Negeri untuk diverifikasi oleh Regulator.
* **Lamaran Kerja & Pelatihan**: Melamar lowongan Faskes dan mendaftar pelatihan kompetensi keperawatan.

### 🎓 2. Portal & API Kampus (Institusi Pendidikan)
* **Ingest Data Lulusan**: Impor data alumni via file CSV maupun REST API Gateway (`/api/v1/lulusan`).
* **Kampus Gateway Lookup**: Pencarian verifikasi data alumni terintegrasi dengan PDDikti.
* **Tracing Alumni & Forecasting**: Analisis daya serap alumni, masa tunggu kerja, serta proyeksi *Supply & Demand* perawat 3 tahun ke depan.

### 📜 3. Portal & API Lembaga Pelatihan / Sertifikasi
* **Manajemen & Ingest Sertifikasi**: Pencatatan dan verifikasi sertifikat kompetensi (BTCLS, ICU, K3, Ners, dll) via Portal & REST API (`/api/v1/sertifikasi`).
* **Status Verifikasi Sertifikat**: Penandaan otomatis sertifikat terverifikasi untuk menaikkan status kesiapan kerja perawat.

### 🏢 4. Portal Mitra (Faskes / Rumah Sakit / Headhunter)
* **Talent Search Ready-to-Hire**: Pencarian perawat siap kerja dengan filter presisi (IPK, Jenjang, Lokasi, Status STR, Total Pengalaman Kerja, Sertifikat).
* **Anti-Poaching & Candidate Locking**: Mekanisme penguncian kandidat (*Lock Candidate*) dan proses rekrutmen terstruktur (*Process, Hire, Release*).
* **Verifikasi & Evaluasi Kinerja**: Pembaruan dan penilaian riwayat pekerjaan alumni (*Kinerja Rating & Score*).

### 🏛️ 5. Portal Regulator & Dinas Kesehatan
* **Audit & Review Legalitas STR**: Verifikasi data & dokumen STR, SIPP, dan Izin Luar Negeri perawat.
* **Auto-Sync STR Engine**: Penjadwalan otomatis sinkronisasi status STR (Aktif, Hold, Expired).
* **Reopen & Audit Log**: Riwayat peninjauan dokumen terperinci untuk transparansi kepatuhan hukum.

### ⚙️ 6. Administrator & Integrasi API Sistem
* **Manajemen API Keys & Signature**: Manajemen kunci API berbasis scope (`lulusan:read`, `lulusan:write`, `sertifikasi:write`, `legalitas:write`) dengan proteksi HMAC Signature.
* **Idempotency Protection**: Dukungan header `Idempotency-Key` untuk mencegah request duplikat pada transaksi API.
* **Webhook Event Engine**: Pengiriman webhook dengan mekanisme *exponential backoff retry* dan *Dead Letter Queue (DLQ)*.
* **Impersonation & Data Quality Metrics**: Fitur impersonate akun dan pemantauan kesehatan data (*preflight identity check*).

---

## 🛠️ Spesifikasi Teknologi

* **Framework**: Laravel 11.x
* **Bahasa**: PHP 8.2+
* **Database**: MySQL / PostgreSQL / SQLite
* **Frontend**: Laravel Blade, TailwindCSS, Alpine.js
* **Keamanan API**: API Key Scopes, HMAC Signature (SHA256), Idempotency Middleware, Rate Limiting Rate Throttling

---

## 🚀 Panduan Instalasi Lokal

### 1. Prasyarat System
* PHP >= 8.2 (ekstensi: `pdo`, `mbstring`, `openssl`, `json`, `curl`)
* Composer >= 2.x
* Node.js & NPM (opsional untuk build frontend)

### 2. Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/USERNAME-ANDA/platform-keperawatan.git
cd platform-keperawatan

# 2. Install dependensi composer
composer install

# 3. Salin file environment & buat app key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di file .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=platform_keperawatan
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Jalankan migrasi database
php artisan migrate

# 6. (Opsional) Isi data awal / demo scenario
php artisan db:seed
# Atau jalankan demo refresh:
# DEMO_SEED=true php artisan demo:refresh --force

# 7. Jalankan server lokal
php artisan serve
```

Aplikasi akan dapat diakses melalui `http://127.0.0.1:8000`.

---

## 🧪 Pengujian & Kualitas Kode

Platform ini dilengkapi dengan pengujian otomatis (*Automated Feature & Unit Testing*) yang mencakup alur rekrutmen, proteksi API, presedensi query SQL, dan integritas data.

Untuk menjalankan pengujian:

```bash
# Jalankan seluruh suite tes
php artisan test

# Jalankan pengecekan integritas NIK & perawat identity
php artisan preflight:perawat-identity
```

---

## 🔒 Keamanan & Proteksi Data

* Seluruh data identitas perawat (NIK) terikat dengan proteksi *foreign key constraint* secara ketat.
* Endpoint API terlindungi oleh otentikasi API Key, pencatatan log request, dan pembatasan rate limit.
* Data sensitif dan kredensial disimpulkan secara aman melalui file `.env`.

---

## 📝 Lisensi

Proyek ini dilindungi di bawah [Lisensi MIT](LICENSE).
