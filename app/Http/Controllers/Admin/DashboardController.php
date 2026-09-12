<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerEvent;
use App\Models\DataLegalitas;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use App\Services\DataQualityMetricsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DataQualityMetricsService $dataQualityMetrics)
    {
    }

    public function index(Request $request): View
    {
        [$rangePreset, $rangeStart, $rangeEnd, $rangeLabel] = $this->resolveDateRange($request);
        $rangeStartAt = $rangeStart->copy()->startOfDay();
        $rangeEndAt = $rangeEnd->copy()->endOfDay();

        $today = now()->startOfDay();
        $in30Days = $today->copy()->addDays(30)->endOfDay();

        $statsCacheKey = 'admin_dashboard_stats_' . $rangePreset . '_' . $rangeStartAt->toDateString() . '_' . $rangeEndAt->toDateString();
        $stats = Cache::remember($statsCacheKey, 600, function () use ($rangeStartAt, $rangeEndAt, $today, $in30Days) {
            return [
                'perawat_total' => Perawat::count(),
                'perawat_ready' => Perawat::where('status_kesiapan', Perawat::KESIAPAN_READY)->count(),
                'perawat_fresh' => Perawat::where('status_ketenagakerjaan', Perawat::KETENAGAKERJAAN_FRESH)->count(),
                'perawat_bekerja' => Perawat::where('status_ketenagakerjaan', Perawat::KETENAGAKERJAAN_BEKERJA)->count(),
                'perawat_berpengalaman' => Perawat::where('status_ketenagakerjaan', Perawat::KETENAGAKERJAAN_BERPENGALAMAN)->count(),
                'str_aktif' => DataLegalitas::where('status_str', DataLegalitas::STATUS_STR_AKTIF)->count(),
                'str_hold' => DataLegalitas::where('status_str', DataLegalitas::STATUS_STR_HOLD)->count(),
                'str_expired' => DataLegalitas::where('status_str', DataLegalitas::STATUS_STR_EXPIRED)->count(),
                'lowongan_dibuka' => Lowongan::where('status', 'dibuka')->count(),
                'rekrutmen_locked' => Rekrutmen::where('status', Rekrutmen::STATUS_LOCKED)->count(),
                'rekrutmen_hired' => Rekrutmen::where('status', Rekrutmen::STATUS_HIRED)->count(),
                'lamaran_total' => Lamaran::count(),
                'lamaran_diajukan' => Lamaran::where('status', Lamaran::STATUS_DIAJUKAN)->count(),
                'lamaran_diterima' => Lamaran::where('status', Lamaran::STATUS_DITERIMA)->count(),
                'riwayat_pending' => RiwayatPekerjaan::where('verifikasi_status', RiwayatPekerjaan::VERIF_PENDING)->count(),
                'sertifikasi_pending' => SertifikasiKompetensi::where('status_verifikasi', SertifikasiKompetensi::VERIF_PENDING)->count(),
                'legalitas_pending' => DataLegalitas::whereIn('data_status', [
                    DataLegalitas::DATA_STATUS_SUBMITTED,
                    DataLegalitas::DATA_STATUS_PENDING_SYNC,
                    DataLegalitas::DATA_STATUS_DRAFT,
                ])->count(),
                'users_pending' => User::where('status', User::STATUS_PENDING)->count(),
                'str_expiring_30' => DataLegalitas::query()
                    ->whereNotNull('str_berlaku_sampai')
                    ->whereDate('str_berlaku_sampai', '>=', $today->toDateString())
                    ->whereDate('str_berlaku_sampai', '<=', $in30Days->toDateString())
                    ->count(),
                'str_without_expiry' => DataLegalitas::query()
                    ->whereNotNull('nomor_str')
                    ->whereNull('str_berlaku_sampai')
                    ->count(),
                'movement_perawat_baru' => Perawat::query()
                    ->whereBetween('created_at', [$rangeStartAt, $rangeEndAt])
                    ->count(),
                'movement_legalitas_reviewed' => DataLegalitas::query()
                    ->whereNotNull('data_reviewed_at')
                    ->whereBetween('data_reviewed_at', [$rangeStartAt, $rangeEndAt])
                    ->count(),
                'movement_pengalaman_verified' => RiwayatPekerjaan::query()
                    ->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                    ->whereNotNull('verified_at')
                    ->whereBetween('verified_at', [$rangeStartAt, $rangeEndAt])
                    ->count(),
                'movement_hired' => Rekrutmen::query()
                    ->whereNotNull('hired_at')
                    ->whereBetween('hired_at', [$rangeStartAt, $rangeEndAt])
                    ->count(),
                'movement_events' => CareerEvent::query()
                    ->whereBetween('happened_at', [$rangeStartAt, $rangeEndAt])
                    ->count(),
            ];
        });

        $expiringCacheKey = 'admin_dashboard_expiring_' . $today->toDateString() . '_' . $in30Days->toDateString();
        $strExpiringSoon = Cache::remember($expiringCacheKey, 600, function () use ($today, $in30Days) {
            return DataLegalitas::query()
                ->with('perawat:id,nik,nama')
                ->whereNotNull('str_berlaku_sampai')
                ->whereDate('str_berlaku_sampai', '>=', $today->toDateString())
                ->whereDate('str_berlaku_sampai', '<=', $in30Days->toDateString())
                ->orderBy('str_berlaku_sampai')
                ->limit(10)
                ->get();
        });

        $expiredRecentCacheKey = 'admin_dashboard_expired_' . $today->toDateString();
        $strExpiredRecent = Cache::remember($expiredRecentCacheKey, 600, function () use ($today) {
            return DataLegalitas::query()
                ->with('perawat:id,nik,nama')
                ->whereNotNull('str_berlaku_sampai')
                ->whereDate('str_berlaku_sampai', '<', $today->toDateString())
                ->orderByDesc('str_berlaku_sampai')
                ->limit(10)
                ->get();
        });

        $trendCacheKey = 'admin_dashboard_trend_' . $rangeStartAt->toDateString() . '_' . $rangeEndAt->toDateString();
        $trendRows = Cache::remember($trendCacheKey, 600, function () use ($rangeStart, $rangeEnd) {
            return $this->buildMovementTrendRows($rangeStart, $rangeEnd);
        });
        $trendMax = max(1, collect($trendRows)->max('total') ?? 0);

        $latestEventsCacheKey = 'admin_dashboard_latest_events_' . $rangeStartAt->toDateString() . '_' . $rangeEndAt->toDateString();
        $latestEvents = Cache::remember($latestEventsCacheKey, 600, function () use ($rangeStartAt, $rangeEndAt) {
            return CareerEvent::query()
                ->with('perawat:id,nik,nama')
                ->whereBetween('happened_at', [$rangeStartAt, $rangeEndAt])
                ->latest('happened_at')
                ->limit(12)
                ->get();
        });

        $qualitySummary = Cache::remember('admin_dashboard_quality_summary', 600, function () {
            $qualitySnapshot = $this->dataQualityMetrics->snapshot();
            return $qualitySnapshot['summary'];
        });

        return view('admin.dashboard', compact(
            'stats',
            'strExpiringSoon',
            'strExpiredRecent',
            'trendRows',
            'trendMax',
            'latestEvents',
            'qualitySummary',
            'rangePreset',
            'rangeStart',
            'rangeEnd',
            'rangeLabel'
        ));
    }

    /**
     * @return array{0:string,1:Carbon,2:Carbon,3:string}
     */
    private function resolveDateRange(Request $request): array
    {
        $today = now()->startOfDay();
        $preset = (string) $request->query('range', '30d');
        $start = null;
        $end = null;
        $label = '';

        if ($preset === '7d') {
            $start = $today->copy()->subDays(6);
            $end = $today->copy();
            $label = '7 Hari Terakhir';
        } elseif ($preset === 'this_month') {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth()->startOfDay();
            $label = 'Bulan Ini';
        } elseif ($preset === 'custom') {
            $startInput = (string) $request->query('start_date');
            $endInput = (string) $request->query('end_date');

            try {
                $start = Carbon::parse($startInput)->startOfDay();
                $end = Carbon::parse($endInput)->startOfDay();
            } catch (\Throwable) {
                $preset = '30d';
            }

            if ($start && $end) {
                if ($end->lt($start)) {
                    [$start, $end] = [$end, $start];
                }

                $maxDays = 120;
                if ($start->diffInDays($end) > $maxDays) {
                    $start = $end->copy()->subDays($maxDays - 1);
                }

                $label = $start->format('d M Y').' - '.$end->format('d M Y');
            }
        }

        if (!$start || !$end) {
            $preset = '30d';
            $start = $today->copy()->subDays(29);
            $end = $today->copy();
            $label = '30 Hari Terakhir';
        }

        return [$preset, $start, $end, $label];
    }

    /**
     * @return array<int,array<string,int|string>>
     */
    private function buildMovementTrendRows(Carbon $startDate, Carbon $endDate): array
    {
        $raw = CareerEvent::query()
            ->selectRaw('DATE(happened_at) as day, category, COUNT(*) as total')
            ->whereBetween('happened_at', [$startDate, $endDate->copy()->endOfDay()])
            ->groupBy('day', 'category')
            ->get();

        $counter = [];
        foreach ($raw as $item) {
            $day = (string) $item->day;
            $category = (string) $item->category;
            $counter[$day][$category] = (int) $item->total;
        }

        $rows = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $day = $cursor->toDateString();
            $pendidikan = $counter[$day]['pendidikan'] ?? 0;
            $sertifikasi = $counter[$day]['sertifikasi'] ?? 0;
            $legalitas = $counter[$day]['legalitas'] ?? 0;
            $pengalaman = $counter[$day]['pengalaman'] ?? 0;

            $rows[] = [
                'day' => $day,
                'label' => $cursor->format('d M'),
                'pendidikan' => $pendidikan,
                'sertifikasi' => $sertifikasi,
                'legalitas' => $legalitas,
                'pengalaman' => $pengalaman,
                'total' => $pendidikan + $sertifikasi + $legalitas + $pengalaman,
            ];

            $cursor->addDay();
        }

        return $rows;
    }
}
