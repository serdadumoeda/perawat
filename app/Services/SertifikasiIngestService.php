<?php

namespace App\Services;

use App\Models\Perawat;
use App\Models\SertifikasiKompetensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SertifikasiIngestService
{
    public function __construct(private PerawatStatusService $statusService)
    {
    }

    public function ingest(array $rows, string $lembagaId): array
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
                'nama_sertifikat' => ['required', 'string', 'max:255'],
                'nomor_sertifikat' => ['nullable', 'string', 'max:100'],
                'tanggal_terbit' => ['nullable', 'date'],
                'tanggal_kadaluarsa' => ['nullable', 'date', 'after_or_equal:tanggal_terbit'],
                'status_verifikasi' => ['nullable', 'in:pending,verified,rejected,valid'],
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
                DB::transaction(function () use ($data, $lembagaId, $perawat, &$result) {
                    $statusVerifikasi = $data['status_verifikasi'] ?? SertifikasiKompetensi::VERIF_PENDING;
                    $reviewed = in_array($statusVerifikasi, [
                        SertifikasiKompetensi::VERIF_VERIFIED,
                        SertifikasiKompetensi::VERIF_VALID,
                        SertifikasiKompetensi::VERIF_REJECTED,
                    ], true);

                    $sertifikasi = SertifikasiKompetensi::updateOrCreate(
                        [
                            'perawat_nik' => $perawat->nik,
                            'lembaga_pelatihan_id' => $lembagaId,
                            'nama_sertifikat' => $data['nama_sertifikat'],
                            'nomor_sertifikat' => $data['nomor_sertifikat'] ?? null,
                        ],
                        [
                            'tanggal_terbit' => $data['tanggal_terbit'] ?? null,
                            'tanggal_kadaluarsa' => $data['tanggal_kadaluarsa'] ?? null,
                            'status_verifikasi' => $statusVerifikasi,
                            'source_system' => 'lembaga_api',
                            'source_actor' => 'lembaga',
                            'verified_by' => null,
                            'verified_at' => $reviewed ? now() : null,
                            'rejected_reason' => $statusVerifikasi === SertifikasiKompetensi::VERIF_REJECTED
                                ? 'Ditolak via ingest API lembaga.'
                                : null,
                        ]
                    );

                    $this->statusService->refresh($perawat);

                    if ($sertifikasi->wasRecentlyCreated) {
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
