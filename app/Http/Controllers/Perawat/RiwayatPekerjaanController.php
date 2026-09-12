<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Perawat\StoreRiwayatPekerjaanRequest;
use App\Models\MitraPengguna;
use App\Models\RiwayatPekerjaan;
use App\Services\AuditLogService;
use App\Services\PerawatStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatPekerjaanController extends Controller
{
    public function index(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $riwayat = RiwayatPekerjaan::with(['mitraPengguna', 'verifiedBy'])
            ->where('perawat_nik', $perawat->nik)
            ->latest()
            ->paginate(15);

        return view('perawat.riwayat_pekerjaan.index', compact('riwayat'));
    }

    public function create(): View
    {
        $mitra = MitraPengguna::orderBy('nama')->get();

        return view('perawat.riwayat_pekerjaan.create', compact('mitra'));
    }

    public function store(StoreRiwayatPekerjaanRequest $request, PerawatStatusService $statusService, AuditLogService $auditLog): RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $existingPending = RiwayatPekerjaan::where('perawat_nik', $perawat->nik)
            ->where('mitra_pengguna_id', $request->validated('mitra_pengguna_id'))
            ->where('verifikasi_status', RiwayatPekerjaan::VERIF_PENDING)
            ->exists();

        if ($existingPending) {
            return back()->withErrors(['mitra_pengguna_id' => 'Anda masih memiliki pengajuan verifikasi yang sedang diproses untuk mitra ini.'])->withInput();
        }

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $request->validated('mitra_pengguna_id'),
            'posisi' => $request->validated('posisi'),
            'unit' => $request->validated('unit'),
            'tanggal_mulai' => $request->validated('tanggal_mulai'),
            'tanggal_selesai' => $request->validated('status_pekerjaan') === 'aktif' ? null : $request->validated('tanggal_selesai'),
            'status_pekerjaan' => $request->validated('status_pekerjaan'),
            'keterangan' => $request->validated('keterangan'),
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
            'sumber_data' => RiwayatPekerjaan::SOURCE_PERAWAT,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING,
            'source_system' => 'perawat_portal',
            'source_actor' => 'perawat',
        ]);

        $auditLog->log($request->user(), 'riwayat_pekerjaan.submit', $riwayat, [
            'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
        ], $request);

        $statusService->refresh($perawat);

        return redirect()
            ->route('perawat.riwayat_pekerjaan.index')
            ->with('status', 'Riwayat pekerjaan berhasil dikirim untuk verifikasi.');
    }

    public function edit(Request $request, RiwayatPekerjaan $riwayatPekerjaan): View|\Illuminate\Http\RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat || $riwayatPekerjaan->perawat_nik !== $perawat->nik) {
            abort(403);
        }

        if ($riwayatPekerjaan->verifikasi_status === RiwayatPekerjaan::VERIF_VERIFIED) {
            return redirect()
                ->route('perawat.riwayat_pekerjaan.index')
                ->withErrors(['status' => 'Riwayat pekerjaan yang sudah terverifikasi tidak dapat diubah.']);
        }

        $mitra = MitraPengguna::orderBy('nama')->get();

        return view('perawat.riwayat_pekerjaan.edit', compact('riwayatPekerjaan', 'mitra'));
    }

    public function update(StoreRiwayatPekerjaanRequest $request, RiwayatPekerjaan $riwayatPekerjaan, PerawatStatusService $statusService, AuditLogService $auditLog): RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat || $riwayatPekerjaan->perawat_nik !== $perawat->nik) {
            abort(403);
        }

        if ($riwayatPekerjaan->verifikasi_status === RiwayatPekerjaan::VERIF_VERIFIED) {
            return redirect()
                ->route('perawat.riwayat_pekerjaan.index')
                ->withErrors(['status' => 'Riwayat pekerjaan yang sudah terverifikasi tidak dapat diubah.']);
        }

        $riwayatPekerjaan->update([
            'mitra_pengguna_id' => $request->validated('mitra_pengguna_id'),
            'posisi' => $request->validated('posisi'),
            'unit' => $request->validated('unit'),
            'tanggal_mulai' => $request->validated('tanggal_mulai'),
            'tanggal_selesai' => $request->validated('status_pekerjaan') === 'aktif' ? null : $request->validated('tanggal_selesai'),
            'status_pekerjaan' => $request->validated('status_pekerjaan'),
            'keterangan' => $request->validated('keterangan'),
            'verifikasi_status' => RiwayatPekerjaan::VERIF_PENDING, // Reset status to pending when updated
            'rejected_reason' => null, // Reset rejection note
        ]);

        $auditLog->log($request->user(), 'riwayat_pekerjaan.update', $riwayatPekerjaan, [], $request);

        $statusService->refresh($perawat);

        return redirect()
            ->route('perawat.riwayat_pekerjaan.index')
            ->with('status', 'Riwayat pekerjaan berhasil diperbarui dan dikirim ulang untuk verifikasi.');
    }

    public function destroy(Request $request, RiwayatPekerjaan $riwayatPekerjaan, PerawatStatusService $statusService, AuditLogService $auditLog): RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat || $riwayatPekerjaan->perawat_nik !== $perawat->nik) {
            abort(403);
        }

        if ($riwayatPekerjaan->verifikasi_status === RiwayatPekerjaan::VERIF_VERIFIED) {
            return redirect()
                ->route('perawat.riwayat_pekerjaan.index')
                ->withErrors(['status' => 'Riwayat pekerjaan yang sudah terverifikasi tidak dapat dihapus.']);
        }

        $auditLog->log($request->user(), 'riwayat_pekerjaan.delete', $riwayatPekerjaan, [], $request);

        $riwayatPekerjaan->delete();

        $statusService->refresh($perawat);

        return redirect()
            ->route('perawat.riwayat_pekerjaan.index')
            ->with('status', 'Riwayat pekerjaan berhasil dihapus.');
    }
}
