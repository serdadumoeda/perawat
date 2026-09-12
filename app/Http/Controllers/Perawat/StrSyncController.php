<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Perawat\StoreStrSyncRequest;
use App\Models\DataLegalitas;
use App\Models\StrSyncRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StrSyncController extends Controller
{
    public function create(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $latestRequest = StrSyncRequest::where('perawat_nik', $perawat->nik)->latest()->first();

        return view('perawat.str_sync.create', [
            'perawat' => $perawat,
            'legalitas' => $perawat->dataLegalitas,
            'latestRequest' => $latestRequest,
        ]);
    }

    public function store(StoreStrSyncRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $existingPending = StrSyncRequest::where('perawat_nik', $perawat->nik)
            ->where('status', StrSyncRequest::STATUS_PENDING)
            ->exists();
        if ($existingPending) {
            return back()->withErrors(['nomor_str' => 'Permintaan sinkronisasi STR masih pending.']);
        }

        $existingLegalitas = DataLegalitas::where('perawat_nik', $perawat->nik)->first();
        if ($existingLegalitas && $existingLegalitas->data_status === DataLegalitas::DATA_STATUS_VERIFIED) {
            return back()->withErrors(['nomor_str' => 'Data STR Anda sudah terverifikasi. Hubungi Dinas Kesehatan untuk pembaruan.']);
        }

        $legalitasData = [
            'nomor_str' => $request->validated('nomor_str'),
            'nomor_izin_luar_negeri' => $request->validated('nomor_izin_luar_negeri'),
            'sumber_validasi' => 'perawat',
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'data_submitted_at' => now(),
            'source_verification' => 'manual',
            'source_system' => 'perawat_portal',
            'source_actor' => 'perawat',
        ];

        if ($request->hasFile('str_document')) {
            $legalitasData['str_document_path'] = $request->file('str_document')->store('legalitas/str', 'public');
            $legalitasData['str_document_status'] = DataLegalitas::DOC_STATUS_PENDING;
            $legalitasData['str_document_reviewed_by'] = null;
            $legalitasData['str_document_reviewed_at'] = null;
            $legalitasData['str_document_note'] = null;
        }

        if ($request->hasFile('izin_luar_negeri_document')) {
            $legalitasData['izin_luar_negeri_document_path'] = $request->file('izin_luar_negeri_document')->store('legalitas/izin_luar_negeri', 'public');
            $legalitasData['izin_luar_negeri_document_status'] = DataLegalitas::DOC_STATUS_PENDING;
            $legalitasData['izin_luar_negeri_document_reviewed_by'] = null;
            $legalitasData['izin_luar_negeri_document_reviewed_at'] = null;
            $legalitasData['izin_luar_negeri_document_note'] = null;
        }

        $legalitas = DataLegalitas::updateOrCreate(['perawat_nik' => $perawat->nik], $legalitasData);

        StrSyncRequest::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => $request->validated('nomor_str'),
            'status' => StrSyncRequest::STATUS_PENDING,
            'requested_by' => $request->user()->id,
            'requested_role' => 'perawat',
            'catatan' => $request->validated('catatan'),
        ]);

        if ($request->hasFile('str_document')) {
            $auditLog->log($request->user(), 'legalitas.document.submit', $legalitas, [
                'document_type' => 'str',
                'status' => DataLegalitas::DOC_STATUS_PENDING,
            ], $request);
        }

        if ($request->hasFile('izin_luar_negeri_document')) {
            $auditLog->log($request->user(), 'legalitas.document.submit', $legalitas, [
                'document_type' => 'izin_luar_negeri',
                'status' => DataLegalitas::DOC_STATUS_PENDING,
            ], $request);
        }

        return redirect()
            ->route('perawat.str_sync.create')
            ->with('status', 'Permintaan sinkronisasi STR berhasil diajukan.');
    }
}
