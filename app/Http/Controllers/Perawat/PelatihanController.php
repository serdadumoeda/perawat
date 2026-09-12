<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Perawat\DaftarPelatihanRequest;
use App\Models\Pelatihan;
use App\Models\PelatihanPeserta;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PelatihanController extends Controller
{
    public function index(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $query = Pelatihan::query()
            ->with('lembagaPelatihan')
            ->where('status', Pelatihan::STATUS_DIBUKA);

        if ($request->filled('q')) {
            $query->where('nama', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('lembaga')) {
            $query->whereHas('lembagaPelatihan', function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->string('lembaga').'%');
            });
        }

        $pelatihan = $query->latest()->paginate(12)->withQueryString();

        $enrolled = PelatihanPeserta::where('perawat_nik', $perawat->nik)
            ->pluck('status', 'pelatihan_id')
            ->all();

        return view('perawat.pelatihan.index', compact('pelatihan', 'enrolled'));
    }

    public function mine(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $riwayat = PelatihanPeserta::with('pelatihan.lembagaPelatihan')
            ->where('perawat_nik', $perawat->nik)
            ->latest()
            ->paginate(15);

        return view('perawat.pelatihan.mine', compact('riwayat'));
    }

    public function daftar(DaftarPelatihanRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        return DB::transaction(function () use ($request, $perawat, $auditLog) {
            $pelatihan = Pelatihan::query()
                ->where('status', Pelatihan::STATUS_DIBUKA)
                ->lockForUpdate()
                ->findOrFail($request->validated('pelatihan_id'));

            $exists = PelatihanPeserta::where('pelatihan_id', $pelatihan->id)
                ->where('perawat_nik', $perawat->nik)
                ->exists();

            if ($exists) {
                return back()->withErrors(['pelatihan_id' => 'Anda sudah terdaftar di pelatihan ini.']);
            }

            if ($pelatihan->kuota) {
                $count = PelatihanPeserta::where('pelatihan_id', $pelatihan->id)->count();
                if ($count >= $pelatihan->kuota) {
                    return back()->withErrors(['pelatihan_id' => 'Kuota pelatihan sudah penuh.']);
                }
            }

            $peserta = PelatihanPeserta::create([
                'pelatihan_id' => $pelatihan->id,
                'perawat_nik' => $perawat->nik,
                'status' => PelatihanPeserta::STATUS_TERDAFTAR,
                'tanggal_daftar' => now()->toDateString(),
            ]);

            $auditLog->log($request->user(), 'pelatihan.daftar', $peserta, [
                'pelatihan_id' => $pelatihan->id,
            ], $request);

            return redirect()
                ->route('perawat.pelatihan.mine')
                ->with('status', 'Pendaftaran pelatihan berhasil.');
        });
    }
}
