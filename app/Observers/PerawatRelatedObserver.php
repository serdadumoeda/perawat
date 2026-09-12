<?php

namespace App\Observers;

use App\Models\Perawat;
use App\Services\PerawatStatusService;
use Illuminate\Database\Eloquent\Model;

class PerawatRelatedObserver
{
    public function __construct(private PerawatStatusService $statusService)
    {
    }

    public function saved(Model $model): void
    {
        $this->refresh($model);
    }

    public function deleted(Model $model): void
    {
        $this->refresh($model);
    }

    private function refresh(Model $model): void
    {
        $perawatId = $model->perawat_id ?? null;
        $perawatNik = $model->perawat_nik ?? null;

        if (!$perawatId && !$perawatNik) {
            return;
        }

        $perawat = $perawatId
            ? Perawat::find($perawatId)
            : Perawat::where('nik', $perawatNik)->first();
        if (!$perawat) {
            return;
        }

        $this->statusService->refresh($perawat);
    }
}
