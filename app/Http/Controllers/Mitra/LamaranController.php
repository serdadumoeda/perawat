<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\LamaranActionRequest;
use App\Models\Lamaran;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\User;
use App\Notifications\LamaranStatusNotification;
use App\Services\AuditLogService;
use App\Services\PerawatStatusService;
use App\Services\RiwayatPekerjaanService;
use Illuminate\Http\RedirectResponse;

class LamaranController extends Controller
{
    public function process(LamaranActionRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $auditLog) {
            $lamaran = $this->resolveLamaran($request);
            $lamaran = Lamaran::where('id', $lamaran->id)->lockForUpdate()->firstOrFail();

            if ($this->isFinal($lamaran)) {
                return back()->withErrors(['status' => 'Lamaran sudah berstatus final.']);
            }

            $this->ensureNotLockedByOtherMitra($lamaran->perawat_nik, $lamaran->mitra_pengguna_id);

            $rekrutmen = Rekrutmen::where('mitra_pengguna_id', $lamaran->mitra_pengguna_id)
                ->where('perawat_nik', $lamaran->perawat_nik)
                ->lockForUpdate()
                ->first() ?? new Rekrutmen([
                    'mitra_pengguna_id' => $lamaran->mitra_pengguna_id,
                    'perawat_nik' => $lamaran->perawat_nik,
                ]);

            if ($rekrutmen->status === Rekrutmen::STATUS_HIRED) {
                return back()->withErrors(['status' => 'Kandidat sudah berstatus hired.']);
            }

            $rekrutmen->status = Rekrutmen::STATUS_LOCKED;
            $rekrutmen->lowongan_id = $lamaran->lowongan_id;
            $rekrutmen->locked_at = now();
            $rekrutmen->save();

            $lamaran->status = Lamaran::STATUS_DIPROSES;
            $lamaran->save();

            $auditLog->log($request->user(), 'lamaran.lock', $lamaran, [
                'mitra_pengguna_id' => $lamaran->mitra_pengguna_id,
                'lowongan_id' => $lamaran->lowongan_id,
            ], $request);

            $this->notifyPerawat($lamaran, 'Lamaran Anda sedang diproses.');

            return back()->with('status', 'Lamaran dikunci dan ditandai diproses.');
        });
    }

    public function reject(LamaranActionRequest $request, AuditLogService $auditLog, PerawatStatusService $statusService): RedirectResponse
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $auditLog, $statusService) {
            $lamaran = $this->resolveLamaran($request);
            $lamaran = Lamaran::where('id', $lamaran->id)->lockForUpdate()->firstOrFail();

            if ($this->isFinal($lamaran)) {
                return back()->withErrors(['status' => 'Lamaran sudah berstatus final.']);
            }

            $rekrutmen = Rekrutmen::where('mitra_pengguna_id', $lamaran->mitra_pengguna_id)
                ->where('perawat_nik', $lamaran->perawat_nik)
                ->lockForUpdate()
                ->first();

            if ($rekrutmen && $rekrutmen->status !== Rekrutmen::STATUS_HIRED) {
                $rekrutmen->status = Rekrutmen::STATUS_RELEASED;
                $rekrutmen->lowongan_id = $lamaran->lowongan_id;
                $rekrutmen->released_at = now();
                $rekrutmen->save();
            }
            $lamaran->status = Lamaran::STATUS_DITOLAK;
            $lamaran->save();

            $auditLog->log($request->user(), 'lamaran.reject', $lamaran, [
                'mitra_pengguna_id' => $lamaran->mitra_pengguna_id,
                'lowongan_id' => $lamaran->lowongan_id,
            ], $request);

            $perawat = $lamaran->perawat ?? Perawat::where('nik', $lamaran->perawat_nik)->first();
            if ($perawat) {
                $statusService->refresh($perawat);
            }

            $this->notifyPerawat($lamaran, 'Lamaran Anda ditolak.');

            return back()->with('status', 'Lamaran ditolak.');
        });
    }

    public function hire(LamaranActionRequest $request, PerawatStatusService $statusService, AuditLogService $auditLog, RiwayatPekerjaanService $riwayatService): RedirectResponse
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $statusService, $auditLog, $riwayatService) {
            $lamaran = $this->resolveLamaran($request);
            $lamaran = Lamaran::where('id', $lamaran->id)->lockForUpdate()->firstOrFail();

            if ($this->isFinal($lamaran)) {
                return back()->withErrors(['status' => 'Lamaran sudah berstatus final.']);
            }

            $this->ensureNotLockedByOtherMitra($lamaran->perawat_nik, $lamaran->mitra_pengguna_id);

            $rekrutmen = Rekrutmen::where('mitra_pengguna_id', $lamaran->mitra_pengguna_id)
                ->where('perawat_nik', $lamaran->perawat_nik)
                ->lockForUpdate()
                ->first() ?? new Rekrutmen([
                    'mitra_pengguna_id' => $lamaran->mitra_pengguna_id,
                    'perawat_nik' => $lamaran->perawat_nik,
                ]);

            $rekrutmen->status = Rekrutmen::STATUS_HIRED;
            $rekrutmen->lowongan_id = $lamaran->lowongan_id;
            $rekrutmen->hired_at = now();
            $rekrutmen->save();

            $lamaran->status = Lamaran::STATUS_DITERIMA;
            $lamaran->save();

            $auditLog->log($request->user(), 'lamaran.hire', $lamaran, [
                'mitra_pengguna_id' => $lamaran->mitra_pengguna_id,
                'lowongan_id' => $lamaran->lowongan_id,
                'rekrutmen_id' => $rekrutmen->id,
            ], $request);

            $perawat = $lamaran->perawat ?? Perawat::where('nik', $lamaran->perawat_nik)->first();
            if ($perawat) {
                $riwayat = $riwayatService->startEmployment($request->user(), $perawat, $lamaran->mitra_pengguna_id, $lamaran->lowongan);
                if ($riwayat->wasRecentlyCreated) {
                    $auditLog->log($request->user(), 'riwayat_pekerjaan.start', $riwayat, [
                        'mitra_pengguna_id' => $lamaran->mitra_pengguna_id,
                        'perawat_nik' => $lamaran->perawat_nik,
                    ], $request);
                }
                $statusService->refresh($perawat);
            }

            $this->notifyPerawat($lamaran, 'Lamaran Anda diterima.');

            return back()->with('status', 'Lamaran diterima dan kandidat di-hire.');
        });
    }

    private function resolveLamaran(LamaranActionRequest $request): Lamaran
    {
        $mitraId = $request->validated('mitra_pengguna_id');

        $lamaran = Lamaran::with('lowongan', 'perawat')->findOrFail($request->validated('lamaran_id'));

        $this->authorize('manage', $lamaran);

        if ($lamaran->mitra_pengguna_id !== $mitraId) {
            abort(403);
        }

        return $lamaran;
    }

    private function isFinal(Lamaran $lamaran): bool
    {
        return in_array($lamaran->status, [Lamaran::STATUS_DITERIMA, Lamaran::STATUS_DITOLAK, Lamaran::STATUS_DIBATALKAN], true);
    }

    private function ensureNotLockedByOtherMitra(?string $perawatNik, string $mitraId): void
    {
        if (!$perawatNik) {
            return;
        }

        $exists = Rekrutmen::where('perawat_nik', $perawatNik)
            ->whereIn('status', [Rekrutmen::STATUS_LOCKED, Rekrutmen::STATUS_HIRED])
            ->where('mitra_pengguna_id', '!=', $mitraId)
            ->exists();

        if ($exists) {
            abort(409, 'Kandidat sedang terkunci oleh mitra lain.');
        }
    }

    private function notifyPerawat(Lamaran $lamaran, string $message): void
    {
        $user = User::where('perawat_nik', $lamaran->perawat_nik)
            ->where('role', User::ROLE_PERAWAT)
            ->first();

        if ($user) {
            $user->notify(new LamaranStatusNotification($lamaran, $message));
        }
    }
}
