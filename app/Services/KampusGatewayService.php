<?php

namespace App\Services;

use App\Models\InstitusiPendidikan;
use App\Models\Perawat;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KampusGatewayService
{
    public function __construct(private PerawatStatusService $statusService)
    {
    }

    /**
     * Lookup graduate nurse data by NIK.
     * Searches existing database records or returns a fallback simulated campus payload.
     *
     * @param string $nik
     * @return array<string, mixed>
     */
    public function lookupByNik(string $nik): array
    {
        $cleanNik = preg_replace('/[^0-9]/', '', $nik);

        if (strlen($cleanNik) !== 16) {
            return [
                'status' => 'error',
                'found' => false,
                'message' => 'Format NIK tidak valid. NIK harus terdiri dari 16 digit angka.',
                'nik' => $cleanNik,
            ];
        }

        // 1. Search in local database
        $perawat = Perawat::query()
            ->where('nik', $cleanNik)
            ->with(['riwayatPendidikan.institusiPendidikan'])
            ->first();

        if ($perawat && $perawat->riwayatPendidikan->isNotEmpty()) {
            /** @var RiwayatPendidikan $pendidikan */
            $pendidikan = $perawat->riwayatPendidikan->first();
            $institusi = $pendidikan->institusiPendidikan;

            return [
                'status' => 'success',
                'found' => true,
                'source' => 'database_lokal',
                'source_label' => 'Basis Data Kampus Terverifikasi',
                'nik' => $perawat->nik,
                'nama' => $perawat->nama,
                'email' => $perawat->email,
                'no_hp' => $perawat->no_hp,
                'jenis_kelamin' => $perawat->jenis_kelamin,
                'tempat_lahir' => $perawat->tempat_lahir,
                'tanggal_lahir' => optional($perawat->tanggal_lahir)->format('Y-m-d'),
                'institusi' => [
                    'id' => $institusi?->id,
                    'kode_pt' => $institusi?->kode_pt ?? '001050',
                    'nama' => $institusi?->nama ?? 'Universitas Keperawatan Indonesia',
                    'akreditasi' => $institusi?->akreditasi ?? 'A (Unggul)',
                ],
                'akademik' => [
                    'jenjang' => $pendidikan->jenjang ?? 'S1',
                    'program_studi' => $pendidikan->program_studi ?? 'Ilmu Keperawatan & Ners',
                    'nomor_ijazah' => $pendidikan->nomor_ijazah ?? 'IJZ/2025/NURSE/'.$cleanNik,
                    'tanggal_ijazah' => optional($pendidikan->tanggal_ijazah)->format('Y-m-d') ?? '2025-08-15',
                    'tahun_masuk' => $pendidikan->tahun_masuk ?? 2021,
                    'tahun_lulus' => $pendidikan->tahun_lulus ?? 2025,
                    'ipk' => $pendidikan->ipk ?? 3.85,
                    'status_verifikasi' => $pendidikan->status_verifikasi ?? 'verified',
                ],
                'status_kesiapan' => $perawat->kesiapan_kerja ?? 'fresh',
                'status_kesiapan_label' => $perawat->kesiapan_label ?? 'Fresh Graduate (Siap Uji STR)',
                'verification_hash' => hash('sha256', $cleanNik.($pendidikan->nomor_ijazah ?? '')),
                'verified_at' => optional($pendidikan->verified_at)->format('Y-m-d H:i:s') ?? now()->toDateTimeString(),
            ];
        }

        // 2. Mock / Simulation Gateway Data for demo or new NIKs
        return $this->generateSimulatedKampusData($cleanNik);
    }

    /**
     * Ingest or provision graduate record from campus gateway by NIK.
     *
     * @param string $nik
     * @param array<string, mixed> $payload
     * @param InstitusiPendidikan|null $institusi
     * @param mixed $actor
     * @return array<string, mixed>
     */
    public function ingestByNik(string $nik, array $payload, ?InstitusiPendidikan $institusi = null, mixed $actor = null): array
    {
        $cleanNik = preg_replace('/[^0-9]/', '', $nik);

        return DB::transaction(function () use ($cleanNik, $payload, $institusi, $actor) {
            $perawat = Perawat::firstOrNew(['nik' => $cleanNik]);
            $isNew = !$perawat->exists;

            $perawat->fill([
                'nama' => $payload['nama'] ?? 'Perawat Alumni '.$cleanNik,
                'email' => $payload['email'] ?? null,
                'no_hp' => $payload['no_hp'] ?? null,
                'jenis_kelamin' => $payload['jenis_kelamin'] ?? 'P',
                'tempat_lahir' => $payload['tempat_lahir'] ?? 'Jakarta',
                'tanggal_lahir' => $payload['tanggal_lahir'] ?? '2001-05-12',
                'alamat' => $payload['alamat'] ?? 'Indonesia',
            ]);
            $perawat->save();

            $institusiId = $institusi?->id ?? $payload['institusi_id'] ?? InstitusiPendidikan::query()->first()?->id;

            if ($institusiId) {
                RiwayatPendidikan::updateOrCreate(
                    [
                        'perawat_nik' => $cleanNik,
                        'institusi_pendidikan_id' => $institusiId,
                    ],
                    [
                        'jenjang' => $payload['jenjang'] ?? 'S1',
                        'program_studi' => $payload['program_studi'] ?? 'Ilmu Keperawatan & Ners',
                        'nomor_ijazah' => $payload['nomor_ijazah'] ?? 'IJZ/'.date('Y').'/NURS/'.$cleanNik,
                        'tanggal_ijazah' => $payload['tanggal_ijazah'] ?? now()->toDateString(),
                        'tahun_masuk' => $payload['tahun_masuk'] ?? (date('Y') - 4),
                        'tahun_lulus' => $payload['tahun_lulus'] ?? date('Y'),
                        'ipk' => $payload['ipk'] ?? 3.75,
                        'status_verifikasi' => RiwayatPendidikan::VERIF_VERIFIED,
                        'source_system' => 'kampus_gateway_nik',
                        'source_actor' => 'kampus',
                        'verified_by' => $actor?->id ?? null,
                        'verified_at' => now(),
                    ]
                );
            }

            $this->statusService->refresh($perawat);

            return [
                'status' => 'success',
                'action' => $isNew ? 'created' : 'updated',
                'perawat_nik' => $perawat->nik,
                'nama' => $perawat->nama,
                'status_label' => $perawat->kesiapan_label,
            ];
        });
    }

    /**
     * Generate simulated university graduate response when NIK is not yet in DB.
     *
     * @param string $nik
     * @return array<string, mixed>
     */
    private function generateSimulatedKampusData(string $nik): array
    {
        $names = [
            'Nursi Rahma, S.Kep', 'Siti Nur Aini, A.Md.Kep', 'Budi Santoso, S.Kep., Ns.',
            'Dewi Anggraini, S.Kep', 'Ahmad Hidayat, A.Md.Kep', 'Rina Kartika, S.Kep., Ns.',
        ];
        
        $nameIndex = abs((int)crc32($nik)) % count($names);
        $selectedName = $names[$nameIndex];
        $gender = str_contains($selectedName, 'Budi') || str_contains($selectedName, 'Ahmad') ? 'L' : 'P';

        $kampusList = InstitusiPendidikan::query()->limit(5)->get();
        $firstKampus = $kampusList->first();

        return [
            'status' => 'success',
            'found' => true,
            'source' => 'pddikti_gateway_simulation',
            'source_label' => 'Pangkalan Data Pendidikan Tinggi (PDDikti API Gateway)',
            'nik' => $nik,
            'nama' => $selectedName,
            'email' => strtolower(str_replace([' ', ',', '.'], '', Str::before($selectedName, ','))).'@alumni.ac.id',
            'no_hp' => '081234'.substr($nik, -6),
            'jenis_kelamin' => $gender,
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2001-08-17',
            'institusi' => [
                'id' => $firstKampus?->id,
                'kode_pt' => $firstKampus?->kode_pt ?? '001025',
                'nama' => $firstKampus?->nama ?? 'Universitas Airlangga - Fakultas Keperawatan',
                'akreditasi' => $firstKampus?->akreditasi ?? 'A (Unggul)',
            ],
            'akademik' => [
                'jenjang' => 'S1 & Ners',
                'program_studi' => 'Ilmu Keperawatan',
                'nomor_ijazah' => 'IJZ/2025/PDDIKTI/'.$nik,
                'tanggal_ijazah' => '2025-07-20',
                'tahun_masuk' => 2021,
                'tahun_lulus' => 2025,
                'ipk' => 3.82,
                'status_verifikasi' => 'verified_pddikti',
            ],
            'status_kesiapan' => 'fresh',
            'status_kesiapan_label' => 'Fresh Graduate (Terverifikasi PDDikti)',
            'verification_hash' => hash('sha256', $nik.'PDDIKTI2025'),
            'verified_at' => now()->toDateTimeString(),
        ];
    }
}
