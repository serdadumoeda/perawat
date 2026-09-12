<?php

namespace App\Services;

use App\Models\Perawat;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LulusanIngestService
{
    public function __construct(private PerawatStatusService $statusService)
    {
    }

    public function ingest(array $rows, string $institusiId): array
    {
        $result = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $data) {
            $validator = Validator::make($data, [
                'nik' => ['required', 'digits:16'],
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
            ]);

            if ($validator->fails()) {
                $result['errors'][] = 'Baris '.($index + 1).': '.implode(', ', $validator->errors()->all());
                continue;
            }

            try {
                DB::transaction(function () use ($data, $institusiId, &$result) {
                    $perawat = Perawat::firstOrNew(['nik' => $data['nik']]);
                    $isNew = !$perawat->exists;

                    $perawat->fill([
                        'nama' => $data['nama'],
                        'email' => $data['email'] ?? null,
                        'no_hp' => $data['no_hp'] ?? null,
                        'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                        'tempat_lahir' => $data['tempat_lahir'] ?? null,
                        'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                        'alamat' => $data['alamat'] ?? null,
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
                                'status_verifikasi' => RiwayatPendidikan::VERIF_VERIFIED,
                                'source_system' => 'kampus_api',
                                'source_actor' => 'kampus',
                                'verified_by' => null,
                                'verified_at' => now(),
                                'rejected_reason' => null,
                            ]
                        )
                    );

                    $this->statusService->refresh($perawat);

                    if ($isNew) {
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
}
