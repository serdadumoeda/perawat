<?php

namespace App\Services;

use App\Models\Lowongan;
use App\Models\Perawat;
use App\Models\User;
use App\Notifications\LowonganBaruNotification;

class LowonganNotificationService
{
    public function notifyMatchingPerawat(Lowongan $lowongan): int
    {
        $kriteria = $lowongan->kriteria ?? [];

        $query = Perawat::query()
            ->where('status_kesiapan', Perawat::KESIAPAN_READY)
            ->whereHas('user', function ($q) {
                $q->where('role', User::ROLE_PERAWAT);
            });

        if (!empty($kriteria['status_str'])) {
            $query->whereHas('dataLegalitas', function ($q) use ($kriteria) {
                $q->where('status_str', $kriteria['status_str']);
            });
        }

        if (!empty($kriteria['sertifikat'])) {
            $query->whereHas('sertifikasi', function ($q) use ($kriteria) {
                $q->where('nama_sertifikat', 'like', '%'.$kriteria['sertifikat'].'%');
            });
        }

        $notified = 0;
        $buffer = [];

        foreach ($query->select('nik')->cursor() as $perawat) {
            $buffer[] = $perawat->nik;

            if (count($buffer) >= 200) {
                $notified += $this->notifyUsersByPerawatIds($buffer, $lowongan);
                $buffer = [];
            }
        }

        if (!empty($buffer)) {
            $notified += $this->notifyUsersByPerawatIds($buffer, $lowongan);
        }

        return $notified;
    }

    private function notifyUsersByPerawatIds(array $perawatNiks, Lowongan $lowongan): int
    {
        $count = 0;

        User::where('role', User::ROLE_PERAWAT)
            ->whereIn('perawat_nik', $perawatNiks)
            ->each(function (User $user) use ($lowongan, &$count) {
                $user->notify(new LowonganBaruNotification($lowongan));
                $count++;
            });

        return $count;
    }
}
