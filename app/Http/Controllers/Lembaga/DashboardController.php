<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\Pelatihan;
use App\Models\PelatihanPeserta;
use App\Models\SertifikasiKompetensi;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $lembagaIds = auth()->user()->lembagaPelatihan()->pluck('lembaga_pelatihan.id');

        $pelatihanQuery = Pelatihan::query()->whereIn('lembaga_pelatihan_id', $lembagaIds);
        $pesertaQuery = PelatihanPeserta::query()->whereHas('pelatihan', function ($q) use ($lembagaIds) {
            $q->whereIn('lembaga_pelatihan_id', $lembagaIds);
        });
        $sertifikasiQuery = SertifikasiKompetensi::query()->whereIn('lembaga_pelatihan_id', $lembagaIds);

        $stats = [
            'pelatihan_total' => (clone $pelatihanQuery)->count(),
            'pelatihan_dibuka' => (clone $pelatihanQuery)->where('status', Pelatihan::STATUS_DIBUKA)->count(),
            'pelatihan_ditutup' => (clone $pelatihanQuery)->where('status', Pelatihan::STATUS_DITUTUP)->count(),
            'peserta_total' => (clone $pesertaQuery)->count(),
            'peserta_lulus' => (clone $pesertaQuery)->where('status', PelatihanPeserta::STATUS_LULUS)->count(),
            'peserta_tidak_lulus' => (clone $pesertaQuery)->where('status', PelatihanPeserta::STATUS_TIDAK_LULUS)->count(),
            'sertifikasi_total' => (clone $sertifikasiQuery)->count(),
            'sertifikasi_verified' => (clone $sertifikasiQuery)->where('status_verifikasi', SertifikasiKompetensi::VERIF_VERIFIED)->count(),
            'sertifikasi_pending' => (clone $sertifikasiQuery)->where('status_verifikasi', SertifikasiKompetensi::VERIF_PENDING)->count(),
            'sertifikasi_rejected' => (clone $sertifikasiQuery)->where('status_verifikasi', SertifikasiKompetensi::VERIF_REJECTED)->count(),
        ];

        $topPelatihan = Pelatihan::withCount('peserta')
            ->whereIn('lembaga_pelatihan_id', $lembagaIds)
            ->orderByDesc('peserta_count')
            ->limit(10)
            ->get();

        return view('lembaga.dashboard', [
            'stats' => $stats,
            'topPelatihan' => $topPelatihan,
        ]);
    }
}
