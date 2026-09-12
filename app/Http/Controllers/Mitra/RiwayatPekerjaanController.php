<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\CompleteRiwayatPekerjaanRequest;
use App\Http\Requests\Mitra\StoreRiwayatPekerjaanRequest;
use App\Http\Requests\Mitra\UpdateKinerjaRequest;
use App\Http\Requests\Mitra\VerifyRiwayatPekerjaanRequest;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\User;
use App\Notifications\RiwayatPekerjaanStatusNotification;
use App\Services\AuditLogService;
use App\Services\PerawatStatusService;
use App\Services\VerificationService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatPekerjaanController extends Controller
{
    public function create(): View
    {
        $mitra = auth()->user()->mitraPengguna()->get();

        return view('mitra.riwayat_pekerjaan.create', compact('mitra'));
    }

    public function pending(Request $request): View
    {
        $mitraIds = $request->user()->mitraPengguna()->pluck('mitra_pengguna.id');

        $riwayat = RiwayatPekerjaan::with(['perawat', 'mitraPengguna', 'verifiedBy'])
            ->whereIn('mitra_pengguna_id', $mitraIds)
            ->where('verifikasi_status', RiwayatPekerjaan::VERIF_PENDING)
            ->latest()
            ->paginate(15);

        return view('mitra.riwayat_pekerjaan.pending', compact('riwayat'));
    }

    public function active(Request $request): View
    {
        $mitraIds = $request->user()->mitraPengguna()->pluck('mitra_pengguna.id');

        $riwayat = RiwayatPekerjaan::with(['perawat', 'mitraPengguna'])
            ->whereIn('mitra_pengguna_id', $mitraIds)
            ->where('status_pekerjaan', RiwayatPekerjaan::STATUS_AKTIF)
            ->where(function ($q) {
                $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                    ->orWhere('is_verified', true);
            })
            ->latest()
            ->paginate(15);

        return view('mitra.riwayat_pekerjaan.active', compact('riwayat'));
    }

    public function kinerja(Request $request): View
    {
        $mitraIds = $request->user()->mitraPengguna()->pluck('mitra_pengguna.id');

        $aktif = RiwayatPekerjaan::with(['perawat', 'mitraPengguna'])
            ->whereIn('mitra_pengguna_id', $mitraIds)
            ->where('status_pekerjaan', RiwayatPekerjaan::STATUS_AKTIF)
            ->where(function ($q) {
                $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                    ->orWhere('is_verified', true);
            })
            ->latest()
            ->paginate(10, ['*'], 'aktif_page')
            ->withQueryString();

        $riwayat = RiwayatPekerjaan::with(['perawat', 'mitraPengguna'])
            ->whereIn('mitra_pengguna_id', $mitraIds)
            ->whereIn('status_pekerjaan', [RiwayatPekerjaan::STATUS_SELESAI, RiwayatPekerjaan::STATUS_RESIGN])
            ->latest('tanggal_selesai')
            ->paginate(10, ['*'], 'riwayat_page')
            ->withQueryString();

        return view('mitra.riwayat_pekerjaan.kinerja', compact('aktif', 'riwayat'));
    }

    public function editKinerja(RiwayatPekerjaan $riwayat): View
    {
        $this->authorize('updateStatus', $riwayat);

        if (!in_array($riwayat->status_pekerjaan, [RiwayatPekerjaan::STATUS_SELESAI, RiwayatPekerjaan::STATUS_RESIGN], true)) {
            abort(403);
        }

        $riwayat->load(['perawat', 'mitraPengguna']);

        return view('mitra.riwayat_pekerjaan.edit_kinerja', compact('riwayat'));
    }

    public function updateKinerja(UpdateKinerjaRequest $request, RiwayatPekerjaan $riwayat, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('updateStatus', $riwayat);

        if (!in_array($riwayat->status_pekerjaan, [RiwayatPekerjaan::STATUS_SELESAI, RiwayatPekerjaan::STATUS_RESIGN], true)) {
            return back()->withErrors(['status' => 'Riwayat pekerjaan belum selesai/resign.']);
        }

        $riwayat->penilaian_kinerja = $request->validated('penilaian_kinerja');
        $riwayat->kinerja_rating = $request->validated('kinerja_rating');
        $riwayat->kinerja_score = $request->validated('kinerja_score');
        $riwayat->keterangan = $request->validated('keterangan');
        $riwayat->save();

        $auditLog->log($request->user(), 'riwayat_pekerjaan.kinerja_update', $riwayat, [
            'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
            'perawat_nik' => $riwayat->perawat_nik,
        ], $request);

        $this->notifyPerawat($riwayat, 'Evaluasi kinerja Anda telah diperbarui.');

        return redirect()
            ->route('mitra.riwayat_kinerja.index')
            ->with('status', 'Evaluasi kinerja berhasil diperbarui.');
    }

    public function exportKinerja(Request $request): Response
    {
        $mitraIds = $request->user()->mitraPengguna()->pluck('mitra_pengguna.id');

        $query = RiwayatPekerjaan::with(['perawat', 'mitraPengguna'])
            ->whereIn('mitra_pengguna_id', $mitraIds)
            ->whereIn('status_pekerjaan', [RiwayatPekerjaan::STATUS_SELESAI, RiwayatPekerjaan::STATUS_RESIGN])
            ->orderByDesc('tanggal_selesai');

        $filename = 'riwayat-kinerja-'.now()->format('Ymd_His').'.csv';

        $callback = static function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Perawat',
                'Mitra',
                'Posisi',
                'Status',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Penilaian',
                'Rating (1-5)',
                'Skor (0-100)',
                'Catatan',
            ]);

            foreach ($query->cursor() as $item) {
                fputcsv($handle, [
                    $item->perawat?->nama,
                    $item->mitraPengguna?->nama,
                    $item->posisi,
                    $item->status_pekerjaan,
                    optional($item->tanggal_mulai)->format('Y-m-d'),
                    optional($item->tanggal_selesai)->format('Y-m-d'),
                    $item->penilaian_kinerja,
                    $item->kinerja_rating,
                    $item->kinerja_score,
                    $item->keterangan,
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function store(StoreRiwayatPekerjaanRequest $request, PerawatStatusService $statusService, AuditLogService $auditLog): JsonResponse|RedirectResponse
    {
        $mitraId = $request->validated('mitra_pengguna_id');

        $this->authorize('create', [RiwayatPekerjaan::class, $mitraId]);

        $perawat = $this->resolvePerawat($request->validated());

        $riwayat = RiwayatPekerjaan::create([
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitraId,
            'posisi' => $request->validated('posisi'),
            'unit' => $request->validated('unit'),
            'tanggal_mulai' => $request->validated('tanggal_mulai'),
            'tanggal_selesai' => ($request->validated('status_pekerjaan') ?? RiwayatPekerjaan::STATUS_AKTIF) === 'aktif' ? null : $request->validated('tanggal_selesai'),
            'status_pekerjaan' => $request->validated('status_pekerjaan') ?? RiwayatPekerjaan::STATUS_AKTIF,
            'keterangan' => $request->validated('keterangan'),
            'penilaian_kinerja' => $request->validated('penilaian_kinerja'),
            'kinerja_rating' => $request->validated('kinerja_rating'),
            'kinerja_score' => $request->validated('kinerja_score'),
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'sumber_data' => RiwayatPekerjaan::SOURCE_MITRA,
            'verifikasi_status' => RiwayatPekerjaan::VERIF_VERIFIED,
            'source_system' => 'mitra_portal',
            'source_actor' => 'mitra',
        ]);

        $auditLog->log($request->user(), 'riwayat_pekerjaan.create', $riwayat, [
            'mitra_pengguna_id' => $mitraId,
            'perawat_nik' => $perawat->nik,
        ], $request);

        $statusService->refresh($perawat);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Riwayat pekerjaan berhasil disimpan.',
                'riwayat_pekerjaan_id' => $riwayat->id,
            ]);
        }

        return redirect()
            ->route('mitra.riwayat_pekerjaan.create')
            ->with('status', 'Riwayat pekerjaan berhasil disimpan.');
    }

    public function verify(VerifyRiwayatPekerjaanRequest $request, RiwayatPekerjaan $riwayat, PerawatStatusService $statusService, AuditLogService $auditLog, VerificationService $verification): RedirectResponse
    {
        $this->authorize('verify', $riwayat);

        if ($riwayat->verifikasi_status !== RiwayatPekerjaan::VERIF_PENDING) {
            return back()->withErrors(['status' => 'Riwayat pekerjaan sudah diproses.']);
        }

        $context = [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'extra' => [
                'is_verified' => true,
            ],
        ];
        if ($request->filled('penilaian_kinerja')) {
            $context['extra']['penilaian_kinerja'] = $request->validated('penilaian_kinerja');
        }
        if ($request->filled('keterangan')) {
            $context['note'] = $request->validated('keterangan');
        }

        $verification->apply($riwayat, VerificationService::ACTION_APPROVE, [
            'status_field' => 'verifikasi_status',
            'approved_value' => RiwayatPekerjaan::VERIF_VERIFIED,
            'rejected_value' => RiwayatPekerjaan::VERIF_REJECTED,
            'reviewed_by_field' => 'verified_by',
            'reviewed_at_field' => 'verified_at',
            'note_field' => 'keterangan',
            'approved_payload' => [
                'rejected_reason' => null,
            ],
        ], $context);

        $auditLog->log($request->user(), 'riwayat_pekerjaan.verify', $riwayat, [
            'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
            'perawat_nik' => $riwayat->perawat_nik,
        ], $request);

        $statusService->refresh($riwayat->perawat);

        $this->notifyPerawat($riwayat, 'Riwayat pekerjaan Anda telah diverifikasi.');

        return back()->with('status', 'Riwayat pekerjaan berhasil diverifikasi.');
    }

    public function reject(VerifyRiwayatPekerjaanRequest $request, RiwayatPekerjaan $riwayat, AuditLogService $auditLog, VerificationService $verification): RedirectResponse
    {
        $this->authorize('reject', $riwayat);

        if ($riwayat->verifikasi_status !== RiwayatPekerjaan::VERIF_PENDING) {
            return back()->withErrors(['status' => 'Riwayat pekerjaan sudah diproses.']);
        }

        $context = [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'extra' => [
                'is_verified' => false,
            ],
        ];
        if ($request->filled('keterangan')) {
            $context['note'] = $request->validated('keterangan');
        }

        $verification->apply($riwayat, VerificationService::ACTION_REJECT, [
            'status_field' => 'verifikasi_status',
            'approved_value' => RiwayatPekerjaan::VERIF_VERIFIED,
            'rejected_value' => RiwayatPekerjaan::VERIF_REJECTED,
            'reviewed_by_field' => 'verified_by',
            'reviewed_at_field' => 'verified_at',
            'note_field' => 'rejected_reason',
        ], $context);

        if ($request->filled('keterangan')) {
            $riwayat->keterangan = $request->validated('keterangan');
            $riwayat->save();
        }

        $auditLog->log($request->user(), 'riwayat_pekerjaan.reject', $riwayat, [
            'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
            'perawat_nik' => $riwayat->perawat_nik,
        ], $request);

        $this->notifyPerawat($riwayat, 'Riwayat pekerjaan Anda ditolak.');

        return back()->with('status', 'Riwayat pekerjaan ditolak.');
    }

    public function complete(CompleteRiwayatPekerjaanRequest $request, RiwayatPekerjaan $riwayat, PerawatStatusService $statusService, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('updateStatus', $riwayat);

        if ($riwayat->status_pekerjaan !== RiwayatPekerjaan::STATUS_AKTIF) {
            return back()->withErrors(['status' => 'Riwayat pekerjaan tidak dalam status aktif.']);
        }

        if ($riwayat->verifikasi_status !== RiwayatPekerjaan::VERIF_VERIFIED && !$riwayat->is_verified) {
            return back()->withErrors(['status' => 'Riwayat pekerjaan belum terverifikasi.']);
        }

        $tanggalSelesai = $request->date('tanggal_selesai');
        if ($riwayat->tanggal_mulai && $tanggalSelesai && $tanggalSelesai->lt($riwayat->tanggal_mulai)) {
            return back()->withErrors(['tanggal_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        $riwayat->status_pekerjaan = $request->validated('status_pekerjaan');
        $riwayat->tanggal_selesai = $tanggalSelesai;
        $riwayat->penilaian_kinerja = $request->validated('penilaian_kinerja');
        $riwayat->kinerja_rating = $request->validated('kinerja_rating');
        $riwayat->kinerja_score = $request->validated('kinerja_score');
        $riwayat->keterangan = $request->validated('keterangan');
        $riwayat->save();

        $rekrutmen = Rekrutmen::where('perawat_nik', $riwayat->perawat_nik)
            ->where('mitra_pengguna_id', $riwayat->mitra_pengguna_id)
            ->where('status', Rekrutmen::STATUS_HIRED)
            ->first();

        if ($rekrutmen) {
            $rekrutmen->status = Rekrutmen::STATUS_RELEASED;
            $rekrutmen->released_at = now();
            $rekrutmen->save();
        }

        $auditLog->log($request->user(), 'riwayat_pekerjaan.complete', $riwayat, [
            'mitra_pengguna_id' => $riwayat->mitra_pengguna_id,
            'perawat_nik' => $riwayat->perawat_nik,
            'status_pekerjaan' => $riwayat->status_pekerjaan,
        ], $request);

        $statusService->refresh($riwayat->perawat);
        $this->notifyPerawat($riwayat, 'Riwayat pekerjaan Anda telah diperbarui ('.$riwayat->status_pekerjaan.').');

        return back()->with('status', 'Status riwayat pekerjaan berhasil diperbarui.');
    }

    private function resolvePerawat(array $data): Perawat
    {
        $nik = $data['perawat_nik'] ?? $data['nik'] ?? null;
        if ($nik) {
            return Perawat::where('nik', $nik)->firstOrFail();
        }

        if (!empty($data['perawat_id'])) {
            return Perawat::findOrFail($data['perawat_id']);
        }

        abort(422, 'Perawat tidak ditemukan.');
    }

    private function notifyPerawat(RiwayatPekerjaan $riwayat, string $message): void
    {
        $user = User::where('perawat_nik', $riwayat->perawat_nik)
            ->where('role', User::ROLE_PERAWAT)
            ->first();

        if ($user) {
            $user->notify(new RiwayatPekerjaanStatusNotification($riwayat, $message));
        }
    }
}
