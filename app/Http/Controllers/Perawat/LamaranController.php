<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\Rekrutmen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LamaranController extends Controller
{
    public function index(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $lamaran = Lamaran::with('lowongan.mitraPengguna')
            ->where('perawat_nik', $perawat->nik)
            ->latest()
            ->paginate(15);

        return view('perawat.lamaran.index', compact('lamaran'));
    }

    public function cancel(Request $request, Lamaran $lamaran): RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat || $lamaran->perawat_nik !== $perawat->nik) {
            abort(403);
        }

        if ($lamaran->status !== Lamaran::STATUS_DIAJUKAN) {
            return back()->withErrors(['status' => 'Lamaran sudah dalam proses seleksi dan tidak bisa dibatalkan sepihak.']);
        }

        $rekrutmen = Rekrutmen::where('mitra_pengguna_id', $lamaran->mitra_pengguna_id)
            ->where('perawat_nik', $lamaran->perawat_nik)
            ->first();

        if ($rekrutmen && $rekrutmen->status === Rekrutmen::STATUS_LOCKED) {
            $rekrutmen->status = Rekrutmen::STATUS_RELEASED;
            $rekrutmen->lowongan_id = $lamaran->lowongan_id;
            $rekrutmen->released_at = now();
            $rekrutmen->save();
        }

        $lamaran->status = Lamaran::STATUS_DIBATALKAN;
        $lamaran->save();

        return back()->with('status', 'Lamaran berhasil dibatalkan.');
    }
}
