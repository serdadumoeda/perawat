<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\StoreLowonganRequest;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Jobs\DispatchLowonganNotification;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LowonganController extends Controller
{
    public function index(): View
    {
        $mitraIds = auth()->user()->mitraPengguna()->pluck('mitra_pengguna.id');
        $lowongan = Lowongan::whereIn('mitra_pengguna_id', $mitraIds)->latest()->paginate(15);

        return view('mitra.lowongan.index', compact('lowongan'));
    }

    public function create(): View
    {
        return view('mitra.lowongan.create');
    }

    public function show(Lowongan $lowongan): View
    {
        $this->authorize('view', $lowongan);

        $lowongan->load('mitraPengguna');
        $lamaran = Lamaran::with('perawat')
            ->where('lowongan_id', $lowongan->id)
            ->latest()
            ->paginate(15);

        return view('mitra.lowongan.show', compact('lowongan', 'lamaran'));
    }

    public function store(StoreLowonganRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $mitraId = $request->validated('mitra_pengguna_id');

        $this->authorize('create', [Lowongan::class, $mitraId]);

        $lowongan = Lowongan::create([
            'mitra_pengguna_id' => $mitraId,
            'judul' => $request->validated('judul'),
            'deskripsi' => $request->validated('deskripsi'),
            'tipe_penempatan' => $request->validated('tipe_penempatan'),
            'negara_penempatan' => $request->validated('tipe_penempatan') === 'domestik' ? null : $request->validated('negara_penempatan'),
            'provinsi_penempatan' => $request->validated('provinsi_penempatan'),
            'kota_penempatan' => $request->validated('kota_penempatan'),
            'kriteria' => $request->validated('kriteria') ?? [],
            'status' => 'dibuka',
            'published_at' => now(),
        ]);

        $auditLog->log($request->user(), 'lowongan.create', $lowongan, [
            'mitra_pengguna_id' => $mitraId,
        ], $request);

        DispatchLowonganNotification::dispatch($lowongan);

        return redirect()
            ->route('mitra.lowongan.index')
            ->with('status', 'Lowongan berhasil dibuat.');
    }

    public function edit(Lowongan $lowongan): View
    {
        $this->authorize('update', $lowongan);

        return view('mitra.lowongan.edit', compact('lowongan'));
    }

    public function update(\App\Http\Requests\Mitra\UpdateLowonganRequest $request, Lowongan $lowongan, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('update', $lowongan);

        $lowongan->update([
            'judul' => $request->validated('judul'),
            'deskripsi' => $request->validated('deskripsi'),
            'tipe_penempatan' => $request->validated('tipe_penempatan'),
            'negara_penempatan' => $request->validated('tipe_penempatan') === 'domestik' ? null : $request->validated('negara_penempatan'),
            'provinsi_penempatan' => $request->validated('provinsi_penempatan'),
            'kota_penempatan' => $request->validated('kota_penempatan'),
            'kriteria' => $request->validated('kriteria') ?? [],
        ]);

        $auditLog->log($request->user(), 'lowongan.update', $lowongan, [], $request);

        return redirect()
            ->route('mitra.lowongan.show', $lowongan)
            ->with('status', 'Lowongan berhasil diperbarui.');
    }

    public function close(Lowongan $lowongan, AuditLogService $auditLog, \Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authorize('update', $lowongan);

        $lowongan->update([
            'status' => 'ditutup',
        ]);

        $auditLog->log($request->user(), 'lowongan.close', $lowongan, [], $request);

        return redirect()
            ->route('mitra.lowongan.show', $lowongan)
            ->with('status', 'Lowongan berhasil ditutup.');
    }

    public function reopen(Lowongan $lowongan, AuditLogService $auditLog, \Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authorize('update', $lowongan);

        $lowongan->update([
            'status' => 'dibuka',
        ]);

        $auditLog->log($request->user(), 'lowongan.reopen', $lowongan, [], $request);

        return redirect()
            ->route('mitra.lowongan.show', $lowongan)
            ->with('status', 'Lowongan berhasil dibuka kembali.');
    }

    public function destroy(Lowongan $lowongan, AuditLogService $auditLog, \Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authorize('delete', $lowongan);

        // Pembatalan otomatis lamaran aktif saat lowongan dihapus
        \App\Models\Lamaran::where('lowongan_id', $lowongan->id)
            ->whereIn('status', [\App\Models\Lamaran::STATUS_DIAJUKAN, \App\Models\Lamaran::STATUS_DIPROSES])
            ->update(['status' => \App\Models\Lamaran::STATUS_DIBATALKAN]);

        // Release kandidat yang terkunci oleh lowongan ini
        \App\Models\Rekrutmen::where('lowongan_id', $lowongan->id)
            ->where('status', \App\Models\Rekrutmen::STATUS_LOCKED)
            ->update([
                'status' => \App\Models\Rekrutmen::STATUS_RELEASED,
                'released_at' => now(),
            ]);

        $auditLog->log($request->user(), 'lowongan.delete', $lowongan, [], $request);

        $lowongan->delete();

        return redirect()
            ->route('mitra.lowongan.index')
            ->with('status', 'Lowongan berhasil dihapus.');
    }
}
