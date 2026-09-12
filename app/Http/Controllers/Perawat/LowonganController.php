<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Perawat\ApplyLowonganRequest;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Perawat;
use App\Models\User;
use App\Notifications\LamaranMasukNotification;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LowonganController extends Controller
{
    public function index(Request $request): View
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $query = Lowongan::query()
            ->with('mitraPengguna')
            ->where('status', 'dibuka')
            ->whereNotNull('published_at');

        if ($request->filled('q')) {
            $query->where('judul', 'like', '%'.$request->string('q').'%');
        }
        if ($request->filled('tipe_penempatan')) {
            $query->where('tipe_penempatan', $request->string('tipe_penempatan'));
        }

        $lowongan = $query->latest()->paginate(12)->withQueryString();
        $appliedIds = $perawat->lamaran()->pluck('lowongan_id')->all();

        return view('perawat.lowongan.index', compact('lowongan', 'appliedIds', 'perawat'));
    }

    public function show(Request $request, Lowongan $lowongan): View
    {
        if ($lowongan->status !== 'dibuka' || !$lowongan->published_at) {
            abort(404);
        }

        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        $lowongan->load('mitraPengguna');
        $lamaran = $perawat->lamaran()->where('lowongan_id', $lowongan->id)->latest()->first();

        return view('perawat.lowongan.show', compact('lowongan', 'lamaran', 'perawat'));
    }

    public function apply(ApplyLowonganRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $perawat = $request->user()->perawat;
        if (!$perawat) {
            abort(403);
        }

        if ($perawat->status_kesiapan !== Perawat::KESIAPAN_READY) {
            return back()->withErrors(['status' => 'Profil belum ready to hire. Lengkapi pendidikan, sertifikasi, dan legalitas.']);
        }

        $isLockedOrHired = \App\Models\Rekrutmen::where('perawat_nik', $perawat->nik)
            ->whereIn('status', [\App\Models\Rekrutmen::STATUS_LOCKED, \App\Models\Rekrutmen::STATUS_HIRED])
            ->exists();

        if ($isLockedOrHired) {
            return back()->withErrors(['status' => 'Anda sedang terikat rekrutmen aktif. Selesaikan atau lepas terlebih dahulu sebelum melamar lowongan baru.']);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $perawat, $auditLog) {
            $lowongan = Lowongan::query()
                ->where('status', 'dibuka')
                ->whereNotNull('published_at')
                ->lockForUpdate()
                ->findOrFail($request->validated('lowongan_id'));

            $exists = Lamaran::where('perawat_nik', $perawat->nik)
                ->where('lowongan_id', $lowongan->id)
                ->exists();

            if ($exists) {
                return back()->withErrors(['lowongan_id' => 'Anda sudah melamar lowongan ini.']);
            }

            $lamaran = Lamaran::create([
                'perawat_nik' => $perawat->nik,
                'lowongan_id' => $lowongan->id,
                'mitra_pengguna_id' => $lowongan->mitra_pengguna_id,
                'status' => Lamaran::STATUS_DIAJUKAN,
                'catatan' => $request->validated('catatan'),
            ]);

            $auditLog->log($request->user(), 'lamaran.apply', $lamaran, [
                'lowongan_id' => $lowongan->id,
                'mitra_pengguna_id' => $lowongan->mitra_pengguna_id,
            ], $request);

            $this->notifyMitra($lamaran);

            return redirect()
                ->route('perawat.lowongan.show', $lowongan)
                ->with('status', 'Lamaran berhasil dikirim.');
        });
    }

    private function notifyMitra(Lamaran $lamaran): void
    {
        $lamaran->loadMissing(['perawat', 'lowongan']);

        User::whereIn('role', [User::ROLE_MITRA, User::ROLE_FASKES, User::ROLE_HEADHUNTER])
            ->whereHas('mitraPengguna', function ($q) use ($lamaran) {
                $q->where('mitra_pengguna.id', $lamaran->mitra_pengguna_id);
            })
            ->each(function (User $user) use ($lamaran) {
                $user->notify(new LamaranMasukNotification($lamaran));
            });
    }
}
