<?php

namespace App\Http\Controllers\Regulator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Regulator\ImportLegalitasRequest;
use App\Http\Requests\Regulator\UpsertLegalitasRequest;
use App\Models\DataLegalitas;
use App\Models\ImportBatch;
use App\Models\Perawat;
use App\Services\AuditLogService;
use App\Services\LegalitasImportService;
use App\Services\PerawatStatusService;
use App\Services\RegulatorOrganisasiResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LegalitasController extends Controller
{
    public function create(): View
    {
        return view('regulator.legalitas.create');
    }

    public function importForm(): View
    {
        $importLogs = ImportBatch::query()
            ->where('module', 'legalitas')
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('regulator.legalitas.import', compact('importLogs'));
    }

    public function import(
        ImportLegalitasRequest $request,
        LegalitasImportService $importService,
        AuditLogService $auditLog,
        RegulatorOrganisasiResolver $organisasiResolver
    ): RedirectResponse|View
    {
        $action = $request->validated('action');
        if ($action === 'preview') {
            $preview = $importService->preview($request->file('file'));
            $importLogs = ImportBatch::query()
                ->where('module', 'legalitas')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(10)
                ->get();

            return view('regulator.legalitas.import', [
                'importLogs' => $importLogs,
                'preview' => $preview,
            ]);
        }

        $result = $importService->importFromPreview(
            tempFile: $request->validated('temp_file'),
            mapping: $request->validated('mapping', []),
            actor: $request->user(),
            originalFilename: $request->validated('original_filename') ?? 'import-legalitas.csv'
        );
        $regulatorContext = $organisasiResolver->context($request->user());

        $auditLog->log($request->user(), 'legalitas.import', null, [
            'processed' => $result['processed'],
            'created' => $result['created'],
            'updated' => $result['updated'],
            'batch_id' => $result['batch']->id,
            'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            'regulator_organisasi_nama' => $regulatorContext['regulator_organisasi_nama'],
            'regulator_organisasi_kode' => $regulatorContext['regulator_organisasi_kode'],
        ], $request);

        $errors = $result['errors'];
        if (count($errors) > 20) {
            $errors = array_slice($errors, 0, 20);
            $errors[] = 'Error lain disimpan di report batch import. Silakan unduh report.';
        }

        return redirect()
            ->route('regulator.legalitas.import')
            ->with('status', 'Import selesai. Diproses: '.$result['processed'].' | Baru: '.$result['created'].' | Update: '.$result['updated'].' | Duplikat: '.($result['duplicate_rows'] ?? 0).' | Gagal: '.$result['failed_rows'])
            ->with('import_errors', $errors)
            ->with('last_import_batch_id', $result['batch']->id);
    }

    public function downloadImportLog(Request $request, ImportBatch $importBatch): StreamedResponse
    {
        abort_unless($importBatch->module === 'legalitas', 404);
        abort_unless($importBatch->user_id === $request->user()->id, 403);
        abort_if(!$importBatch->report_path || !Storage::exists($importBatch->report_path), 404);

        $filename = 'import-legalitas-'.$importBatch->created_at->format('Ymd_His').'.csv';

        return Storage::download($importBatch->report_path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'nik',
            'nomor_str',
            'nomor_sipp',
            'status_str',
            'str_berlaku_mulai',
            'str_berlaku_sampai',
            'nomor_izin_luar_negeri',
            'status_izin_luar_negeri',
            'sumber_validasi',
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        }, 'template-legalitas.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function upsert(
        UpsertLegalitasRequest $request,
        PerawatStatusService $statusService,
        AuditLogService $auditLog,
        RegulatorOrganisasiResolver $organisasiResolver
    ): JsonResponse|RedirectResponse
    {
        $perawat = $this->resolvePerawat($request->validated());
        $existing = DataLegalitas::query()->where('perawat_nik', $perawat->nik)->first();
        if ($existing) {
            $this->authorize('review', $existing);
        }
        $regulatorContext = $organisasiResolver->context($request->user());

        $legalitas = DataLegalitas::updateOrCreate(
            ['perawat_nik' => $perawat->nik],
            [
                'nomor_str' => $request->validated('nomor_str'),
                'nomor_sipp' => $request->validated('nomor_sipp'),
                'status_str' => $request->validated('status_str'),
                'str_berlaku_mulai' => $request->validated('str_berlaku_mulai'),
                'str_berlaku_sampai' => $request->validated('str_berlaku_sampai'),
                'nomor_izin_luar_negeri' => $request->validated('nomor_izin_luar_negeri'),
                'status_izin_luar_negeri' => $request->validated('status_izin_luar_negeri'),
                'sumber_validasi' => $request->validated('sumber_validasi') ?? 'regulator',
                'source_verification' => $request->validated('sumber_validasi') ?? 'manual',
                'source_system' => 'regulator_portal',
                'source_actor' => 'regulator',
                'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
                'terakhir_validasi_at' => now(),
                'data_status' => $request->validated('status_str') === DataLegalitas::STATUS_STR_EXPIRED
                    ? DataLegalitas::DATA_STATUS_EXPIRED
                    : DataLegalitas::DATA_STATUS_VERIFIED,
                'data_reviewed_by' => $request->user()->id,
                'data_reviewed_at' => now(),
            ]
        );

        $statusService->refresh($perawat);

        $auditLog->log($request->user(), 'legalitas.upsert', $legalitas, [
            'perawat_nik' => $perawat->nik,
            'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            'regulator_organisasi_nama' => $regulatorContext['regulator_organisasi_nama'],
            'regulator_organisasi_kode' => $regulatorContext['regulator_organisasi_kode'],
        ], $request);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Data legalitas berhasil diperbarui.',
                'data_legalitas_id' => $legalitas->id,
            ]);
        }

        return redirect()
            ->route('regulator.legalitas.create')
            ->with('status', 'Data legalitas berhasil diperbarui.');
    }

    private function resolvePerawat(array $data): Perawat
    {
        $nik = $data['perawat_nik'] ?? $data['nik'] ?? null;
        if ($nik) {
            return Perawat::where('nik', $nik)->firstOrFail();
        }

        if (!empty($data['perawat_id'])) {
            return Perawat::findOrFail($data['perawat_id']);
        }

        abort(422, 'Perawat tidak ditemukan.');
    }
}
