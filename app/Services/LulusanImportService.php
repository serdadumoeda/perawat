<?php

namespace App\Services;

use App\Models\ImportBatch;
use App\Models\Perawat;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use App\Services\CsvImport\GenericCsvImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LulusanImportService
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
            'nim' => ['label' => 'NIM', 'aliases' => ['nomor_induk_mahasiswa', 'nimah', 'no_induk']],
            'nama' => ['label' => 'Nama Lengkap', 'aliases' => ['nama_lengkap']],
            'email' => ['label' => 'Email'],
            'no_hp' => ['label' => 'No HP', 'aliases' => ['nomor_hp', 'phone']],
            'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'aliases' => ['gender']],
            'tempat_lahir' => ['label' => 'Tempat Lahir'],
            'tanggal_lahir' => ['label' => 'Tanggal Lahir'],
            'alamat' => ['label' => 'Alamat'],
            'jenjang' => ['label' => 'Jenjang'],
            'program_studi' => ['label' => 'Program Studi', 'aliases' => ['jurusan']],
            'nomor_ijazah' => ['label' => 'Nomor Ijazah', 'aliases' => ['no_ijazah']],
            'tanggal_ijazah' => ['label' => 'Tanggal Ijazah', 'aliases' => ['tgl_ijazah']],
            'tahun_masuk' => ['label' => 'Tahun Masuk'],
            'tahun_lulus' => ['label' => 'Tahun Lulus'],
            'ipk' => ['label' => 'IPK'],
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
        string $institusiId,
        ?User $actor = null,
        string $originalFilename = 'import-lulusan.csv'
    ): array {
        $result = $this->csvImportService->importFromTempFile(
            $tempFile,
            $mapping,
            $this->fieldDefinitions(),
            $this->validationRules(),
            ['nama'],
            function (array $data) use ($institusiId, $actor): string {
                $rowState = DB::transaction(function () use ($data, $institusiId, $actor): string {
                    $nik = trim((string) ($data['nik'] ?? ''));
                    $nim = trim((string) ($data['nim'] ?? ''));
                    $ijazah = trim((string) ($data['nomor_ijazah'] ?? ''));

                    $perawat = null;
                    if ($nik !== '') {
                        $perawat = Perawat::where('nik', $nik)->first();
                    }
                    if (!$perawat && $nim !== '') {
                        $perawat = Perawat::where('nim', $nim)->first();
                        if (!$perawat) {
                            $perawat = Perawat::whereHas('riwayatPendidikan', function ($q) use ($institusiId, $nim) {
                                $q->where('institusi_pendidikan_id', $institusiId)->where('nim', $nim);
                            })->first();
                        }
                    }
                    if (!$perawat && !empty($data['nama']) && !empty($data['tanggal_lahir'])) {
                        $perawat = Perawat::where('nama', $data['nama'])
                            ->where('tanggal_lahir', $data['tanggal_lahir'])
                            ->first();
                    }

                    if (!$perawat) {
                        $effectiveNik = $nik !== '' ? $nik : ('TMP-'.substr(md5($institusiId.'|'.($nim ?: $ijazah ?: $data['nama'])), 0, 12));
                        $perawat = new Perawat(['nik' => $effectiveNik]);
                    } elseif ($nik !== '' && str_starts_with($perawat->nik, 'TMP-')) {
                        $perawat->nik = $nik;
                    }

                    $isNew = !$perawat->exists;

                    $perawat->fill([
                        'nim' => $nim ?: $perawat->nim,
                        'nama' => $data['nama'],
                        'email' => $data['email'] ?? $perawat->email,
                        'no_hp' => $data['no_hp'] ?? $perawat->no_hp,
                        'jenis_kelamin' => $data['jenis_kelamin'] ?? $perawat->jenis_kelamin,
                        'tempat_lahir' => $data['tempat_lahir'] ?? $perawat->tempat_lahir,
                        'tanggal_lahir' => $data['tanggal_lahir'] ?? $perawat->tanggal_lahir,
                        'alamat' => $data['alamat'] ?? $perawat->alamat,
                    ]);
                    $perawat->save();

                    RiwayatPendidikan::updateOrCreate(
                        [
                            'perawat_nik' => $perawat->nik,
                            'institusi_pendidikan_id' => $institusiId,
                        ],
                        array_merge(
                            Arr::only($data, [
                                'jenjang',
                                'program_studi',
                                'nomor_ijazah',
                                'tanggal_ijazah',
                                'tahun_masuk',
                                'tahun_lulus',
                                'ipk',
                            ]),
                            [
                                'nim' => $nim ?: null,
                                'status_verifikasi' => RiwayatPendidikan::VERIF_VERIFIED,
                                'source_system' => 'kampus_csv',
                                'source_actor' => 'kampus',
                                'verified_by' => $actor?->id,
                                'verified_at' => now(),
                                'rejected_reason' => null,
                            ]
                        )
                    );

                    $this->statusService->refresh($perawat);

                    return $isNew ? 'created' : 'updated';
                });

                return $rowState;
            },
            [
                'module' => 'lulusan',
                'idempotency_key_resolver' => function (array $data) use ($institusiId): ?string {
                    $nik = trim((string) ($data['nik'] ?? ''));
                    $nim = trim((string) ($data['nim'] ?? ''));
                    $ijazah = trim((string) ($data['nomor_ijazah'] ?? ''));
                    $key = $nik ?: $nim ?: $ijazah ?: ($data['nama'] ?? '');

                    if ($key === '') {
                        return null;
                    }

                    return $institusiId.'|'.$key;
                },
            ]
        );

        $batch = $this->csvImportService->createBatchLog(
            module: 'lulusan',
            user: $actor,
            originalFilename: $originalFilename,
            result: $result,
            context: ['institusi_pendidikan_id' => $institusiId]
        );

        $result['batch'] = $batch;

        return $result;
    }

    /**
     * @return array<string,mixed>
     */
    public function import(UploadedFile $file, string $institusiId, ?User $actor = null): array
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
            institusiId: $institusiId,
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
            'nik' => ['nullable', 'string', 'max:20'],
            'nim' => ['nullable', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'jenjang' => ['nullable', 'string', 'max:50'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'nomor_ijazah' => ['nullable', 'string', 'max:100'],
            'tanggal_ijazah' => ['nullable', 'date'],
            'tahun_masuk' => ['nullable', 'digits:4'],
            'tahun_lulus' => ['nullable', 'digits:4'],
            'ipk' => ['nullable', 'numeric', 'between:0,4.00'],
        ];
    }
}
