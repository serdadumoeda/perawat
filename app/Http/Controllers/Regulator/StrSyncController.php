<?php

namespace App\Http\Controllers\Regulator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Regulator\RejectStrSyncRequest;
use App\Http\Requests\Regulator\SyncStrRequest;
use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Models\StrSyncRequest;
use App\Services\PerawatStatusService;
use App\Services\RegulatorOrganisasiResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StrSyncController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', StrSyncRequest::STATUS_PENDING);

        $requests = StrSyncRequest::with('perawat')
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('regulator.str_sync.index', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    public function syncAll(
        Request $request,
        PerawatStatusService $statusService,
        RegulatorOrganisasiResolver $organisasiResolver
    ): RedirectResponse
    {
        $regulatorOrgId = $organisasiResolver->forUser($request->user())?->id;
        $pending = StrSyncRequest::where('status', StrSyncRequest::STATUS_PENDING)->get();
        $processed = 0;

        foreach ($pending as $syncRequest) {
            $perawat = $syncRequest->perawat
                ?? ($syncRequest->perawat_nik
                    ? Perawat::where('nik', $syncRequest->perawat_nik)->first()
                    : null);
            if (!$perawat) {
                continue;
            }

            DataLegalitas::updateOrCreate(
                ['perawat_nik' => $perawat->nik],
                [
                    'nomor_str' => $syncRequest->nomor_str,
                    'status_str' => DataLegalitas::STATUS_STR_AKTIF,
                    'str_berlaku_mulai' => now()->toDateString(),
                    'str_berlaku_sampai' => now()->addYears(5)->toDateString(),
                    'sumber_validasi' => 'regulator',
                    'source_verification' => 'manual',
                    'source_system' => 'regulator_portal',
                    'source_actor' => 'regulator',
                    'regulator_organisasi_id' => $regulatorOrgId,
                    'terakhir_validasi_at' => now(),
                    'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
                    'data_reviewed_by' => $request->user()->id,
                    'data_reviewed_at' => now(),
                ]
            );

            $syncRequest->update([
                'status' => StrSyncRequest::STATUS_SYNCED,
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
                'response_note' => 'Auto-sync oleh dinas (mock).',
            ]);

            $statusService->refresh($perawat);
            $processed++;
        }

        return redirect()
            ->route('regulator.str_sync.index', ['status' => StrSyncRequest::STATUS_PENDING])
            ->with('status', 'Auto-sync diproses: '.$processed.' permintaan.');
    }

    public function sync(
        SyncStrRequest $request,
        StrSyncRequest $syncRequest,
        PerawatStatusService $statusService,
        RegulatorOrganisasiResolver $organisasiResolver
    ): RedirectResponse
    {
        $regulatorOrgId = $organisasiResolver->forUser($request->user())?->id;
        $perawat = $syncRequest->perawat
            ?? ($syncRequest->perawat_nik
                ? Perawat::where('nik', $syncRequest->perawat_nik)->firstOrFail()
                : abort(404));

        $legalitas = DataLegalitas::updateOrCreate(
            ['perawat_nik' => $perawat->nik],
            [
                'nomor_str' => $syncRequest->nomor_str,
                'nomor_sipp' => $request->validated('nomor_sipp'),
                'status_str' => $request->validated('status_str'),
                'str_berlaku_mulai' => $request->validated('str_berlaku_mulai'),
                'str_berlaku_sampai' => $request->validated('str_berlaku_sampai'),
                'nomor_izin_luar_negeri' => $request->validated('nomor_izin_luar_negeri'),
                'status_izin_luar_negeri' => $request->validated('status_izin_luar_negeri'),
                'sumber_validasi' => 'regulator',
                'source_verification' => 'manual',
                'source_system' => 'regulator_portal',
                'source_actor' => 'regulator',
                'regulator_organisasi_id' => $regulatorOrgId,
                'terakhir_validasi_at' => now(),
                'data_status' => $request->validated('status_str') === DataLegalitas::STATUS_STR_EXPIRED
                    ? DataLegalitas::DATA_STATUS_EXPIRED
                    : DataLegalitas::DATA_STATUS_VERIFIED,
                'data_reviewed_by' => $request->user()->id,
                'data_reviewed_at' => now(),
            ]
        );

        $syncRequest->update([
            'status' => StrSyncRequest::STATUS_SYNCED,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'response_note' => $request->validated('response_note'),
        ]);

        $statusService->refresh($perawat);

        return redirect()
            ->route('regulator.str_sync.index', ['status' => StrSyncRequest::STATUS_PENDING])
            ->with('status', 'Sinkronisasi STR berhasil diproses.');
    }

    public function reject(RejectStrSyncRequest $request, StrSyncRequest $syncRequest): RedirectResponse
    {
        $syncRequest->update([
            'status' => StrSyncRequest::STATUS_REJECTED,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'response_note' => $request->validated('response_note'),
        ]);

        $legalitas = DataLegalitas::where('perawat_nik', $syncRequest->perawat_nik)->first();
        if ($legalitas && $legalitas->data_status === DataLegalitas::DATA_STATUS_SUBMITTED) {
            $legalitas->update([
                'data_status' => DataLegalitas::DATA_STATUS_REJECTED,
                'data_reviewed_by' => $request->user()->id,
                'data_reviewed_at' => now(),
                'data_rejection_note' => $request->validated('response_note'),
            ]);
        }

        return redirect()
            ->route('regulator.str_sync.index', ['status' => StrSyncRequest::STATUS_PENDING])
            ->with('status', 'Permintaan STR ditolak.');
    }
}
