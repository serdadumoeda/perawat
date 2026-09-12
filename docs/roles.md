# Roles & Akses

Dokumen ringkas hak akses per role (non-teknis dan teknis).

## Ringkas Hak Akses
- `kampus`: input data lulusan + akademik (riwayat pendidikan, transkrip, IPK)
- `lembaga`: input sertifikasi kompetensi dan status verifikasi
- `regulator`: input/validasi legalitas (STR, izin luar negeri)
- `mitra` / `faskes`: cari kandidat, buat lowongan, proses lamaran, input & verifikasi riwayat kerja, update status kerja (selesai/resign)
- `perawat`: melihat profil, melamar lowongan, input riwayat kerja mandiri, melihat notifikasi
- `admin`: kelola master data organisasi, mapping user, audit log, laporan, API keys

## Akses Route (ringkas)
- `/kampus/*` hanya `kampus`
- `/lembaga/*` hanya `lembaga`
- `/regulator/*` hanya `regulator`
- `/mitra/*` hanya `mitra` atau `faskes`
- `/perawat/*` hanya `perawat`
- `/admin/*` hanya `admin`

## Catatan
- Semua akses mitra terhadap data dibatasi ke organisasi mitra yang terhubung pada akun.
- Semua aksi penting tercatat di audit log (admin dapat melihat).
