<?php

namespace App\Policies;

use App\Models\InstitusiPendidikan;
use App\Models\User;

class InstitusiPendidikanPolicy
{
    public function manage(User $user, InstitusiPendidikan $institusi): bool
    {
        if (!$user->hasRole(User::ROLE_KAMPUS)) {
            return false;
        }

        return $user->institusiPendidikan()
            ->where('institusi_pendidikan.id', $institusi->id)
            ->exists();
    }
}
