<?php

namespace App\Observers;

use App\Models\DataLegalitas;
use App\Models\RiwayatPekerjaan;
use App\Models\SertifikasiKompetensi;
use App\Models\WebhookSubscription;
use App\Services\OutgoingWebhookService;

class VerifiedStatusWebhookObserver
{
    public function __construct(private readonly OutgoingWebhookService $webhookService)
    {
    }

    public function created(DataLegalitas|RiwayatPekerjaan|SertifikasiKompetensi $model): void
    {
        $this->dispatchIfVerified($model, true);
    }

    public function updated(DataLegalitas|RiwayatPekerjaan|SertifikasiKompetensi $model): void
    {
        $this->dispatchIfVerified($model, false);
    }

    private function dispatchIfVerified(DataLegalitas|RiwayatPekerjaan|SertifikasiKompetensi $model, bool $isCreated): void
    {
        [$event, $isVerifiedNow] = $this->resolveEventAndState($model, $isCreated);

        if (!$event || !$isVerifiedNow) {
            return;
        }

        $this->webhookService->dispatch($event, $this->payloadFor($model));
    }

    private function resolveEventAndState(DataLegalitas|RiwayatPekerjaan|SertifikasiKompetensi $model, bool $isCreated): array
    {
        if ($model instanceof DataLegalitas) {
            $isVerified = $model->data_status === DataLegalitas::DATA_STATUS_VERIFIED;
            $changedToVerified = $isCreated
                ? $isVerified
                : $model->wasChanged('data_status') && $isVerified;

            return [WebhookSubscription::EVENT_LEGALITAS_VERIFIED, $changedToVerified];
        }

        if ($model instanceof RiwayatPekerjaan) {
            $isVerified = $model->verifikasi_status === RiwayatPekerjaan::VERIF_VERIFIED;
            $changedToVerified = $isCreated
                ? $isVerified
                : $model->wasChanged('verifikasi_status') && $isVerified;

            return [WebhookSubscription::EVENT_RIWAYAT_PEKERJAAN_VERIFIED, $changedToVerified];
        }

        $isVerified = in_array($model->status_verifikasi, SertifikasiKompetensi::verifiedStatuses(), true);
        $changedToVerified = $isCreated
            ? $isVerified
            : $model->wasChanged('status_verifikasi') && $isVerified;

        return [WebhookSubscription::EVENT_SERTIFIKASI_VERIFIED, $changedToVerified];
    }

    private function payloadFor(DataLegalitas|RiwayatPekerjaan|SertifikasiKompetensi $model): array
    {
        if ($model instanceof DataLegalitas) {
            return [
                'entity' => 'data_legalitas',
                'id' => $model->id,
                'perawat_nik' => $model->perawat_nik,
                'status' => $model->data_status,
                'verified_at' => optional($model->data_reviewed_at)?->toIso8601String(),
            ];
        }

        if ($model instanceof RiwayatPekerjaan) {
            return [
                'entity' => 'riwayat_pekerjaan',
                'id' => $model->id,
                'perawat_nik' => $model->perawat_nik,
                'status' => $model->verifikasi_status,
                'verified_at' => optional($model->verified_at)?->toIso8601String(),
            ];
        }

        return [
            'entity' => 'sertifikasi_kompetensi',
            'id' => $model->id,
            'perawat_nik' => $model->perawat_nik,
            'status' => $model->status_verifikasi,
            'verified_at' => optional($model->verified_at ?? $model->updated_at)?->toIso8601String(),
        ];
    }
}
