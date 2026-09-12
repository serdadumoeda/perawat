<?php

namespace App\Policies;

use App\Models\SertifikasiKompetensi;
use App\Models\User;

class SertifikasiKompetensiPolicy
{
    public function verify(User $user, SertifikasiKompetensi $sertifikasi): bool
    {
        return $this->ownsLembaga($user, $sertifikasi);
    }

    public function reject(User $user, SertifikasiKompetensi $sertifikasi): bool
    {
        return $this->ownsLembaga($user, $sertifikasi);
    }

    private function ownsLembaga(User $user, SertifikasiKompetensi $sertifikasi): bool
    {
        if (!$user->hasRole(User::ROLE_LEMBAGA)) {
            return false;
        }

        return $user->lembagaPelatihan()
            ->where('lembaga_pelatihan.id', $sertifikasi->lembaga_pelatihan_id)
            ->exists();
    }
}
