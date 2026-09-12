<?php

namespace App\Services;

use App\Models\ImportBatch;
use App\Models\Perawat;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use App\Services\CsvImport\GenericCsvImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SertifikasiImportService
{
    public function __construct(
        private PerawatStatusService $statusService,
        private GenericCsvImportService $csvImportService
    ) {
    }

    /**
     * @return array<string,array{label:string,aliases?:array<int,string>}>
     */
    public function fieldDefinitions(): array
    {
        return [
            'nik' => ['label' => 'NIK', 'aliases' => ['no_ktp', 'nomor_ktp']],
            'nama_sertifikat' => ['label' => 'Nama Sertifikat', 'aliases' => ['nama_training']],
            'jumlah_jam' => ['label' => 'Jumlah Jam'],
            'nilai_kompetensi' => ['label' => 'Nilai Kompetensi'],
            'nomor_sertifikat' => ['label' => 'Nomor Sertifikat', 'aliases' => ['no_sertifikat']],
            'tanggal_terbit' => ['label' => 'Tanggal Terbit'],
            'tanggal_kadaluarsa' => ['label' => 'Tanggal Kadaluarsa', 'aliases' => ['tanggal_expired']],
            'status_verifikasi' => ['label' => 'Status Verifikasi'],
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
        string $lembagaId,
        ?User $actor = null,
        string $originalFilename = 'import-sertifikasi.csv'
    ): array {
        $result = $this->csvImportService->importFromTempFile(
            $tempFile,
            $mapping,
            $this->fieldDefinitions(),
            $this->validationRules(),
            ['nik', 'nama_sertifikat'],
            function (array $data) use ($lembagaId, $actor): string {
                $perawat = Perawat::where('nik', $data['nik'])->first();
                if (!$perawat) {
                    throw new \RuntimeException('NIK tidak ditemukan.');
                }

                $sertifikasi = DB::transaction(function () use ($data, $lembagaId, $perawat, $actor) {
                    $statusVerifikasi = $data['status_verifikasi'] ?? SertifikasiKompetensi::VERIF_PENDING;
                    $reviewed = in_array($statusVerifikasi, [
                        SertifikasiKompetensi::VERIF_VERIFIED,
                        SertifikasiKompetensi::VERIF_VALID,
                        SertifikasiKompetensi::VERIF_REJECTED,
                    ], true);

                    return SertifikasiKompetensi::updateOrCreate(
                        [
                            'perawat_nik' => $perawat->nik,
                            'lembaga_pelatihan_id' => $lembagaId,
                            'nama_sertifikat' => $data['nama_sertifikat'],
                            'nomor_sertifikat' => $data['nomor_sertifikat'] ?? null,
                        ],
                        [
                            'jumlah_jam' => $data['jumlah_jam'] ?? null,
                            'nilai_kompetensi' => $data['nilai_kompetensi'] ?? null,
                            'tanggal_terbit' => $data['tanggal_terbit'] ?? null,
                            'tanggal_kadaluarsa' => $data['tanggal_kadaluarsa'] ?? null,
                            'status_verifikasi' => $statusVerifikasi,
                            'source_system' => 'lembaga_csv',
                            'source_actor' => 'lembaga',
                            'verified_by' => $reviewed ? $actor?->id : null,
                            'verified_at' => $reviewed ? now() : null,
                            'rejected_reason' => $statusVerifikasi === SertifikasiKompetensi::VERIF_REJECTED
                                ? 'Ditolak via import CSV lembaga.'
                                : null,
                        ]
                    );
                });

                $this->statusService->refresh($perawat);

                return $sertifikasi->wasRecentlyCreated ? 'created' : 'updated';
            },
            [
                'module' => 'sertifikasi',
                'idempotency_key_resolver' => function (array $data) use ($lembagaId): ?string {
                    $nik = trim((string) ($data['nik'] ?? ''));
                    $namaSertifikat = trim((string) ($data['nama_sertifikat'] ?? ''));
                    if ($nik === '' || $namaSertifikat === '') {
                        return null;
                    }

                    $nomor = trim((string) ($data['nomor_sertifikat'] ?? ''));

                    return implode('|', [$lembagaId, $nik, $namaSertifikat, $nomor]);
                },
            ]
        );

        $batch = $this->csvImportService->createBatchLog(
            module: 'sertifikasi',
            user: $actor,
            originalFilename: $originalFilename,
            result: $result,
            context: ['lembaga_pelatihan_id' => $lembagaId]
        );

        $result['batch'] = $batch;

        return $result;
    }

    /**
     * @return array<string,mixed>
     */
    public function import(UploadedFile $file, string $lembagaId, ?User $actor = null): array
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
            lembagaId: $lembagaId,
            actor: $actor,
            originalFilename: $preview['original_filename']
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function validationRules(): array
    {
        return [
            'nik' => ['required', 'digits:16', 'exists:perawat,nik'],
            'nama_sertifikat' => ['required', 'string', 'max:255'],
            'jumlah_jam' => ['nullable', 'integer', 'min:1'],
            'nilai_kompetensi' => ['nullable', 'numeric', 'between:0,100'],
            'nomor_sertifikat' => ['nullable', 'string', 'max:100'],
            'tanggal_terbit' => ['nullable', 'date'],
            'tanggal_kadaluarsa' => ['nullable', 'date', 'after_or_equal:tanggal_terbit'],
            'status_verifikasi' => ['nullable', 'in:pending,verified,rejected,valid'],
        ];
    }
}
