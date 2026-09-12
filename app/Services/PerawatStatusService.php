<?php

namespace App\Services;

use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;

class PerawatStatusService
{
    public function refresh(Perawat $perawat): void
    {
        $hasPendidikan = $perawat->riwayatPendidikan()
            ->where('status_verifikasi', RiwayatPendidikan::VERIF_VERIFIED)
            ->exists();
        $hasKompetensi = $perawat->sertifikasi()
            ->whereIn('status_verifikasi', SertifikasiKompetensi::verifiedStatuses())
            ->exists();

        $legalitas = $perawat->dataLegalitas;
        $legalitasAktif = $legalitas
            && $legalitas->status_str === DataLegalitas::STATUS_STR_AKTIF
            && $legalitas->data_status === DataLegalitas::DATA_STATUS_VERIFIED;

        $perawat->status_kesiapan = ($hasPendidikan && $hasKompetensi && $legalitasAktif)
            ? Perawat::KESIAPAN_READY
            : Perawat::KESIAPAN_DRAFT;

        $hasHired = $perawat->rekrutmen()->where('status', Rekrutmen::STATUS_HIRED)->exists();
        $hasActiveJob = $perawat->riwayatPekerjaan()
            ->where('status_pekerjaan', RiwayatPekerjaan::STATUS_AKTIF)
            ->where(function ($q) {
                $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                    ->orWhere('is_verified', true);
            })
            ->exists();
        if ($hasHired || $hasActiveJob) {
            $perawat->status_ketenagakerjaan = Perawat::KETENAGAKERJAAN_BEKERJA;
        } else {
            $hasVerified = $perawat->riwayatPekerjaan()
                ->where(function ($q) {
                    $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                        ->orWhere('is_verified', true);
                })
                ->exists();
            $perawat->status_ketenagakerjaan = $hasVerified
                ? Perawat::KETENAGAKERJAAN_BERPENGALAMAN
                : Perawat::KETENAGAKERJAAN_FRESH;
        }

        $hasLockedOrHired = $perawat->rekrutmen()
            ->whereIn('status', [Rekrutmen::STATUS_LOCKED, Rekrutmen::STATUS_HIRED])
            ->exists();

        if ($hasHired || $hasActiveJob || $hasLockedOrHired) {
            $perawat->status_ketersediaan = Perawat::KETERSEDIAAN_UNAVAILABLE;
        } elseif ($perawat->status_ketersediaan === Perawat::KETERSEDIAAN_UNAVAILABLE) {
            $perawat->status_ketersediaan = Perawat::KETERSEDIAAN_AVAILABLE;
        }

        $perawat->save();
    }
}
