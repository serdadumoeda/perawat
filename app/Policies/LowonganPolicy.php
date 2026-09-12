<?php

namespace App\Policies;

use App\Models\Lowongan;
use App\Models\User;

class LowonganPolicy
{
    public function view(User $user, Lowongan $lowongan): bool
    {
        if (!$user->hasRole([User::ROLE_MITRA, User::ROLE_FASKES, User::ROLE_HEADHUNTER])) {
            return false;
        }

        return $user->mitraPengguna()
            ->where('mitra_pengguna.id', $lowongan->mitra_pengguna_id)
            ->exists();
    }

    public function create(User $user, string $mitraId): bool
    {
        if (!$user->hasRole([User::ROLE_MITRA, User::ROLE_FASKES, User::ROLE_HEADHUNTER])) {
            return false;
        }

        return $user->mitraPengguna()
            ->where('mitra_pengguna.id', $mitraId)
            ->exists();
    }

    public function update(User $user, Lowongan $lowongan): bool
    {
        return $this->view($user, $lowongan);
    }

    public function delete(User $user, Lowongan $lowongan): bool
    {
        return $this->view($user, $lowongan);
    }
}
