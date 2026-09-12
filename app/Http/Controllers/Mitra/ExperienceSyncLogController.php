<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Jobs\SyncExperienceJob;
use App\Models\ExperienceSyncSetting;
use App\Models\RiwayatPekerjaanSync;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExperienceSyncLogController extends Controller
{
    public function index(Request $request): View
    {
        $mitraList = $request->user()->mitraPengguna()->orderBy('nama')->get();
        $selectedId = $request->query('mitra_id') ?: $mitraList->first()?->id;
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($selectedId) {
            $this->ensureMitraAccess($request, $selectedId);
        }

        $logsQuery = $this->buildLogQuery($selectedId, $status, $search, $startDate, $endDate)
            ->orderByDesc('created_at');

        $logs = $logsQuery->paginate(20)->withQueryString();

        $statsBase = RiwayatPekerjaanSync::query()
            ->when($selectedId, function ($q) use ($selectedId) {
                $q->where('mitra_pengguna_id', $selectedId);
            });

        $stats = (clone $statsBase)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'total' => (clone $statsBase)->count(),
            'success' => $stats[RiwayatPekerjaanSync::STATUS_SUCCESS] ?? 0,
            'failed' => $stats[RiwayatPekerjaanSync::STATUS_FAILED] ?? 0,
            'pending' => $stats[RiwayatPekerjaanSync::STATUS_PENDING] ?? 0,
        ];

        return view('mitra.experience_sync.logs', [
            'mitraList' => $mitraList,
            'selectedId' => $selectedId,
            'status' => $status,
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'logs' => $logs,
        ]);
    }

    public function syncNow(Request $request): RedirectResponse
    {
        $mitraId = (string) $request->input('mitra_id');
        if ($mitraId === '') {
            return back()->with('status', 'Pilih mitra terlebih dahulu untuk menjalankan sync.');
        }

        $this->ensureMitraAccess($request, $mitraId);

        $setting = ExperienceSyncSetting::where('mitra_pengguna_id', $mitraId)->first();
        if (!$setting || !$setting->is_active) {
            return back()->with('status', 'Sync belum aktif. Aktifkan terlebih dahulu di pengaturan.');
        }

        SyncExperienceJob::dispatch($mitraId);

        return back()->with('status', 'Job sync pengalaman kerja telah dikirim.');
    }

    public function export(Request $request): StreamedResponse
    {
        $mitraId = $request->query('mitra_id');
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($mitraId) {
            $this->ensureMitraAccess($request, $mitraId);
        }

        $filename = 'log-sync-pengalaman-' . now()->format('Ymd_His') . '.csv';

        $query = $this->buildLogQuery($mitraId, $status, $search, $startDate, $endDate)
            ->orderByDesc('created_at');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Waktu',
                'Mitra',
                'Perawat',
                'NIK',
                'Status',
                'Sumber',
                'External ID',
                'Last Synced',
                'Payload',
                'Error',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $log) {
                    fputcsv($handle, [
                        optional($log->created_at)->format('Y-m-d H:i:s'),
                        $log->mitraPengguna?->nama ?? '',
                        $log->perawat?->nama ?? '',
                        $log->perawat?->nik ?? '',
                        $log->status,
                        $log->source_system,
                        $log->external_id,
                        optional($log->last_synced_at)->format('Y-m-d H:i:s'),
                        json_encode($log->payload, JSON_UNESCAPED_UNICODE),
                        $log->error_message,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildLogQuery(?string $mitraId, ?string $status, string $search, ?string $startDate, ?string $endDate)
    {
        $dateRange = $this->parseDateRange($startDate, $endDate);

        return RiwayatPekerjaanSync::query()
            ->with(['perawat', 'mitraPengguna'])
            ->when($mitraId, function ($q) use ($mitraId) {
                $q->where('mitra_pengguna_id', $mitraId);
            })
            ->when($status && in_array($status, [
                RiwayatPekerjaanSync::STATUS_SUCCESS,
                RiwayatPekerjaanSync::STATUS_FAILED,
                RiwayatPekerjaanSync::STATUS_PENDING,
            ], true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('external_id', 'like', "%{$search}%")
                        ->orWhere('source_system', 'like', "%{$search}%")
                        ->orWhereHas('perawat', function ($perawatQuery) use ($search) {
                            $perawatQuery->where('nama', 'like', "%{$search}%")
                                ->orWhere('nik', 'like', "%{$search}%");
                        })
                        ->orWhereHas('mitraPengguna', function ($mitraQuery) use ($search) {
                            $mitraQuery->where('nama', 'like', "%{$search}%");
                        });
                });
            })
            ->when($dateRange !== null, function ($q) use ($dateRange) {
                $q->whereBetween('created_at', $dateRange);
            });
    }

    private function parseDateRange(?string $startDate, ?string $endDate): ?array
    {
        if (!$startDate && !$endDate) {
            return null;
        }

        try {
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
            $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
        } catch (\Throwable $e) {
            return null;
        }

        if ($start && $end) {
            return [$start, $end];
        }

        if ($start) {
            return [$start, $start->copy()->endOfDay()];
        }

        if ($end) {
            return [$end->copy()->startOfDay(), $end];
        }

        return null;
    }

    private function ensureMitraAccess(Request $request, string $mitraId): void
    {
        $exists = $request->user()
            ->mitraPengguna()
            ->where('mitra_pengguna.id', $mitraId)
            ->exists();

        if (!$exists) {
            abort(403, 'Mitra tidak sesuai dengan akun ini.');
        }
    }
}
