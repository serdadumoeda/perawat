<?php

namespace App\Http\Controllers;

use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Services\CvPdfService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPerawatController extends Controller
{
    public function show(string $token): View
    {
        $perawat = $this->resolvePublicPerawat($token);

        return view('public.perawat.show', compact('perawat'));
    }

    public function cv(string $token)
    {
        $perawat = $this->resolvePublicPerawat($token);

        $html = view('public.perawat.cv', compact('perawat'))->render();

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, 'cv-terverifikasi-'.$perawat->nik.'.html');
    }

    public function cvPdf(string $token, CvPdfService $pdfService)
    {
        $perawat = $this->resolvePublicPerawat($token);

        try {
            $pdf = $pdfService->generate($perawat);
        } catch (\RuntimeException $e) {
            return redirect()->route('public.perawat.show', $token)->withErrors(['pdf' => $e->getMessage()]);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="cv-terverifikasi-'.$perawat->nik.'.pdf"',
        ]);
    }

    private function resolvePublicPerawat(string $token): Perawat
    {
        $perawat = Perawat::where('public_profile_token', $token)
            ->where('public_profile_enabled', true)
            ->with([
                'dataLegalitas.dataReviewedBy',
                'dataLegalitas.regulatorOrganisasi',
                'riwayatPendidikan.institusiPendidikan',
                'sertifikasi.lembagaPelatihan',
                'sertifikasi.verifiedBy',
                'riwayatPekerjaan' => function ($query) {
                    $query->where(function ($q) {
                        $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                            ->orWhere('is_verified', true);
                    });
                },
                'riwayatPekerjaan.mitraPengguna',
                'riwayatPekerjaan.verifiedBy',
                'careerEvents' => function ($query) {
                    $query->latest('happened_at')
                        ->latest('created_at')
                        ->limit(10);
                },
            ])
            ->firstOrFail();

        return $perawat;
    }
}
