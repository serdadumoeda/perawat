<?php

namespace App\Services;

use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;

class DataQualityMetricsService
{
    /**
     * @return array{
     *     summary:array<string,int|float>,
     *     completeness:array<int,array<string,int|float|string>>,
     *     verification:array<int,array<string,int|float|string>>,
     *     actorCoverage:array<int,array<string,int|float|string>>,
     * }
     */
    public function snapshot(): array
    {
        $totalPerawat = Perawat::query()->count();

        $withPendidikan = Perawat::query()->whereHas('riwayatPendidikan')->count();
        $withLegalitas = Perawat::query()->whereHas('dataLegalitas')->count();
        $withSertifikasiVerified = Perawat::query()->whereHas('sertifikasi', function ($query) {
            $query->whereIn('status_verifikasi', SertifikasiKompetensi::verifiedStatuses());
        })->count();
        $withPengalamanVerified = Perawat::query()->whereHas('riwayatPekerjaan', function ($query) {
            $query->where(function ($q) {
                $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                    ->orWhere('is_verified', true);
            });
        })->count();

        $readyToHire = Perawat::query()->where('status_kesiapan', Perawat::KESIAPAN_READY)->count();

        $completeness = [
            $this->coverageRow('pendidikan', 'Cakupan Pendidikan', $withPendidikan, $totalPerawat),
            $this->coverageRow('legalitas', 'Cakupan Legalitas Data', $withLegalitas, $totalPerawat),
            $this->coverageRow('sertifikasi_verified', 'Cakupan Sertifikasi Terverifikasi', $withSertifikasiVerified, $totalPerawat),
            $this->coverageRow('pengalaman_verified', 'Cakupan Pengalaman Terverifikasi', $withPengalamanVerified, $totalPerawat),
            $this->coverageRow('ready_to_hire', 'Perawat Ready to Hire', $readyToHire, $totalPerawat),
        ];

        $pendidikanTotal = RiwayatPendidikan::query()->count();
        $pendidikanVerified = RiwayatPendidikan::query()->where('status_verifikasi', RiwayatPendidikan::VERIF_VERIFIED)->count();
        $pendidikanRejected = RiwayatPendidikan::query()->where('status_verifikasi', RiwayatPendidikan::VERIF_REJECTED)->count();

        $sertifikasiTotal = SertifikasiKompetensi::query()->count();
        $sertifikasiVerified = SertifikasiKompetensi::query()
            ->whereIn('status_verifikasi', SertifikasiKompetensi::verifiedStatuses())
            ->count();
        $sertifikasiRejected = SertifikasiKompetensi::query()
            ->where('status_verifikasi', SertifikasiKompetensi::VERIF_REJECTED)
            ->count();

        $legalitasTotal = DataLegalitas::query()->count();
        $legalitasVerified = DataLegalitas::query()
            ->whereIn('data_status', [DataLegalitas::DATA_STATUS_VERIFIED, DataLegalitas::DATA_STATUS_EXPIRED])
            ->count();
        $legalitasRejected = DataLegalitas::query()
            ->where('data_status', DataLegalitas::DATA_STATUS_REJECTED)
            ->count();

        $pengalamanTotal = RiwayatPekerjaan::query()->count();
        $pengalamanVerified = RiwayatPekerjaan::query()
            ->where(function ($query) {
                $query->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                    ->orWhere('is_verified', true);
            })
            ->count();
        $pengalamanRejected = RiwayatPekerjaan::query()
            ->where('verifikasi_status', RiwayatPekerjaan::VERIF_REJECTED)
            ->count();

        $verification = [
            $this->verificationRow(
                key: 'pendidikan',
                label: 'Riwayat Pendidikan',
                total: $pendidikanTotal,
                verified: $pendidikanVerified,
                rejected: $pendidikanRejected,
            ),
            $this->verificationRow(
                key: 'sertifikasi',
                label: 'Sertifikasi Kompetensi',
                total: $sertifikasiTotal,
                verified: $sertifikasiVerified,
                rejected: $sertifikasiRejected,
            ),
            $this->verificationRow(
                key: 'legalitas_data',
                label: 'Legalitas Data (STR)',
                total: $legalitasTotal,
                verified: $legalitasVerified,
                rejected: $legalitasRejected,
            ),
            $this->verificationRow(
                key: 'pengalaman_kerja',
                label: 'Riwayat Pekerjaan',
                total: $pengalamanTotal,
                verified: $pengalamanVerified,
                rejected: $pengalamanRejected,
            ),
        ];

        $actorCoverage = $this->buildActorCoverage($totalPerawat);

        $summary = [
            'total_perawat' => $totalPerawat,
            'ready_to_hire' => $readyToHire,
            'ready_to_hire_rate' => $this->percentage($readyToHire, $totalPerawat),
            'avg_completeness_rate' => $this->averageRate($completeness),
            'avg_verified_rate' => $this->averageRate($verification),
            'legalitas_str_aktif' => DataLegalitas::query()->where('status_str', DataLegalitas::STATUS_STR_AKTIF)->count(),
            'legalitas_str_aktif_rate' => $this->percentage(
                DataLegalitas::query()->where('status_str', DataLegalitas::STATUS_STR_AKTIF)->count(),
                max(1, $legalitasTotal)
            ),
        ];

        return [
            'summary' => $summary,
            'completeness' => $completeness,
            'verification' => $verification,
            'actorCoverage' => $actorCoverage,
        ];
    }

