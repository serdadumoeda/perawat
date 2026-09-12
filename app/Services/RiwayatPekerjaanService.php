<?php

namespace App\Services;

use App\Models\Lowongan;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\User;

class RiwayatPekerjaanService
{
    public function startEmployment(User $actor, Perawat $perawat, string $mitraId, ?Lowongan $lowongan = null): RiwayatPekerjaan
    {
        $existing = RiwayatPekerjaan::query()
            ->where('perawat_nik', $perawat->nik)
            ->where('mitra_pengguna_id', $mitraId)
            ->where('status_pekerjaan', RiwayatPekerjaan::STATUS_AKTIF)
            ->latest()
            ->first();

        if ($existing) {
            if (!$existing->posisi && $lowongan && $lowongan->mitra_pengguna_id === $mitraId) {
                $existing->posisi = $lowongan->judul;
                $existing->save();
            }

            return $existing;
        }

        $posisi = null;
        if ($lowongan && $lowongan->mitra_pengguna_id === $mitraId) {
            $posisi = $lowongan->judul;
        }

        return RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitraId,
            'posisi' => $posisi,
            'tanggal_mulai' => now(),
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_AKTIF,
            'sumber_data' => RiwayatPekerjaan::SOURCE_MITRA,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'source_system' => 'mitra_portal',
            'source_actor' => 'mitra',
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $actor->id,
        ]);
    }
}
