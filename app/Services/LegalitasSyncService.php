<?php

namespace App\Services;

use App\Models\DataLegalitas;
use Illuminate\Support\Carbon;

class LegalitasSyncService
{
    public function sync(): array
    {
        $result = [
            'processed' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        $today = now()->startOfDay();

        DataLegalitas::chunk(500, function ($items) use (&$result, $today) {
            foreach ($items as $item) {
                $result['processed']++;

                $newStatus = $item->status_str;

                if ($item->str_berlaku_sampai instanceof Carbon) {
                    $newStatus = $item->str_berlaku_sampai->lt($today)
                        ? DataLegalitas::STATUS_STR_EXPIRED
                        : DataLegalitas::STATUS_STR_AKTIF;
                } elseif (!$item->nomor_str && !$newStatus) {
                    $newStatus = DataLegalitas::STATUS_STR_HOLD;
                }

                $shouldUpdate = $newStatus !== $item->status_str
                    || !$item->terakhir_validasi_at
                    || $item->sumber_validasi !== 'sync_stub';

                $nextDataStatus = $item->data_status ?? DataLegalitas::DATA_STATUS_DRAFT;
                if ($newStatus === DataLegalitas::STATUS_STR_EXPIRED) {
                    $nextDataStatus = DataLegalitas::DATA_STATUS_EXPIRED;
                } elseif ($newStatus === DataLegalitas::STATUS_STR_AKTIF) {
                    $nextDataStatus = DataLegalitas::DATA_STATUS_VERIFIED;
                } elseif ($item->nomor_str && $nextDataStatus === DataLegalitas::DATA_STATUS_DRAFT) {
                    $nextDataStatus = DataLegalitas::DATA_STATUS_SUBMITTED;
                }

                if ($nextDataStatus !== $item->data_status) {
                    $shouldUpdate = true;
                }

                if ($shouldUpdate) {
                    $item->status_str = $newStatus;
                    $item->sumber_validasi = 'sync_stub';
                    $item->terakhir_validasi_at = now();
                    $item->data_status = $nextDataStatus;
                    if (!$item->source_verification) {
                        $item->source_verification = 'api';
                    }
                    $item->source_system = 'regulator_api';
                    $item->source_actor = 'regulator';
                    $item->save();

                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }
            }
        });

        return $result;
    }
}
