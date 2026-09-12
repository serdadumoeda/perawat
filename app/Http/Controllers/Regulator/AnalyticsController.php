<?php

namespace App\Http\Controllers\Regulator;

use App\Http\Controllers\Controller;
use App\Models\DataLegalitas;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $totalPerawat = Perawat::count();
        $ready = Perawat::where('status_kesiapan', Perawat::KESIAPAN_READY)->count();
        $fresh = Perawat::where('status_ketenagakerjaan', Perawat::KETENAGAKERJAAN_FRESH)->count();
        $berpengalaman = Perawat::where('status_ketenagakerjaan', Perawat::KETENAGAKERJAAN_BERPENGALAMAN)->count();
        $bekerja = Perawat::where('status_ketenagakerjaan', Perawat::KETENAGAKERJAAN_BEKERJA)->count();

        $strAktif = DataLegalitas::where('status_str', DataLegalitas::STATUS_STR_AKTIF)->count();
        $strHold = DataLegalitas::where('status_str', DataLegalitas::STATUS_STR_HOLD)->count();
        $strExpired = DataLegalitas::where('status_str', DataLegalitas::STATUS_STR_EXPIRED)->count();

        $lowonganOpen = Lowongan::where('status', 'dibuka')->count();
        $lowonganDomestik = Lowongan::where('status', 'dibuka')
            ->where('tipe_penempatan', Lowongan::PENEMPATAN_DOMESTIK)
            ->count();
        $lowonganLuarNegeri = Lowongan::where('status', 'dibuka')
            ->where('tipe_penempatan', Lowongan::PENEMPATAN_LUAR_NEGERI)
            ->count();

        $gap = $lowonganOpen - $ready;
        $ratio = $lowonganOpen > 0 ? round($ready / $lowonganOpen, 2) : null;

        $range = $this->monthRange(6);
        $start = $range['start'];
        $monthKeys = $range['keys'];
        $monthLabels = $range['labels'];

        $lowonganTrend = $this->buildTrend(
            Lowongan::where('created_at', '>=', $start)->get(['id', 'published_at', 'created_at']),
            $monthKeys,
            function (Lowongan $item) {
                return optional($item->published_at ?? $item->created_at)->format('Y-m');
            }
        );

        $lamaranTrend = $this->buildTrend(
            Lamaran::where('created_at', '>=', $start)->get(['id', 'created_at']),
            $monthKeys,
            fn ($item) => $item->created_at->format('Y-m')
        );

        $hiredTrend = $this->buildTrend(
            Rekrutmen::whereNotNull('hired_at')->where('hired_at', '>=', $start)->get(['id', 'hired_at']),
            $monthKeys,
            fn ($item) => $item->hired_at->format('Y-m')
        );

        $lulusanTrend = $this->buildTrend(
            RiwayatPendidikan::where(function ($q) use ($start) {
                $q->where('tanggal_ijazah', '>=', $start)->orWhere('created_at', '>=', $start);
            })->get(['id', 'tanggal_ijazah', 'created_at']),
            $monthKeys,
            function ($item) {
                $date = $item->tanggal_ijazah ?? $item->created_at;
                return $date->format('Y-m');
            }
        );

        $sertifikasiTrend = $this->buildTrend(
            SertifikasiKompetensi::where('created_at', '>=', $start)
                ->whereIn('status_verifikasi', SertifikasiKompetensi::verifiedStatuses())
                ->get(['id', 'created_at']),
            $monthKeys,
            fn ($item) => $item->created_at->format('Y-m')
        );

        $topLowonganProvinsi = $this->topCounts(Lowongan::where('status', 'dibuka'), 'provinsi_penempatan', 5);
        $topPerawatProvinsi = $this->topCounts(Perawat::query(), 'provinsi', 5);
        $topSertifikat = $this->topSertifikatDemand(5);

        return view('regulator.analytics.index', [
            'summary' => [
                'total_perawat' => $totalPerawat,
                'ready_to_hire' => $ready,
                'fresh' => $fresh,
                'berpengalaman' => $berpengalaman,
                'bekerja' => $bekerja,
                'str_aktif' => $strAktif,
                'str_hold' => $strHold,
                'str_expired' => $strExpired,
                'lowongan_open' => $lowonganOpen,
                'lowongan_domestik' => $lowonganDomestik,
                'lowongan_luar_negeri' => $lowonganLuarNegeri,
                'gap' => $gap,
                'ratio' => $ratio,
            ],
            'monthKeys' => $monthKeys,
            'monthLabels' => $monthLabels,
            'trends' => [
                'lowongan' => $lowonganTrend,
                'lamaran' => $lamaranTrend,
                'hired' => $hiredTrend,
                'lulusan' => $lulusanTrend,
                'sertifikasi' => $sertifikasiTrend,
            ],
            'topLowonganProvinsi' => $topLowonganProvinsi,
            'topPerawatProvinsi' => $topPerawatProvinsi,
            'topSertifikat' => $topSertifikat,
        ]);
    }

    private function monthRange(int $months): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $keys = [];
        $labels = [];

        for ($i = 0; $i < $months; $i++) {
            $date = (clone $start)->addMonths($i);
            $key = $date->format('Y-m');
            $keys[] = $key;
            $labels[$key] = $date->format('M Y');
        }

        return [
            'start' => $start,
            'keys' => $keys,
            'labels' => $labels,
        ];
    }

    private function buildTrend(Collection $items, array $monthKeys, callable $resolver): array
    {
        $counts = array_fill_keys($monthKeys, 0);

        foreach ($items as $item) {
            $key = $resolver($item);
            if (isset($counts[$key])) {
                $counts[$key]++;
            }
        }

        return $counts;
    }

    private function topCounts($query, string $column, int $limit): array
    {
        $items = $query->whereNotNull($column)->pluck($column);
        $counts = [];

        foreach ($items as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        arsort($counts);

        return array_slice($counts, 0, $limit, true);
    }

    private function topSertifikatDemand(int $limit): array
    {
        $counts = [];

        Lowongan::where('status', 'dibuka')->get(['kriteria'])->each(function (Lowongan $lowongan) use (&$counts) {
            $kriteria = $lowongan->kriteria ?? [];
            $raw = $kriteria['sertifikat'] ?? '';
            if (!is_string($raw) || trim($raw) === '') {
                return;
            }

            $items = preg_split('/[,;]+/', $raw);
            foreach ($items as $item) {
                $name = trim($item);
                if ($name === '') {
                    continue;
                }
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        });

        arsort($counts);

        return array_slice($counts, 0, $limit, true);
    }
}
