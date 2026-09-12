<?php

namespace App\Policies;

use App\Models\LembagaPelatihan;
use App\Models\User;

class LembagaPelatihanPolicy
{
    public function manage(User $user, LembagaPelatihan $lembaga): bool
    {
        if (!$user->hasRole(User::ROLE_LEMBAGA)) {
            return false;
        }

        return $user->lembagaPelatihan()
            ->where('lembaga_pelatihan.id', $lembaga->id)
            ->exists();
    }
}
