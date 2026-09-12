<?php

namespace App\Policies;

use App\Models\RiwayatPekerjaan;
use App\Models\User;

class RiwayatPekerjaanPolicy
{
    public function create(User $user, string $mitraId): bool
    {
        if (!$user->hasRole([User::ROLE_MITRA, User::ROLE_FASKES, User::ROLE_HEADHUNTER])) {
            return false;
        }

        return $user->mitraPengguna()
            ->where('mitra_pengguna.id', $mitraId)
            ->exists();
    }

    public function verify(User $user, RiwayatPekerjaan $riwayat): bool
    {
        return $this->ownsMitra($user, $riwayat);
    }

    public function reject(User $user, RiwayatPekerjaan $riwayat): bool
    {
        return $this->ownsMitra($user, $riwayat);
    }

    public function updateStatus(User $user, RiwayatPekerjaan $riwayat): bool
    {
        return $this->ownsMitra($user, $riwayat);
    }

    private function ownsMitra(User $user, RiwayatPekerjaan $riwayat): bool
    {
        if (!$user->hasRole([User::ROLE_MITRA, User::ROLE_FASKES, User::ROLE_HEADHUNTER])) {
            return false;
        }

        return $user->mitraPengguna()
            ->where('mitra_pengguna.id', $riwayat->mitra_pengguna_id)
            ->exists();
    }
}
