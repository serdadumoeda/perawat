<?php

namespace App\Policies;

use App\Models\Lamaran;
use App\Models\User;

class LamaranPolicy
{
    public function manage(User $user, Lamaran $lamaran): bool
    {
        if (!$user->hasRole([User::ROLE_MITRA, User::ROLE_FASKES, User::ROLE_HEADHUNTER])) {
            return false;
        }

        return $user->mitraPengguna()
            ->where('mitra_pengguna.id', $lamaran->mitra_pengguna_id)
            ->exists();
    }
}
