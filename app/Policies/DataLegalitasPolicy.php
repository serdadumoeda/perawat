<?php

namespace App\Policies;

use App\Models\DataLegalitas;
use App\Models\User;

class DataLegalitasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_REGULATOR);
    }

    public function view(User $user, DataLegalitas $legalitas): bool
    {
        return $this->canAccessByOrganisasi($user, $legalitas);
    }

    public function review(User $user, DataLegalitas $legalitas): bool
    {
        return $this->canAccessByOrganisasi($user, $legalitas);
    }

    private function canAccessByOrganisasi(User $user, DataLegalitas $legalitas): bool
    {
        if (!$user->hasRole(User::ROLE_REGULATOR)) {
            return false;
        }

        $allowedOrgIds = $user->regulatorOrganisasi()
            ->where('regulator_organisasi.is_active', true)
            ->pluck('regulator_organisasi.id')
            ->all();

        if ($allowedOrgIds === []) {
            // Backward-compatible: regulator tanpa mapping hanya boleh akses data belum terikat organisasi.
            return $legalitas->regulator_organisasi_id === null;
        }

        return $legalitas->regulator_organisasi_id === null
            || in_array($legalitas->regulator_organisasi_id, $allowedOrgIds, true);
    }
}
