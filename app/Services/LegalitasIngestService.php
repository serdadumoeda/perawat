<?php

namespace App\Services;

use App\Models\DataLegalitas;
use App\Models\Perawat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LegalitasIngestService
{
    public function __construct(private PerawatStatusService $statusService)
    {
    }

    public function ingest(array $rows, ?string $regulatorOrganisasiId = null): array
    {
        $result = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $data) {
            $validator = Validator::make($data, [
                'nik' => ['required', 'digits:16', 'exists:perawat,nik'],
                'nomor_str' => ['nullable', 'string', 'max:100'],
                'status_str' => ['nullable', 'in:aktif,hold,expired'],
                'str_berlaku_mulai' => ['nullable', 'date'],
                'str_berlaku_sampai' => ['nullable', 'date', 'after_or_equal:str_berlaku_mulai'],
                'nomor_izin_luar_negeri' => ['nullable', 'string', 'max:100'],
                'status_izin_luar_negeri' => ['nullable', 'string', 'max:50'],
                'sumber_validasi' => ['nullable', 'string', 'max:100'],
            ]);

            if ($validator->fails()) {
                $result['errors'][] = 'Baris '.($index + 1).': '.implode(', ', $validator->errors()->all());
                continue;
            }

            $perawat = Perawat::where('nik', $data['nik'])->first();
            if (!$perawat) {
                $result['errors'][] = 'Baris '.($index + 1).': NIK tidak ditemukan.';
                continue;
            }

            try {
                DB::transaction(function () use ($data, $perawat, $regulatorOrganisasiId, &$result) {
                    $resolvedStatus = $this->resolveStatusStr(
                        $data['status_str'] ?? null,
                        $data['str_berlaku_sampai'] ?? null,
                        $data['nomor_str'] ?? null
                    );
                    $resolvedDataStatus = $this->resolveDataStatus($resolvedStatus, $data['nomor_str'] ?? null);
                    $isReviewed = in_array($resolvedDataStatus, [
                        DataLegalitas::DATA_STATUS_VERIFIED,
                        DataLegalitas::DATA_STATUS_EXPIRED,
                    ], true);

                    $legalitas = DataLegalitas::updateOrCreate(
                        ['perawat_nik' => $perawat->nik],
                        [
                            'nomor_str' => $data['nomor_str'] ?? null,
                            'status_str' => $resolvedStatus,
                            'str_berlaku_mulai' => $data['str_berlaku_mulai'] ?? null,
                            'str_berlaku_sampai' => $data['str_berlaku_sampai'] ?? null,
                            'nomor_izin_luar_negeri' => $data['nomor_izin_luar_negeri'] ?? null,
                            'status_izin_luar_negeri' => $data['status_izin_luar_negeri'] ?? null,
                            'sumber_validasi' => $data['sumber_validasi'] ?? 'api',
                            'source_verification' => 'api',
                            'source_system' => 'regulator_api',
                            'source_actor' => 'regulator',
                            'regulator_organisasi_id' => $regulatorOrganisasiId,
                            'terakhir_validasi_at' => now(),
                            'data_status' => $resolvedDataStatus,
                            'data_reviewed_by' => null,
                            'data_reviewed_at' => $isReviewed ? now() : null,
                        ]
                    );

                    $this->statusService->refresh($perawat);

                    if ($legalitas->wasRecentlyCreated) {
                        $result['created']++;
                    } else {
                        $result['updated']++;
                    }
                });
            } catch (\Throwable $e) {
                $result['errors'][] = 'Baris '.($index + 1).': '.$e->getMessage();
                continue;
            }

            $result['processed']++;
        }

        return $result;
    }

    private function resolveStatusStr(?string $statusStr, mixed $strBerlakuSampai, ?string $nomorStr): ?string
    {
        if (!empty($strBerlakuSampai)) {
            try {
                $date = Carbon::parse((string) $strBerlakuSampai)->startOfDay();
                return $date->lt(now()->startOfDay())
                    ? DataLegalitas::STATUS_STR_EXPIRED
                    : DataLegalitas::STATUS_STR_AKTIF;
            } catch (\Throwable) {
                // Fallback to explicit status when tanggal tidak valid.
            }
        }

        $normalized = $statusStr ? strtolower(trim($statusStr)) : null;
        if (in_array($normalized, [
            DataLegalitas::STATUS_STR_AKTIF,
            DataLegalitas::STATUS_STR_HOLD,
            DataLegalitas::STATUS_STR_EXPIRED,
        ], true)) {
            return $normalized;
        }

        if (!$nomorStr) {
            return DataLegalitas::STATUS_STR_HOLD;
        }

        return null;
    }

    private function resolveDataStatus(?string $statusStr, ?string $nomorStr): string
    {
        if ($statusStr === DataLegalitas::STATUS_STR_EXPIRED) {
            return DataLegalitas::DATA_STATUS_EXPIRED;
        }

        if ($statusStr === DataLegalitas::STATUS_STR_AKTIF) {
            return DataLegalitas::DATA_STATUS_VERIFIED;
        }

        if (!empty($nomorStr)) {
            return DataLegalitas::DATA_STATUS_SUBMITTED;
        }

        return DataLegalitas::DATA_STATUS_DRAFT;
    }
}
