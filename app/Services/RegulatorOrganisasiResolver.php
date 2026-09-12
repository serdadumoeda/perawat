<?php

namespace App\Services;

use App\Models\RegulatorOrganisasi;
use App\Models\User;

class RegulatorOrganisasiResolver
{
    public function forUser(?User $user): ?RegulatorOrganisasi
    {
        if (!$user || !$user->hasRole(User::ROLE_REGULATOR)) {
            return null;
        }

        return $user->regulatorOrganisasi()
            ->where('regulator_organisasi.is_active', true)
            ->orderByDesc('organisasi_user.created_at')
            ->first()
            ?: $user->regulatorOrganisasi()
                ->orderByDesc('organisasi_user.created_at')
                ->first();
    }

    public function context(?User $user): array
    {
        $organisasi = $this->forUser($user);

        if (!$organisasi) {
            return [
                'regulator_organisasi_id' => null,
                'regulator_organisasi_nama' => null,
                'regulator_organisasi_kode' => null,
            ];
        }

        return [
            'regulator_organisasi_id' => $organisasi->id,
            'regulator_organisasi_nama' => $organisasi->nama,
            'regulator_organisasi_kode' => $organisasi->kode,
        ];
    }
}
