# API Ingestion

Base path: `/api/v1`

OpenAPI/Swagger:

- Swagger UI: `/api/v1/docs`
- OpenAPI JSON: `/api/v1/openapi.json`
- Postman collection (admin): `/admin/api-keys/postman-collection`
- Integration Playground (admin): `/admin/integration-playground`

## Auth
Send API key via header:

- `X-API-KEY: <token>`
- or `Authorization: Bearer <token>`

Scopes required per endpoint:

- `lulusan:write`
- `sertifikasi:write`
- `legalitas:write`

Rate limit: 120 requests / minute / API key (server-side).

Idempotency key (opsional, disarankan untuk POST ingestion):

- `Idempotency-Key: <unique_key>`
- Berlaku per kombinasi `API key + method + endpoint`.
- Jika key sama dan payload sama, server mengembalikan response yang sama (`X-Idempotency-Replayed: true`).
- Jika key sama tetapi payload beda, server mengembalikan `409 Conflict`.

Housekeeping idempotency:

- Command: `php artisan api:idempotency:purge`
- Dry run: `php artisan api:idempotency:purge --dry-run`
- Scheduler harian: otomatis dijalankan jam 02:00.

HMAC signature (opsional per API key):

- `X-API-TIMESTAMP: <unix_timestamp>`
- `X-API-SIGNATURE: <hex_hmac_sha256>`
- Formula: `HMAC_SHA256("{timestamp}.{raw_body}", signing_secret)`
- Jika API key `require_signature=true`, request tanpa signature akan ditolak.

## Request Logging
Semua request ingestion API otomatis dicatat ke tabel `api_request_logs` dengan atribut:

- `api_key_id`
- `endpoint`
- `payload_hash`
- `status_code`
- `response_time_ms`
- `error_message`

## Webhook Outgoing (Verified Event)
Admin dapat mengatur subscription webhook di halaman `/admin/integration-playground`.
Event yang dikirim:

- `legalitas.data.verified`
- `sertifikasi.verified`
- `riwayat_pekerjaan.verified`

Delivery policy:

- Retry otomatis dengan backoff (`WEBHOOK_RETRY_ATTEMPTS`, `WEBHOOK_RETRY_BACKOFF_SECONDS`).
- Jika seluruh retry gagal, delivery terakhir ditandai `is_dead_letter=true`.
- Riwayat delivery + status dead-letter tersedia di Integration Playground.
- Dead-letter bisa di-`Retry` ulang dari UI Integration Playground (admin).

Format payload:

```json
{
  "event": "legalitas.data.verified",
  "timestamp": "2026-02-14T10:00:00+00:00",
  "data": {
    "entity": "data_legalitas",
    "id": "<uuid>",
    "perawat_nik": "1234567890123456",
    "status": "verified",
    "verified_at": "2026-02-14T10:00:00+00:00"
  }
}
```

## Common Response
All endpoints return JSON with summary counts and error list.

Successful response example:

```json
{
  "processed": 2,
  "created": 2,
  "updated": 0,
  "errors": []
}
```

Error response example:

```json
{
  "message": "Unauthorized"
}
```

## POST /lulusan
Scopes: `lulusan:write`

Request body:

```json
{
  "data": [
    {
      "nik": "1234567890123456",
      "nama": "Siti Aminah",
      "email": "siti@example.com",
      "no_hp": "08123456789",
      "jenis_kelamin": "P",
      "tempat_lahir": "Bandung",
      "tanggal_lahir": "2000-02-01",
      "alamat": "Jl. Merdeka No. 1",
      "jenjang": "S1",
      "program_studi": "Keperawatan",
      "nomor_ijazah": "IJZ-2024-0001",
      "tanggal_ijazah": "2024-08-01",
      "tahun_masuk": "2020",
      "tahun_lulus": "2024",
      "ipk": 3.75
    }
  ]
}
```

Rules (ringkas):

- `data` wajib array, 1..500 item
- `nik` wajib 16 digit
- `ipk` 0..4.00

## POST /sertifikasi
Scopes: `sertifikasi:write`

Request body:

```json
{
  "data": [
    {
      "nik": "1234567890123456",
      "nama_sertifikat": "Pelatihan ICU",
      "nomor_sertifikat": "CERT-ICU-001",
      "tanggal_terbit": "2024-08-01",
      "tanggal_kadaluarsa": "2026-08-01",
      "status_verifikasi": "valid"
    }
  ]
}
```

Rules (ringkas):

- `data` wajib array, 1..500 item
- `nik` wajib 16 digit

## POST /legalitas
Scopes: `legalitas:write`

Request body:

```json
{
  "data": [
    {
      "nik": "1234567890123456",
      "nomor_str": "STR-12345",
      "status_str": "aktif",
      "str_berlaku_mulai": "2023-01-01",
      "str_berlaku_sampai": "2028-01-01",
      "nomor_izin_luar_negeri": "LN-001",
      "status_izin_luar_negeri": "aktif",
      "sumber_validasi": "api"
    }
  ]
}
```

Rules (ringkas):

- `data` wajib array, 1..500 item
- `nik` wajib 16 digit
- `status_str` salah satu: `aktif`, `hold`, `expired`
