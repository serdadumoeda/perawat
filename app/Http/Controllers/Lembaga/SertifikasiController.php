<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lembaga\ImportSertifikasiRequest;
use App\Http\Requests\Lembaga\StoreSertifikasiRequest;
use App\Http\Requests\Lembaga\VerifySertifikasiRequest;
use App\Models\ImportBatch;
use App\Models\LembagaPelatihan;
use App\Models\Perawat;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use App\Notifications\SertifikasiStatusNotification;
use App\Services\AuditLogService;
use App\Services\PerawatStatusService;
use App\Services\SertifikasiImportService;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SertifikasiController extends Controller
{
    public function create(): View
    {
        $lembaga = auth()->user()->lembagaPelatihan()->get();

        return view('lembaga.sertifikasi.create', compact('lembaga'));
    }

    public function importForm(): View
    {
        $lembaga = auth()->user()->lembagaPelatihan()->get();
        $importLogs = ImportBatch::query()
            ->where('module', 'sertifikasi')
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('lembaga.sertifikasi.import', [
            'lembaga' => $lembaga,
            'importLogs' => $importLogs,
        ]);
    }

    public function pending(): View
    {
        $lembagaIds = auth()->user()->lembagaPelatihan()->pluck('lembaga_pelatihan.id');

        $sertifikasi = SertifikasiKompetensi::with(['perawat', 'lembagaPelatihan', 'verifiedBy'])
            ->whereIn('lembaga_pelatihan_id', $lembagaIds)
            ->where(function ($q) {
                $q->whereNull('status_verifikasi')
                    ->orWhere('status_verifikasi', SertifikasiKompetensi::VERIF_PENDING);
            })
            ->latest()
            ->paginate(15);

        return view('lembaga.sertifikasi.pending', compact('sertifikasi'));
    }

    public function import(ImportSertifikasiRequest $request, SertifikasiImportService $importService, AuditLogService $auditLog): RedirectResponse|View
    {
        $lembagaId = $request->validated('lembaga_pelatihan_id');
        $lembaga = LembagaPelatihan::findOrFail($lembagaId);
        $this->authorize('manage', $lembaga);

        $action = $request->validated('action');
        if ($action === 'preview') {
            $preview = $importService->preview($request->file('file'));
            $lembagaList = $request->user()->lembagaPelatihan()->get();
            $importLogs = ImportBatch::query()
                ->where('module', 'sertifikasi')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(10)
                ->get();

            return view('lembaga.sertifikasi.import', [
                'lembaga' => $lembagaList,
                'importLogs' => $importLogs,
                'preview' => $preview,
                'selectedLembagaId' => $lembagaId,
            ]);
        }

        $result = $importService->importFromPreview(
            tempFile: $request->validated('temp_file'),
            mapping: $request->validated('mapping', []),
            lembagaId: $lembagaId,
            actor: $request->user(),
            originalFilename: $request->validated('original_filename') ?? 'import-sertifikasi.csv'
        );

        $auditLog->log($request->user(), 'sertifikasi.import', null, [
            'lembaga_pelatihan_id' => $lembagaId,
            'processed' => $result['processed'],
            'created' => $result['created'],
            'updated' => $result['updated'],
            'batch_id' => $result['batch']->id,
        ], $request);

        $errors = $result['errors'];
        if (count($errors) > 20) {
            $errors = array_slice($errors, 0, 20);
            $errors[] = 'Error lain disimpan di report batch import. Silakan unduh report.';
        }

        return redirect()
            ->route('lembaga.sertifikasi.import')
            ->with('status', 'Import selesai. Diproses: '.$result['processed'].' | Baru: '.$result['created'].' | Update: '.$result['updated'].' | Duplikat: '.($result['duplicate_rows'] ?? 0).' | Gagal: '.$result['failed_rows'])
            ->with('import_errors', $errors)
            ->with('last_import_batch_id', $result['batch']->id);
    }

    public function downloadImportLog(Request $request, ImportBatch $importBatch): StreamedResponse
    {
        abort_unless($importBatch->module === 'sertifikasi', 404);
        abort_unless($importBatch->user_id === $request->user()->id, 403);
        abort_if(!$importBatch->report_path || !Storage::exists($importBatch->report_path), 404);

        $filename = 'import-sertifikasi-'.$importBatch->created_at->format('Ymd_His').'.csv';

        return Storage::download($importBatch->report_path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'nik',
            'nama_sertifikat',
            'jumlah_jam',
            'nilai_kompetensi',
            'nomor_sertifikat',
            'tanggal_terbit',
            'tanggal_kadaluarsa',
            'status_verifikasi',
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        }, 'template-sertifikasi.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function store(StoreSertifikasiRequest $request, PerawatStatusService $statusService, AuditLogService $auditLog): JsonResponse|RedirectResponse
    {
        $lembagaId = $request->validated('lembaga_pelatihan_id');
        $lembaga = LembagaPelatihan::findOrFail($lembagaId);
        $this->authorize('manage', $lembaga);

        $perawat = $this->resolvePerawat($request->validated());
        $statusVerifikasi = $request->validated('status_verifikasi') ?? SertifikasiKompetensi::VERIF_PENDING;
        $reviewed = in_array($statusVerifikasi, [
            SertifikasiKompetensi::VERIF_VERIFIED,
            SertifikasiKompetensi::VERIF_VALID,
            SertifikasiKompetensi::VERIF_REJECTED,
        ], true);

        $data = [
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembagaId,
            'nama_sertifikat' => $request->validated('nama_sertifikat'),
            'jumlah_jam' => $request->validated('jumlah_jam'),
            'nilai_kompetensi' => $request->validated('nilai_kompetensi'),
            'nomor_sertifikat' => $request->validated('nomor_sertifikat'),
            'tanggal_terbit' => $request->validated('tanggal_terbit'),
            'tanggal_kadaluarsa' => $request->validated('tanggal_kadaluarsa'),
            'status_verifikasi' => $statusVerifikasi,
            'source_system' => 'lembaga_portal',
            'source_actor' => 'lembaga',
            'verified_by' => $reviewed ? $request->user()->id : null,
            'verified_at' => $reviewed ? now() : null,
            'rejected_reason' => $statusVerifikasi === SertifikasiKompetensi::VERIF_REJECTED
                ? ($request->validated('catatan') ?? 'Ditolak oleh lembaga.')
                : null,
        ];

        if ($request->hasFile('file_sertifikat')) {
            $data['file_sertifikat'] = $request->file('file_sertifikat')->store('sertifikat', 'public');
        }

        $sertifikasi = SertifikasiKompetensi::create($data);

        $statusService->refresh($perawat);

        $auditLog->log($request->user(), 'sertifikasi.create', $sertifikasi, [
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembagaId,
        ], $request);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Sertifikasi kompetensi berhasil disimpan.',
                'sertifikasi_id' => $sertifikasi->id,
            ]);
        }

        return redirect()
            ->route('lembaga.sertifikasi.create')
            ->with('status', 'Sertifikasi kompetensi berhasil disimpan.');
    }

    public function verify(VerifySertifikasiRequest $request, SertifikasiKompetensi $sertifikasi, PerawatStatusService $statusService, AuditLogService $auditLog, VerificationService $verification): RedirectResponse
    {
        $this->authorize('verify', $sertifikasi);

        if ($sertifikasi->status_verifikasi === SertifikasiKompetensi::VERIF_VERIFIED) {
            return back()->withErrors(['status' => 'Sertifikasi sudah terverifikasi.']);
        }

        $verification->apply($sertifikasi, VerificationService::ACTION_APPROVE, [
            'status_field' => 'status_verifikasi',
            'approved_value' => SertifikasiKompetensi::VERIF_VERIFIED,
            'rejected_value' => SertifikasiKompetensi::VERIF_REJECTED,
            'reviewed_by_field' => 'verified_by',
            'reviewed_at_field' => 'verified_at',
            'approved_payload' => [
                'source_system' => 'lembaga_portal',
                'source_actor' => 'lembaga',
                'rejected_reason' => null,
            ],
        ], [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $auditLog->log($request->user(), 'sertifikasi.verify', $sertifikasi, [
            'lembaga_pelatihan_id' => $sertifikasi->lembaga_pelatihan_id,
            'perawat_nik' => $sertifikasi->perawat_nik,
        ], $request);

        $statusService->refresh($sertifikasi->perawat);
        $this->notifyPerawat($sertifikasi, 'Sertifikat Anda telah diverifikasi.');

        return back()->with('status', 'Sertifikasi berhasil diverifikasi.');
    }

    public function reject(VerifySertifikasiRequest $request, SertifikasiKompetensi $sertifikasi, AuditLogService $auditLog, VerificationService $verification): RedirectResponse
    {
        $this->authorize('reject', $sertifikasi);

        if ($sertifikasi->status_verifikasi === SertifikasiKompetensi::VERIF_REJECTED) {
            return back()->withErrors(['status' => 'Sertifikasi sudah ditolak.']);
        }

        $verification->apply($sertifikasi, VerificationService::ACTION_REJECT, [
            'status_field' => 'status_verifikasi',
            'approved_value' => SertifikasiKompetensi::VERIF_VERIFIED,
            'rejected_value' => SertifikasiKompetensi::VERIF_REJECTED,
            'reviewed_by_field' => 'verified_by',
            'reviewed_at_field' => 'verified_at',
            'note_field' => 'rejected_reason',
            'rejected_payload' => [
                'source_system' => 'lembaga_portal',
                'source_actor' => 'lembaga',
            ],
        ], [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'note' => $request->validated('catatan') ?: 'Ditolak oleh lembaga.',
        ]);

        $auditLog->log($request->user(), 'sertifikasi.reject', $sertifikasi, [
            'lembaga_pelatihan_id' => $sertifikasi->lembaga_pelatihan_id,
            'perawat_nik' => $sertifikasi->perawat_nik,
        ], $request);

        $this->notifyPerawat($sertifikasi, 'Sertifikat Anda ditolak.');

        return back()->with('status', 'Sertifikasi ditolak.');
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

    private function notifyPerawat(SertifikasiKompetensi $sertifikasi, string $message): void
    {
        $user = User::where('perawat_nik', $sertifikasi->perawat_nik)
            ->where('role', User::ROLE_PERAWAT)
            ->first();

        if ($user) {
            $user->notify(new SertifikasiStatusNotification($sertifikasi, $message));
        }
    }
}
