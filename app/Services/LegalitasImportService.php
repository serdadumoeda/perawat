<?php

namespace App\Services;

use App\Models\DataLegalitas;
use App\Models\ImportBatch;
use App\Models\Perawat;
use App\Models\User;
use App\Services\CsvImport\GenericCsvImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegalitasImportService
{
    public function __construct(
        private PerawatStatusService $statusService,
        private GenericCsvImportService $csvImportService,
        private RegulatorOrganisasiResolver $organisasiResolver
    ) {
    }

    /**
     * @return array<string,array{label:string,aliases?:array<int,string>}>
     */
    public function fieldDefinitions(): array
    {
        return [
            'nik' => ['label' => 'NIK', 'aliases' => ['no_ktp', 'nomor_ktp']],
            'nomor_str' => ['label' => 'Nomor STR', 'aliases' => ['no_str']],
            'nomor_sipp' => ['label' => 'Nomor SIPP', 'aliases' => ['no_sipp']],
            'status_str' => ['label' => 'Status STR'],
            'str_berlaku_mulai' => ['label' => 'STR Berlaku Mulai'],
            'str_berlaku_sampai' => ['label' => 'STR Berlaku Sampai'],
            'nomor_izin_luar_negeri' => ['label' => 'Nomor Izin Luar Negeri'],
            'status_izin_luar_negeri' => ['label' => 'Status Izin Luar Negeri'],
            'sumber_validasi' => ['label' => 'Sumber Validasi'],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function preview(UploadedFile $file): array
    {
        return $this->csvImportService->preview($file, $this->fieldDefinitions());
    }

    /**
     * @return array{
     *     total_rows:int,
     *     processed:int,
     *     created:int,
     *     updated:int,
     *     success_rows:int,
     *     failed_rows:int,
     *     errors:array<int,string>,
     *     error_rows:array<int,array{row:int,error:string,data:array<string,mixed>}>,
     *     mapping:array<string,string>,
     *     batch:ImportBatch
     * }
     */
    public function importFromPreview(
        string $tempFile,
        array $mapping,
        ?User $actor = null,
        string $originalFilename = 'import-legalitas.csv'
    ): array {
        $regulatorOrgId = $this->organisasiResolver->forUser($actor)?->id;

        $result = $this->csvImportService->importFromTempFile(
            $tempFile,
            $mapping,
            $this->fieldDefinitions(),
            $this->validationRules(),
            ['nik'],
            function (array $data) use ($actor, $regulatorOrgId): string {
                $perawat = Perawat::where('nik', $data['nik'])->first();
                if (!$perawat) {
                    throw new \RuntimeException('NIK tidak ditemukan.');
                }

                $legalitas = DB::transaction(function () use ($data, $perawat, $actor, $regulatorOrgId) {
                    $dataStatus = $this->resolveDataStatus(
                        $data['status_str'] ?? null,
                        $data['nomor_str'] ?? null
                    );
                    $reviewed = in_array($dataStatus, [
                        DataLegalitas::DATA_STATUS_VERIFIED,
                        DataLegalitas::DATA_STATUS_EXPIRED,
                    ], true);

                    return DataLegalitas::updateOrCreate(
                        ['perawat_nik' => $perawat->nik],
                        [
                            'nomor_str' => $data['nomor_str'] ?? null,
                            'nomor_sipp' => $data['nomor_sipp'] ?? null,
                            'status_str' => $data['status_str'] ?? null,
                            'str_berlaku_mulai' => $data['str_berlaku_mulai'] ?? null,
                            'str_berlaku_sampai' => $data['str_berlaku_sampai'] ?? null,
                            'nomor_izin_luar_negeri' => $data['nomor_izin_luar_negeri'] ?? null,
                            'status_izin_luar_negeri' => $data['status_izin_luar_negeri'] ?? null,
                            'sumber_validasi' => $data['sumber_validasi'] ?? 'regulator',
                            'source_verification' => $this->resolveSourceVerification($data['sumber_validasi'] ?? null),
                            'source_system' => $this->resolveSourceSystem($data['sumber_validasi'] ?? null),
                            'source_actor' => $this->resolveSourceActor($data['sumber_validasi'] ?? null),
                            'regulator_organisasi_id' => $regulatorOrgId,
                            'terakhir_validasi_at' => now(),
                            'data_status' => $dataStatus,
                            'data_reviewed_by' => $reviewed ? ($actor?->id) : null,
                            'data_reviewed_at' => $reviewed ? now() : null,
                        ]
                    );
                });

                $this->statusService->refresh($perawat);

                return $legalitas->wasRecentlyCreated ? 'created' : 'updated';
            },
            [
                'module' => 'legalitas',
                'idempotency_key_resolver' => static function (array $data, int $rowNumber): ?string {
                    $nik = trim((string) ($data['nik'] ?? ''));
                    return $nik !== '' ? $nik : null;
                },
            ]
        );

        $batch = $this->csvImportService->createBatchLog(
            module: 'legalitas',
            user: $actor,
            originalFilename: $originalFilename,
            result: $result
        );

        $result['batch'] = $batch;

        return $result;
    }

    /**
     * @return array<string,mixed>
     */
    public function import(UploadedFile $file, ?User $actor = null): array
    {
        $preview = $this->preview($file);
        $mapping = [];

        foreach ($preview['headers'] as $header) {
            if (!empty($header['mapped_field'])) {
                $mapping[$header['key']] = $header['mapped_field'];
            }
        }

        return $this->importFromPreview(
            tempFile: $preview['temp_file'],
            mapping: $mapping,
            actor: $actor,
            originalFilename: $preview['original_filename']
        );
    }

    private function resolveDataStatus(?string $statusStr, ?string $nomorStr): string
    {
        $statusStr = $statusStr ? strtolower(trim($statusStr)) : null;

        if ($statusStr === DataLegalitas::STATUS_STR_EXPIRED) {
            return DataLegalitas::DATA_STATUS_EXPIRED;
        }

        if ($statusStr === DataLegalitas::STATUS_STR_AKTIF) {
            return DataLegalitas::DATA_STATUS_VERIFIED;
        }

        if ($statusStr === DataLegalitas::STATUS_STR_HOLD || $nomorStr) {
            return DataLegalitas::DATA_STATUS_SUBMITTED;
        }

        return DataLegalitas::DATA_STATUS_DRAFT;
    }

    private function resolveSourceVerification(?string $source): string
    {
        $source = strtolower(trim((string) $source));
        if ($source === '') {
            return 'manual';
        }

        if (Str::contains($source, ['api', 'kemenkes', 'ktki', 'sync'])) {
            return 'api';
        }

        return 'manual';
    }

    private function resolveSourceSystem(?string $source): string
    {
        $source = strtolower(trim((string) $source));
        if ($source === '') {
            return 'regulator_portal';
        }

        if (Str::contains($source, ['api', 'kemenkes', 'ktki', 'sync'])) {
            return 'regulator_api';
        }

        if (Str::contains($source, ['perawat'])) {
            return 'perawat_portal';
        }

        return 'regulator_portal';
    }

    private function resolveSourceActor(?string $source): string
    {
        $source = strtolower(trim((string) $source));
        if ($source !== '' && Str::contains($source, ['perawat'])) {
            return 'perawat';
        }

        return 'regulator';
    }

    /**
     * @return array<string,mixed>
     */
    private function validationRules(): array
    {
        return [
            'nik' => ['required', 'digits:16', 'exists:perawat,nik'],
            'nomor_str' => ['nullable', 'string', 'max:100'],
            'nomor_sipp' => ['nullable', 'string', 'max:100'],
            'status_str' => ['nullable', 'in:aktif,hold,expired'],
            'str_berlaku_mulai' => ['nullable', 'date'],
            'str_berlaku_sampai' => ['nullable', 'date', 'after_or_equal:str_berlaku_mulai'],
            'nomor_izin_luar_negeri' => ['nullable', 'string', 'max:100'],
            'status_izin_luar_negeri' => ['nullable', 'string', 'max:50'],
            'sumber_validasi' => ['nullable', 'string', 'max:100'],
        ];
    }
}
