<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lembaga\StorePelatihanRequest;
use App\Http\Requests\Lembaga\UpdatePelatihanPesertaRequest;
use App\Models\LembagaPelatihan;
use App\Models\Pelatihan;
use App\Models\PelatihanPeserta;
use App\Models\SertifikasiKompetensi;
use App\Services\AuditLogService;
use App\Services\PerawatStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PelatihanController extends Controller
{
    public function index(): View
    {
        $lembagaIds = auth()->user()->lembagaPelatihan()->pluck('lembaga_pelatihan.id');

        $pelatihan = Pelatihan::with('lembagaPelatihan')
            ->whereIn('lembaga_pelatihan_id', $lembagaIds)
            ->withCount('peserta')
            ->latest()
            ->paginate(15);

        return view('lembaga.pelatihan.index', compact('pelatihan'));
    }

    public function create(): View
    {
        $lembaga = auth()->user()->lembagaPelatihan()->get();

        return view('lembaga.pelatihan.create', compact('lembaga'));
    }

    public function store(StorePelatihanRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $lembagaId = $request->validated('lembaga_pelatihan_id');
        $lembaga = LembagaPelatihan::findOrFail($lembagaId);
        $this->authorize('manage', $lembaga);

        $pelatihan = Pelatihan::create([
            'lembaga_pelatihan_id' => $lembagaId,
            'nama' => $request->validated('nama'),
            'deskripsi' => $request->validated('deskripsi'),
            'jumlah_jam' => $request->validated('jumlah_jam'),
            'tanggal_mulai' => $request->validated('tanggal_mulai'),
            'tanggal_selesai' => $request->validated('tanggal_selesai'),
            'lokasi' => $request->validated('lokasi'),
            'kuota' => $request->validated('kuota'),
            'status' => $request->validated('status') ?? Pelatihan::STATUS_DIBUKA,
        ]);

        $auditLog->log($request->user(), 'pelatihan.create', $pelatihan, [
            'lembaga_pelatihan_id' => $lembagaId,
        ], $request);

        return redirect()
            ->route('lembaga.pelatihan.index')
            ->with('status', 'Pelatihan berhasil dibuat.');
    }

    public function show(Pelatihan $pelatihan): View
    {
        $this->authorize('manage', $pelatihan->lembagaPelatihan);

        $pelatihan->load(['lembagaPelatihan', 'peserta.perawat']);

        return view('lembaga.pelatihan.show', compact('pelatihan'));
    }

    public function close(Pelatihan $pelatihan, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('manage', $pelatihan->lembagaPelatihan);

        $pelatihan->status = Pelatihan::STATUS_DITUTUP;
        $pelatihan->save();

        $auditLog->log(request()->user(), 'pelatihan.close', $pelatihan, [], request());

        return back()->with('status', 'Pelatihan ditutup.');
    }

    public function markLulus(Pelatihan $pelatihan, PelatihanPeserta $peserta, UpdatePelatihanPesertaRequest $request, PerawatStatusService $statusService, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('manage', $pelatihan->lembagaPelatihan);

        if ($peserta->pelatihan_id !== $pelatihan->id) {
            abort(404);
        }

        $peserta->status = PelatihanPeserta::STATUS_LULUS;
        $peserta->tanggal_lulus = now()->toDateString();
        $peserta->nilai_kompetensi = $request->validated('nilai_kompetensi');
        $peserta->catatan = $request->validated('catatan');
        $peserta->save();

        $sertifikasi = SertifikasiKompetensi::updateOrCreate(
            [
                'perawat_nik' => $peserta->perawat_nik,
                'pelatihan_id' => $pelatihan->id,
            ],
            [
                'lembaga_pelatihan_id' => $pelatihan->lembaga_pelatihan_id,
                'nama_sertifikat' => $pelatihan->nama,
                'jumlah_jam' => $pelatihan->jumlah_jam,
                'nilai_kompetensi' => $peserta->nilai_kompetensi,
                'tanggal_terbit' => $pelatihan->tanggal_selesai ?? now()->toDateString(),
                'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
                'source_system' => 'lembaga_pelatihan',
                'source_actor' => 'lembaga',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'rejected_reason' => null,
            ]
        );

        $statusService->refresh($peserta->perawat);

        $auditLog->log($request->user(), 'pelatihan.peserta.lulus', $peserta, [
            'pelatihan_id' => $pelatihan->id,
            'sertifikasi_id' => $sertifikasi->id,
        ], $request);

        return back()->with('status', 'Peserta ditandai lulus dan sertifikat dibuat.');
    }

    public function markTidakLulus(Pelatihan $pelatihan, PelatihanPeserta $peserta, UpdatePelatihanPesertaRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('manage', $pelatihan->lembagaPelatihan);

        if ($peserta->pelatihan_id !== $pelatihan->id) {
            abort(404);
        }

        $peserta->status = PelatihanPeserta::STATUS_TIDAK_LULUS;
        $peserta->nilai_kompetensi = $request->validated('nilai_kompetensi');
        $peserta->catatan = $request->validated('catatan');
        $peserta->save();

        $auditLog->log($request->user(), 'pelatihan.peserta.tidak_lulus', $peserta, [
            'pelatihan_id' => $pelatihan->id,
        ], $request);

        return back()->with('status', 'Peserta ditandai tidak lulus.');
    }
}
