<?php

namespace App\Traits;

use App\Models\Perawat;
use InvalidArgumentException;

trait HasPerawatNik
{
    protected static function bootHasPerawatNik(): void
    {
        static::saving(function ($model) {
            if (empty($model->perawat_id) && !empty($model->perawat_nik)) {
                $model->perawat_id = Perawat::where('nik', $model->perawat_nik)->value('id');
            }

            if (empty($model->perawat_nik) && !empty($model->perawat_id)) {
                $model->perawat_nik = Perawat::whereKey($model->perawat_id)->value('nik');
            }

            if (!empty($model->perawat_id) && !empty($model->perawat_nik)) {
                $isPairValid = Perawat::query()
                    ->whereKey($model->perawat_id)
                    ->where('nik', $model->perawat_nik)
                    ->exists();

                if (!$isPairValid) {
                    throw new InvalidArgumentException('Kombinasi perawat_id dan perawat_nik tidak konsisten.');
                }
            }

            if (!empty($model->perawat_id) && empty($model->perawat_nik)) {
                throw new InvalidArgumentException('Perawat tidak ditemukan untuk ID yang diberikan.');
            }

            if (!empty($model->perawat_nik) && empty($model->perawat_id)) {
                throw new InvalidArgumentException('Perawat tidak ditemukan untuk NIK yang diberikan.');
            }
        });
    }
}
