<?php

namespace App\Http\Controllers\Kampus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kampus\UpsertTracingAlumniRequest;
use App\Models\InstitusiPendidikan;
use App\Models\Perawat;
use App\Models\TracingAlumni;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class TracingAlumniController extends Controller
{
    public function index(Request $request): View
    {
        $institusiList = $request->user()->institusiPendidikan()->orderBy('nama')->get();
        $selectedId = $request->query('institusi_id') ?: $institusiList->first()?->id;

        $selectedInstitusi = null;
        $perawat = new LengthAwarePaginator([], 0, 15);

        if ($selectedId) {
            $selectedInstitusi = InstitusiPendidikan::findOrFail($selectedId);
            $this->authorize('manage', $selectedInstitusi);

            $query = Perawat::query()
                ->whereHas('riwayatPendidikan', function ($q) use ($selectedId) {
                    $q->where('institusi_pendidikan_id', $selectedId);
                })
                ->with(['tracingAlumni' => function ($q) use ($selectedId) {
                    $q->where('institusi_pendidikan_id', $selectedId);
                }])
                ->orderBy('nama');

            if ($request->filled('q')) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('nik', 'like', $term);
                });
            }

            if ($request->filled('status')) {
                $status = $request->string('status')->toString();
                $query->whereHas('tracingAlumni', function ($q) use ($selectedId, $status) {
                    $q->where('institusi_pendidikan_id', $selectedId)
                        ->where('status', $status);
                });
            }

            $perawat = $query->paginate(15)->withQueryString();
        }

        return view('kampus.tracing.index', [
            'institusiList' => $institusiList,
            'selectedInstitusi' => $selectedInstitusi,
            'perawat' => $perawat,
            'statusOptions' => TracingAlumni::STATUSES,
        ]);
    }

    public function edit(Request $request, Perawat $perawat): View
    {
        $institusiList = $request->user()->institusiPendidikan()->orderBy('nama')->get();
        $selectedId = $request->query('institusi_id') ?: $institusiList->first()?->id;

        if (!$selectedId) {
            abort(404, 'Institusi tidak ditemukan.');
        }

        $institusi = InstitusiPendidikan::findOrFail($selectedId);
        $this->authorize('manage', $institusi);

        $belongs = $perawat->riwayatPendidikan()
            ->where('institusi_pendidikan_id', $institusi->id)
            ->exists();
        if (!$belongs) {
            abort(403, 'Perawat tidak terdaftar pada institusi ini.');
        }

        $tracing = TracingAlumni::firstOrNew([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
        ]);

        return view('kampus.tracing.edit', [
            'institusiList' => $institusiList,
            'selectedInstitusi' => $institusi,
            'perawat' => $perawat,
            'tracing' => $tracing,
            'statusOptions' => TracingAlumni::STATUSES,
        ]);
    }

    public function update(
        UpsertTracingAlumniRequest $request,
        Perawat $perawat,
        AuditLogService $auditLog
    ): RedirectResponse {
        $institusiId = $request->validated('institusi_pendidikan_id');
        $institusi = InstitusiPendidikan::findOrFail($institusiId);
        $this->authorize('manage', $institusi);

        $belongs = $perawat->riwayatPendidikan()
            ->where('institusi_pendidikan_id', $institusiId)
            ->exists();
        if (!$belongs) {
            abort(403, 'Perawat tidak terdaftar pada institusi ini.');
        }

        $tracing = TracingAlumni::updateOrCreate(
            [
                'perawat_nik' => $perawat->nik,
                'institusi_pendidikan_id' => $institusiId,
            ],
            [
                'status' => $request->validated('status'),
                'instansi' => $request->validated('instansi'),
                'posisi' => $request->validated('posisi'),
                'kota' => $request->validated('kota'),
                'provinsi' => $request->validated('provinsi'),
                'tanggal_mulai' => $request->validated('tanggal_mulai'),
                'catatan' => $request->validated('catatan'),
                'updated_by' => $request->user()->id,
            ]
        );

        $auditLog->log($request->user(), 'tracing_alumni.upsert', $tracing, [
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusiId,
        ], $request);

        return redirect()
            ->route('kampus.tracing.index', ['institusi_id' => $institusiId])
            ->with('status', 'Tracing alumni berhasil disimpan.');
    }

    public function analytics(Request $request): View
    {
        $institusiList = $request->user()->institusiPendidikan()->orderBy('nama')->get();
        $selectedId = $request->query('institusi_id') ?: $institusiList->first()?->id;

        $selectedInstitusi = null;
        $summary = [
            'total_alumni' => 0,
            'traced' => 0,
            'not_traced' => 0,
        ];
        $statusCounts = collect();
        $topInstansi = collect();
        $topKota = collect();
        $topProvinsi = collect();

        if ($selectedId) {
            $selectedInstitusi = InstitusiPendidikan::findOrFail($selectedId);
            $this->authorize('manage', $selectedInstitusi);

            $alumniQuery = Perawat::query()
                ->whereHas('riwayatPendidikan', function ($q) use ($selectedId) {
                    $q->where('institusi_pendidikan_id', $selectedId);
                });

            $summary['total_alumni'] = $alumniQuery->count();

            $tracingQuery = TracingAlumni::query()
                ->where('institusi_pendidikan_id', $selectedId);

            $summary['traced'] = $tracingQuery->count();
            $summary['not_traced'] = max(0, $summary['total_alumni'] - $summary['traced']);

            $statusCounts = $tracingQuery
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $topInstansi = $tracingQuery
                ->whereNotNull('instansi')
                ->select('instansi', DB::raw('COUNT(*) as total'))
                ->groupBy('instansi')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $topKota = $tracingQuery
                ->whereNotNull('kota')
                ->select('kota', DB::raw('COUNT(*) as total'))
                ->groupBy('kota')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $topProvinsi = $tracingQuery
                ->whereNotNull('provinsi')
                ->select('provinsi', DB::raw('COUNT(*) as total'))
                ->groupBy('provinsi')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        }

        return view('kampus.tracing.analytics', [
            'institusiList' => $institusiList,
            'selectedInstitusi' => $selectedInstitusi,
            'summary' => $summary,
            'statusCounts' => $statusCounts,
            'topInstansi' => $topInstansi,
            'topKota' => $topKota,
            'topProvinsi' => $topProvinsi,
        ]);
    }
}