    /**
     * @return array<string,int|float|string>
     */
    private function coverageRow(string $key, string $label, int $filled, int $population): array
    {
        $missing = max(0, $population - $filled);

        return [
            'key' => $key,
            'label' => $label,
            'population' => $population,
            'filled' => $filled,
            'missing' => $missing,
            'rate' => $this->percentage($filled, $population),
        ];
    }

    /**
     * @return array<string,int|float|string>
     */
    private function verificationRow(string $key, string $label, int $total, int $verified, int $rejected): array
    {
        $pending = max(0, $total - $verified - $rejected);

        return [
            'key' => $key,
            'label' => $label,
            'total' => $total,
            'verified' => $verified,
            'pending' => $pending,
            'rejected' => $rejected,
            'rate' => $this->percentage($verified, $total),
        ];
    }

    /**
     * @return array<int,array<string,int|float|string>>
     */
    private function buildActorCoverage(int $totalPerawat): array
    {
        $kampusRecords = RiwayatPendidikan::query()->where('source_actor', 'kampus')->count();
        $kampusPerawat = RiwayatPendidikan::query()->where('source_actor', 'kampus')->distinct('perawat_nik')->count('perawat_nik');

        $lembagaRecords = SertifikasiKompetensi::query()->where('source_actor', 'lembaga')->count();
        $lembagaPerawat = SertifikasiKompetensi::query()->where('source_actor', 'lembaga')->distinct('perawat_nik')->count('perawat_nik');

        $regulatorRecords = DataLegalitas::query()->where('source_actor', 'regulator')->count();
        $regulatorPerawat = DataLegalitas::query()->where('source_actor', 'regulator')->distinct('perawat_nik')->count('perawat_nik');

        $mitraRecords = RiwayatPekerjaan::query()->where('source_actor', 'mitra')->count();
        $mitraPerawat = RiwayatPekerjaan::query()->where('source_actor', 'mitra')->distinct('perawat_nik')->count('perawat_nik');

        $perawatRecords = DataLegalitas::query()->where('source_actor', 'perawat')->count()
            + RiwayatPekerjaan::query()->where('source_actor', 'perawat')->count()
            + SertifikasiKompetensi::query()->where('source_actor', 'perawat')->count();

        $perawatNiks = DataLegalitas::query()->where('source_actor', 'perawat')->pluck('perawat_nik')
            ->merge(RiwayatPekerjaan::query()->where('source_actor', 'perawat')->pluck('perawat_nik'))
            ->merge(SertifikasiKompetensi::query()->where('source_actor', 'perawat')->pluck('perawat_nik'))
            ->filter()
            ->unique();

        $selfPerawatCoverage = $perawatNiks->count();

        return [
            $this->actorCoverageRow('kampus', 'Kampus', $kampusRecords, $kampusPerawat, $totalPerawat),
            $this->actorCoverageRow('lembaga', 'Lembaga Pelatihan/Sertifikasi', $lembagaRecords, $lembagaPerawat, $totalPerawat),
            $this->actorCoverageRow('regulator', 'Regulator', $regulatorRecords, $regulatorPerawat, $totalPerawat),
            $this->actorCoverageRow('mitra', 'Mitra/Faskes/Headhunter', $mitraRecords, $mitraPerawat, $totalPerawat),
            $this->actorCoverageRow('perawat', 'Perawat (Self-Service)', $perawatRecords, $selfPerawatCoverage, $totalPerawat),
        ];
    }

    /**
     * @return array<string,int|float|string>
     */
    private function actorCoverageRow(string $key, string $label, int $records, int $coveredPerawat, int $totalPerawat): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'records' => $records,
            'covered_perawat' => $coveredPerawat,
            'coverage_rate' => $this->percentage($coveredPerawat, $totalPerawat),
        ];
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    /**
     * @param array<int,array<string,int|float|string>> $rows
     */
    private function averageRate(array $rows): float
    {
        if ($rows === []) {
            return 0.0;
        }

        $rates = array_map(static fn (array $row): float => (float) ($row['rate'] ?? 0), $rows);

        return round(array_sum($rates) / count($rates), 2);
    }
}
