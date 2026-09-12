<?php

namespace App\Http\Controllers\Kampus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kampus\ImportLulusanRequest;
use App\Http\Requests\Kampus\StoreLulusanRequest;
use App\Http\Requests\Kampus\UpdateLulusanRequest;
use App\Models\ImportBatch;
use App\Models\InstitusiPendidikan;
use App\Models\Perawat;
use App\Models\RiwayatPendidikan;
use App\Services\AuditLogService;
use App\Services\LulusanImportService;
use App\Services\PerawatStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LulusanController extends Controller
{
    public function index(Request $request): View
    {
        $institusiList = $request->user()->institusiPendidikan()->orderBy('nama')->get();
        $selectedId = $request->query('institusi_id') ?: $institusiList->first()?->id;

        $selectedInstitusi = null;
        $perawat = Perawat::query()->whereRaw('1 = 0')->paginate(15);

        if ($selectedId) {
            $selectedInstitusi = InstitusiPendidikan::findOrFail($selectedId);
            $this->authorize('manage', $selectedInstitusi);

            $query = Perawat::query()
                ->whereHas('riwayatPendidikan', function ($q) use ($selectedId) {
                    $q->where('institusi_pendidikan_id', $selectedId);
                })
                ->with(['riwayatPendidikan' => function ($q) use ($selectedId) {
                    $q->where('institusi_pendidikan_id', $selectedId);
                }])
                ->orderBy('nama');

            if ($request->filled('q')) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('nik', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            }

            $perawat = $query->paginate(15)->withQueryString();
        }

        return view('kampus.lulusan.index', [
            'institusiList' => $institusiList,
            'selectedInstitusi' => $selectedInstitusi,
            'perawat' => $perawat,
        ]);
    }

    public function create(): View
    {
        $institusi = auth()->user()->institusiPendidikan()->get();

        return view('kampus.lulusan.create', compact('institusi'));
    }

    public function importForm(): View
    {
        $institusi = auth()->user()->institusiPendidikan()->get();
        $importLogs = ImportBatch::query()
            ->where('module', 'lulusan')
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('kampus.lulusan.import', [
            'institusi' => $institusi,
            'importLogs' => $importLogs,
        ]);
    }

    public function import(ImportLulusanRequest $request, LulusanImportService $importService, AuditLogService $auditLog): RedirectResponse|View
    {
        $institusiId = $request->validated('institusi_pendidikan_id');
        $institusi = InstitusiPendidikan::findOrFail($institusiId);
        $this->authorize('manage', $institusi);

        $action = $request->validated('action');
        if ($action === 'preview') {
            $preview = $importService->preview($request->file('file'));
            $institusiList = $request->user()->institusiPendidikan()->get();
            $importLogs = ImportBatch::query()
                ->where('module', 'lulusan')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(10)
                ->get();

            return view('kampus.lulusan.import', [
                'institusi' => $institusiList,
                'importLogs' => $importLogs,
                'preview' => $preview,
                'selectedInstitusiId' => $institusiId,
            ]);
        }

        $result = $importService->importFromPreview(
            tempFile: $request->validated('temp_file'),
            mapping: $request->validated('mapping', []),
            institusiId: $institusiId,
            actor: $request->user(),
            originalFilename: $request->validated('original_filename') ?? 'import-lulusan.csv'
        );

        $auditLog->log($request->user(), 'lulusan.import', null, [
            'institusi_pendidikan_id' => $institusiId,
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
            ->route('kampus.lulusan.import')
            ->with('status', 'Import selesai. Diproses: '.$result['processed'].' | Baru: '.$result['created'].' | Update: '.$result['updated'].' | Duplikat: '.($result['duplicate_rows'] ?? 0).' | Gagal: '.$result['failed_rows'])
            ->with('import_errors', $errors)
            ->with('last_import_batch_id', $result['batch']->id);
    }

    public function downloadImportLog(Request $request, ImportBatch $importBatch): StreamedResponse
    {
        abort_unless($importBatch->module === 'lulusan', 404);
        abort_unless($importBatch->user_id === $request->user()->id, 403);
        abort_if(!$importBatch->report_path || !Storage::exists($importBatch->report_path), 404);

        $filename = 'import-lulusan-'.$importBatch->created_at->format('Ymd_His').'.csv';

        return Storage::download($importBatch->report_path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'nik', 'nim', 'nama', 'email', 'no_hp', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat',
            'jenjang', 'program_studi', 'nomor_ijazah', 'tanggal_ijazah', 'tahun_masuk', 'tahun_lulus', 'ipk',
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        }, 'template-lulusan.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function store(StoreLulusanRequest $request, PerawatStatusService $statusService, AuditLogService $auditLog): JsonResponse|RedirectResponse
    {
        $institusiId = $request->validated('institusi_pendidikan_id');
        $institusi = InstitusiPendidikan::findOrFail($institusiId);
        $this->authorize('manage', $institusi);

        $nik = trim((string) $request->validated('nik', ''));
        $nim = trim((string) $request->validated('nim', ''));
        $ijazah = trim((string) $request->validated('nomor_ijazah', ''));

        $perawat = null;
        if ($nik !== '') {
            $perawat = Perawat::where('nik', $nik)->first();
        }
        if (!$perawat && $nim !== '') {
            $perawat = Perawat::where('nim', $nim)->first();
        }
        if (!$perawat) {
            $effectiveNik = $nik !== '' ? $nik : ('TMP-'.substr(md5($institusiId.'|'.($nim ?: $ijazah ?: $request->validated('nama'))), 0, 12));
            $perawat = new Perawat(['nik' => $effectiveNik]);
        }

        $perawat->fill([
            'nim' => $nim ?: $perawat->nim,
            'nama' => $request->validated('nama'),
            'email' => $request->validated('email'),
            'no_hp' => $request->validated('no_hp'),
            'jenis_kelamin' => $request->validated('jenis_kelamin'),
            'tempat_lahir' => $request->validated('tempat_lahir'),
            'tanggal_lahir' => $request->validated('tanggal_lahir'),
            'alamat' => $request->validated('alamat'),
        ]);
        $perawat->save();

        $riwayatData = [
            'nim' => $nim ?: null,
            'jenjang' => $request->validated('jenjang'),
            'program_studi' => $request->validated('program_studi'),
            'nomor_ijazah' => $request->validated('nomor_ijazah'),
            'tanggal_ijazah' => $request->validated('tanggal_ijazah'),
            'tahun_masuk' => $request->validated('tahun_masuk'),
            'tahun_lulus' => $request->validated('tahun_lulus'),
            'ipk' => $request->validated('ipk'),
            'status_verifikasi' => RiwayatPendidikan::VERIF_VERIFIED,
            'source_system' => 'kampus_portal',
            'source_actor' => 'kampus',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejected_reason' => null,
        ];

        if ($request->hasFile('transkrip')) {
            $riwayatData['transkrip_path'] = $request->file('transkrip')->store('transkrip', 'public');
        }

        if ($request->hasFile('ijazah')) {
            $riwayatData['ijazah_path'] = $request->file('ijazah')->store('ijazah', 'public');
        }

        RiwayatPendidikan::updateOrCreate(
            [
                'perawat_nik' => $perawat->nik,
                'institusi_pendidikan_id' => $institusiId,
            ],
            $riwayatData
        );

        $statusService->refresh($perawat);

        $auditLog->log($request->user(), 'lulusan.create', $perawat, [
            'institusi_pendidikan_id' => $institusiId,
        ], $request);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Data lulusan berhasil disimpan.',
                'perawat_nik' => $perawat->nik,
            ]);
        }

        return redirect()
            ->route('kampus.lulusan.create')
            ->with('status', 'Data lulusan berhasil disimpan.');
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

        $riwayat = RiwayatPendidikan::where('perawat_nik', $perawat->nik)
            ->where('institusi_pendidikan_id', $institusi->id)
            ->first();

        if (!$riwayat) {
            abort(403, 'Perawat tidak terdaftar pada institusi ini.');
        }

        return view('kampus.lulusan.edit', [
            'institusiList' => $institusiList,
            'selectedInstitusi' => $institusi,
            'perawat' => $perawat,
            'riwayat' => $riwayat,
        ]);
    }

    public function update(UpdateLulusanRequest $request, Perawat $perawat, PerawatStatusService $statusService, AuditLogService $auditLog): RedirectResponse
    {
        $institusiId = $request->validated('institusi_pendidikan_id');
        $institusi = InstitusiPendidikan::findOrFail($institusiId);
        $this->authorize('manage', $institusi);

        $riwayat = RiwayatPendidikan::where('perawat_nik', $perawat->nik)
            ->where('institusi_pendidikan_id', $institusiId)
            ->first();

        if (!$riwayat) {
            abort(403, 'Perawat tidak terdaftar pada institusi ini.');
        }

        $perawat->fill([
            'nama' => $request->validated('nama'),
            'email' => $request->validated('email'),
            'no_hp' => $request->validated('no_hp'),
            'jenis_kelamin' => $request->validated('jenis_kelamin'),
            'tempat_lahir' => $request->validated('tempat_lahir'),
            'tanggal_lahir' => $request->validated('tanggal_lahir'),
            'alamat' => $request->validated('alamat'),
        ]);
        $perawat->save();

        $riwayatData = [
            'jenjang' => $request->validated('jenjang'),
            'program_studi' => $request->validated('program_studi'),
            'nomor_ijazah' => $request->validated('nomor_ijazah'),
            'tanggal_ijazah' => $request->validated('tanggal_ijazah'),
            'tahun_masuk' => $request->validated('tahun_masuk'),
            'tahun_lulus' => $request->validated('tahun_lulus'),
            'ipk' => $request->validated('ipk'),
            'status_verifikasi' => RiwayatPendidikan::VERIF_VERIFIED,
            'source_system' => 'kampus_portal',
            'source_actor' => 'kampus',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejected_reason' => null,
        ];

        if ($request->hasFile('transkrip')) {
            $riwayatData['transkrip_path'] = $request->file('transkrip')->store('transkrip', 'public');
        }

        if ($request->hasFile('ijazah')) {
            $riwayatData['ijazah_path'] = $request->file('ijazah')->store('ijazah', 'public');
        }

        $riwayat->update($riwayatData);

        $statusService->refresh($perawat);

        $auditLog->log($request->user(), 'lulusan.update', $perawat, [
            'institusi_pendidikan_id' => $institusiId,
            'riwayat_pendidikan_id' => $riwayat->id,
        ], $request);

        return redirect()
            ->route('kampus.lulusan.edit', [$perawat, 'institusi_id' => $institusiId])
            ->with('status', 'Data lulusan berhasil diperbarui.');
    }
}
