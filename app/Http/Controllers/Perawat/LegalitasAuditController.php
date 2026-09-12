<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataLegalitas;
use App\Support\NotificationQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LegalitasAuditController extends Controller
{
    public function index(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(404);
        }

        $perawat->load('dataLegalitas');

        $preset = $request->query('preset');
        $status = $request->query('status', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if ($preset && !$dateFrom && !$dateTo) {
            [$dateFrom, $dateTo] = $this->resolvePresetRange($preset);
        }

        $logs = new LengthAwarePaginator([], 0, 20, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
        if ($perawat->dataLegalitas) {
            $logs = AuditLog::with('user')
                ->where('subject_type', DataLegalitas::class)
                ->where('subject_id', $perawat->dataLegalitas->id)
                ->where('action', 'legalitas.data.review')
                ->when($status && $status !== 'all', function ($query) use ($status) {
                    $query->where('meta->status', $status);
                })
                ->when($dateFrom, function ($query) use ($dateFrom) {
                    try {
                        $start = Carbon::parse($dateFrom)->startOfDay();
                        $query->where('created_at', '>=', $start);
                    } catch (\Throwable $e) {
                    }
                })
                ->when($dateTo, function ($query) use ($dateTo) {
                    try {
                        $end = Carbon::parse($dateTo)->endOfDay();
                        $query->where('created_at', '<=', $end);
                    } catch (\Throwable $e) {
                    }
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        return view('perawat.legalitas_audit', [
            'perawat' => $perawat,
            'logs' => $logs,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'preset' => $preset,
            'statusOptions' => DataLegalitas::dataStatusOptions(),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $unreadLegalitas = $request->user()->unreadNotifications();
        NotificationQuery::whereDataEquals($unreadLegalitas, 'tipe', 'legalitas_data_status');
        $unreadLegalitas->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi legalitas ditandai sudah dibaca.');
    }

    private function resolvePresetRange(string $preset): array
    {
        $today = Carbon::now();

        return match ($preset) {
            '7d' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
            '30d' => [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
            'month' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            default => [null, null],
        };
    }
}
