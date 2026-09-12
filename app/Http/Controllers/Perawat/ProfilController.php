<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Perawat\UpdateProfilRequest;
use App\Models\Perawat;
use App\Models\RiwayatPekerjaan;
use App\Support\NotificationQuery;
use App\Services\CvPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function show(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(404);
        }

        $perawat->load([
            'dataLegalitas.dataReviewedBy',
            'dataLegalitas.regulatorOrganisasi',
            'riwayatPendidikan.institusiPendidikan',
            'sertifikasi.lembagaPelatihan',
            'sertifikasi.verifiedBy',
            'riwayatPekerjaan.mitraPengguna',
            'riwayatPekerjaan.verifiedBy',
            'careerEvents' => function ($query) {
                $query->latest('happened_at')
                    ->latest('created_at')
                    ->limit(15);
            },
        ]);

        $legalitasDataUnreadQuery = $request->user()->unreadNotifications();
        NotificationQuery::whereDataEquals($legalitasDataUnreadQuery, 'tipe', 'legalitas_data_status');
        $legalitasDataUnread = $legalitasDataUnreadQuery->count();

        return view('perawat.dashboard', compact('perawat', 'legalitasDataUnread'));
    }

    public function edit(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        return view('perawat.profil.edit', compact('perawat'));
    }

    public function update(UpdateProfilRequest $request)
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $perawat->fill($request->validated());
        $perawat->save();

        $user = $request->user();
        $userUpdates = [];
        if ($request->filled('email') && $user->email !== $request->input('email')) {
            $userUpdates['email'] = $request->input('email');
        }
        if ($request->filled('nama') && $user->name !== $request->input('nama')) {
            $userUpdates['name'] = $request->input('nama');
        }
        if ($userUpdates !== []) {
            $user->update($userUpdates);
        }

        return redirect()
            ->route('perawat.profil.edit')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    public function updateShare(Request $request)
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $enable = (bool) $request->input('public_profile_enabled', false);
        $regenerate = (bool) $request->input('regenerate_token', false);

        if ($regenerate) {
            $enable = true;
        }

        if ($enable && (!$perawat->public_profile_token || $regenerate)) {
            $perawat->public_profile_token = Str::random(64);
        }

        $perawat->public_profile_enabled = $enable;
        $perawat->save();

        return redirect()
            ->route('perawat.profil.edit')
            ->with('status', 'Pengaturan profil publik berhasil diperbarui.');
    }

    public function cv(Request $request)
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(404);
        }

        $perawat->load([
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
        ]);

        $html = view('perawat.cv', compact('perawat'))->render();

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, 'cv-terverifikasi-'.$perawat->nik.'.html');
    }

    public function cvPdf(Request $request, CvPdfService $pdfService)
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(404);
        }

        $perawat->load([
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
        ]);

        try {
            $pdf = $pdfService->generate($perawat);
        } catch (\RuntimeException $e) {
            return redirect()->route('perawat.dashboard')->withErrors(['pdf' => $e->getMessage()]);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="cv-terverifikasi-'.$perawat->nik.'.pdf"',
        ]);
    }
}
