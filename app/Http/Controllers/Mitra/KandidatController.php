<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\LockKandidatRequest;
use App\Models\DataLegalitas;
use App\Models\Lowongan;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\SertifikasiKompetensi;
use App\Services\AuditLogService;
use App\Services\CvPdfService;
use App\Services\PerawatStatusService;
use App\Services\RiwayatPekerjaanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KandidatController extends Controller
{
    public function index(Request $request): View
    {
        $viewerMitraIds = $request->user()
            ->mitraPengguna()
            ->pluck('mitra_pengguna.id')
            ->all();

        $sort = $request->string('sort')->toString();
        $driver = DB::getDriverName();

        $query = Perawat::query()
            ->with(['dataLegalitas', 'sertifikasi', 'riwayatPendidikan'])
            ->where('status_kesiapan', Perawat::KESIAPAN_READY);

        // Anti-poaching: kandidat yang sudah hired tidak muncul kecuali hired oleh mitra milik viewer.
        $query->whereDoesntHave('rekrutmen', function ($q) use ($viewerMitraIds) {
            $q->where('status', Rekrutmen::STATUS_HIRED);

            if ($viewerMitraIds !== []) {
                $q->whereNotIn('mitra_pengguna_id', $viewerMitraIds);
            }
        });

        if ($request->filled('nama')) {
            $query->where('nama', 'like', '%'.$request->string('nama').'%');
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->string('jenis_kelamin'));
        }

        if ($request->filled('status_str')) {
            $query->whereHas('dataLegalitas', function ($q) use ($request) {
                $q->where('status_str', $request->string('status_str'));
            });
        }

        if ($request->filled('status_ketenagakerjaan')) {
            $query->where('status_ketenagakerjaan', $request->string('status_ketenagakerjaan'));
        }

        if ($request->filled('status_ketersediaan')) {
            $query->where('status_ketersediaan', $request->string('status_ketersediaan'));
        }

        if ($request->filled('kota')) {
            $query->where('kota', $request->string('kota'));
        }

        if ($request->filled('provinsi')) {
            $query->where('provinsi', $request->string('provinsi'));
        }

        if ($request->filled('jenjang')) {
            $query->whereHas('riwayatPendidikan', function ($q) use ($request) {
                $q->where('jenjang', $request->string('jenjang'));
            });
        }

        if ($request->filled('program_studi')) {
            $query->whereHas('riwayatPendidikan', function ($q) use ($request) {
                $q->where('program_studi', 'like', '%'.$request->string('program_studi').'%');
            });
        }

        if ($request->filled('min_ipk')) {
            $minIpk = (float) $request->input('min_ipk');
            $query->whereHas('riwayatPendidikan', function ($q) use ($minIpk) {
                $q->whereNotNull('ipk')->where('ipk', '>=', $minIpk);
            });
        }

        if ($request->filled('sertifikat')) {
            $query->whereHas('sertifikasi', function ($q) use ($request) {
                $q->where('nama_sertifikat', 'like', '%'.$request->string('sertifikat').'%');
                $q->whereIn('status_verifikasi', SertifikasiKompetensi::verifiedStatuses());
            });
        }

        if ($request->filled('sertifikat_verifikasi')) {
            $query->whereHas('sertifikasi', function ($q) use ($request) {
                $q->where('nama_sertifikat', $request->string('sertifikat_verifikasi'))
                    ->whereIn('status_verifikasi', SertifikasiKompetensi::verifiedStatuses());
            });
        }

        if ($request->filled('min_pengalaman')) {
            $years = (int) $request->input('min_pengalaman');
            if ($years > 0) {
                $cutoff = now()->subYears($years)->toDateString();
                $query->whereHas('riwayatPekerjaan', function ($q) use ($cutoff) {
                    $q->whereNotNull('tanggal_mulai')
                        ->where('tanggal_mulai', '<=', $cutoff)
                        ->where(function ($q) {
                            $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                                ->orWhere('is_verified', true);
                        });
                });
            }
        }

        if ($request->filled('lokasi')) {
            $lokasi = $request->string('lokasi');
            $query->where(function ($q) use ($lokasi) {
                $q->where('alamat', 'like', '%'.$lokasi.'%')
                    ->orWhere('tempat_lahir', 'like', '%'.$lokasi.'%');
            });
        }

        $needsExpJoin = $request->filled('min_pengalaman_total')
            || in_array($sort, ['pengalaman_desc', 'pengalaman_asc'], true);
        $needsLegalitasJoin = in_array($sort, ['str_expired', 'str_active'], true);
        $needsIpkMax = in_array($sort, ['ipk_desc', 'ipk_asc'], true);
        $useSqliteExperienceFallback = $driver === 'sqlite' && $needsExpJoin;
        $experienceTotals = [];
        if ($useSqliteExperienceFallback) {
            $experienceTotals = $this->experienceTotalsMap($driver);
        }

        if (($needsExpJoin && !$useSqliteExperienceFallback) || $needsLegalitasJoin) {
            $query->select('perawat.*');
        }

        if ($needsExpJoin && !$useSqliteExperienceFallback) {
            $experienceSql = $this->experienceTotalsSql($driver);
            $query->leftJoin(DB::raw("({$experienceSql}) as exp"), 'exp.perawat_nik', '=', 'perawat.nik')
                ->addSelect(DB::raw('COALESCE(exp.total_months, 0) as total_pengalaman_bulan'));
        }

        if ($needsLegalitasJoin) {
            $query->leftJoin('data_legalitas as dl', 'dl.perawat_nik', '=', 'perawat.nik');
        }

        if ($request->filled('min_pengalaman_total')) {
            $years = (float) $request->input('min_pengalaman_total');
            if ($years > 0) {
                $months = $years * 12;
                if ($useSqliteExperienceFallback) {
                    $eligibleNik = collect($experienceTotals)
                        ->filter(fn (float $totalMonths) => $totalMonths >= $months)
                        ->keys()
                        ->values()
                        ->all();

                    if ($eligibleNik === []) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('perawat.nik', $eligibleNik);
                    }
                } else {
                    $query->whereRaw('COALESCE(exp.total_months, 0) >= ?', [$months]);
                }
            }
        }

        if ($needsIpkMax) {
            $query->withMax('riwayatPendidikan', 'ipk');
        }

        switch ($sort) {
            case 'ipk_desc':
                $query->orderByDesc('riwayat_pendidikan_max_ipk');
                break;
            case 'ipk_asc':
                $query->orderBy('riwayat_pendidikan_max_ipk');
                break;
            case 'pengalaman_desc':
                if ($useSqliteExperienceFallback) {
                    $this->applyNurseOrderFromExperienceMap($query, $experienceTotals, true);
                } else {
                    $query->orderByDesc('total_pengalaman_bulan');
                }
                break;
            case 'pengalaman_asc':
                if ($useSqliteExperienceFallback) {
                    $this->applyNurseOrderFromExperienceMap($query, $experienceTotals, false);
                } else {
                    $query->orderBy('total_pengalaman_bulan');
                }
                break;
            case 'str_expired':
                $query->orderByRaw(
                    'CASE
                        WHEN dl.status_str = ? THEN 0
                        WHEN dl.status_str = ? THEN 1
                        WHEN dl.status_str = ? THEN 2
                        ELSE 3
                    END',
                    [
                        DataLegalitas::STATUS_STR_EXPIRED,
                        DataLegalitas::STATUS_STR_HOLD,
                        DataLegalitas::STATUS_STR_AKTIF,
                    ]
                );
                break;
            case 'str_active':
                $query->orderByRaw(
                    'CASE
                        WHEN dl.status_str = ? THEN 0
                        WHEN dl.status_str = ? THEN 1
                        WHEN dl.status_str = ? THEN 2
                        ELSE 3
                    END',
                    [
                        DataLegalitas::STATUS_STR_AKTIF,
                        DataLegalitas::STATUS_STR_HOLD,
                        DataLegalitas::STATUS_STR_EXPIRED,
                    ]
                );
                break;
            case 'nama_asc':
                $query->orderBy('nama');
                break;
            default:
                $query->latest();
                break;
        }

        $sertifikatOptions = SertifikasiKompetensi::query()
            ->whereIn('status_verifikasi', SertifikasiKompetensi::verifiedStatuses())
            ->whereNotNull('nama_sertifikat')
            ->select('nama_sertifikat')
            ->distinct()
            ->orderBy('nama_sertifikat')
            ->limit(200)
            ->pluck('nama_sertifikat');

        $kotaOptions = Perawat::query()
            ->where('status_kesiapan', Perawat::KESIAPAN_READY)
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->select('kota')
            ->distinct()
            ->orderBy('kota')
            ->limit(200)
            ->pluck('kota');

        $provinsiOptions = Perawat::query()
            ->where('status_kesiapan', Perawat::KESIAPAN_READY)
            ->whereNotNull('provinsi')
            ->where('provinsi', '!=', '')
            ->select('provinsi')
            ->distinct()
            ->orderBy('provinsi')
            ->limit(200)
            ->pluck('provinsi');

        $perawat = $query->paginate(15)->withQueryString();

        $activeRecruitmentByNik = $this->resolveActiveRecruitmentByNik($perawat->getCollection()->pluck('nik')->all());

        return view('mitra.kandidat.index', compact(
            'perawat',
            'sertifikatOptions',
            'kotaOptions',
            'provinsiOptions',
            'activeRecruitmentByNik',
            'viewerMitraIds'
        ));
    }

    public function show(Request $request, Perawat $perawat): View
    {
        $this->ensureCandidateVisibleToViewer($request, $perawat);
        $viewerMitraIds = $request->user()
            ->mitraPengguna()
            ->pluck('mitra_pengguna.id')
            ->all();

        $perawat->load(['dataLegalitas', 'riwayatPendidikan.institusiPendidikan', 'sertifikasi.lembagaPelatihan', 'riwayatPekerjaan.mitraPengguna']);
        $activeRecruitment = $this->resolveActiveRecruitmentByNik([$perawat->nik])[$perawat->nik] ?? null;

        return view('mitra.kandidat.show', compact('perawat', 'activeRecruitment', 'viewerMitraIds'));
    }

    public function cvPdf(Request $request, Perawat $perawat, CvPdfService $pdfService)
    {
        $this->ensureCandidateVisibleToViewer($request, $perawat);

        $perawat->load([
            'dataLegalitas',
            'riwayatPendidikan.institusiPendidikan',
            'sertifikasi.lembagaPelatihan',
            'riwayatPekerjaan' => function ($query) {
                $query->where(function ($q) {
                    $q->where('verifikasi_status', RiwayatPekerjaan::VERIF_VERIFIED)
                        ->orWhere('is_verified', true);
                });
            },
            'riwayatPekerjaan.mitraPengguna',
        ]);

        try {
            $pdf = $pdfService->generate($perawat);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['pdf' => $e->getMessage()]);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="cv-terverifikasi-'.$perawat->nik.'.pdf"',
        ]);
    }

    private function ensureCandidateVisibleToViewer(Request $request, Perawat $perawat): void
    {
        if ($perawat->status_kesiapan !== Perawat::KESIAPAN_READY) {
            abort(403);
        }

        $viewerMitraIds = $request->user()
            ->mitraPengguna()
            ->pluck('mitra_pengguna.id')
            ->all();

        $isHiredByOtherMitra = $perawat->rekrutmen()
            ->where('status', Rekrutmen::STATUS_HIRED)
            ->when(
                $viewerMitraIds !== [],
                fn ($query) => $query->whereNotIn('mitra_pengguna_id', $viewerMitraIds)
            )
            ->exists();

        if ($isHiredByOtherMitra) {
            abort(403);
        }
    }

    public function lock(LockKandidatRequest $request): RedirectResponse
    {
        $user = $request->user();
        $mitraId = $request->validated('mitra_pengguna_id');

        if (!$user->mitraPengguna()->where('mitra_pengguna.id', $mitraId)->exists()) {
            abort(403);
        }

        $this->ensureLowonganBelongsToMitra($request->validated('lowongan_id'), $mitraId);

        return DB::transaction(function () use ($request, $mitraId) {
            $perawat = $this->resolvePerawat($request->validated());
            $perawat = Perawat::where('nik', $perawat->nik)->lockForUpdate()->firstOrFail();

            if ($perawat->status_kesiapan !== Perawat::KESIAPAN_READY) {
                return back()->withErrors(['perawat_nik' => 'Perawat belum ready to hire.']);
            }

            $this->ensureNotLockedByOtherMitra($perawat->nik, $mitraId);

            $rekrutmen = Rekrutmen::updateOrCreate([
                'mitra_pengguna_id' => $mitraId,
                'perawat_nik' => $perawat->nik,
            ], [
                'lowongan_id' => $request->validated('lowongan_id'),
                'status' => Rekrutmen::STATUS_LOCKED,
                'locked_at' => now(),
            ]);

            return redirect()
                ->route('mitra.kandidat.show', $perawat)
                ->with('status', 'Kandidat berhasil dikunci.');
        });
    }

    public function hire(LockKandidatRequest $request, PerawatStatusService $statusService, RiwayatPekerjaanService $riwayatService, AuditLogService $auditLog): RedirectResponse
    {
        $user = $request->user();
        $mitraId = $request->validated('mitra_pengguna_id');

        if (!$user->mitraPengguna()->where('mitra_pengguna.id', $mitraId)->exists()) {
            abort(403);
        }

        $this->ensureLowonganBelongsToMitra($request->validated('lowongan_id'), $mitraId);

        return DB::transaction(function () use ($request, $mitraId, $statusService, $riwayatService, $auditLog) {
            $perawat = $this->resolvePerawat($request->validated());
            $perawat = Perawat::where('nik', $perawat->nik)->lockForUpdate()->firstOrFail();

            if ($perawat->status_kesiapan !== Perawat::KESIAPAN_READY) {
                return back()->withErrors(['perawat_nik' => 'Perawat belum ready to hire.']);
            }

            $this->ensureNotLockedByOtherMitra($perawat->nik, $mitraId);

            $rekrutmen = Rekrutmen::updateOrCreate([
                'mitra_pengguna_id' => $mitraId,
                'perawat_nik' => $perawat->nik,
            ], [
                'status' => Rekrutmen::STATUS_HIRED,
                'lowongan_id' => $request->validated('lowongan_id'),
                'hired_at' => now(),
            ]);

            if ($rekrutmen->lowongan_id) {
                \App\Models\Lamaran::where('perawat_nik', $perawat->nik)
                    ->where('lowongan_id', $rekrutmen->lowongan_id)
                    ->update(['status' => \App\Models\Lamaran::STATUS_DITERIMA]);
            }

            $lowongan = $rekrutmen->lowongan_id ? $rekrutmen->lowongan : null;
            $riwayat = $riwayatService->startEmployment($request->user(), $perawat, $mitraId, $lowongan);
            if ($riwayat->wasRecentlyCreated) {
                $auditLog->log($request->user(), 'riwayat_pekerjaan.start', $riwayat, [
                    'mitra_pengguna_id' => $mitraId,
                    'perawat_nik' => $perawat->nik,
                ], $request);
            }
            $statusService->refresh($perawat);

            return redirect()
                ->route('mitra.kandidat.show', $perawat)
                ->with('status', 'Kandidat berhasil di-hire.');
        });
    }

    public function release(LockKandidatRequest $request, PerawatStatusService $statusService): RedirectResponse
    {
        $user = $request->user();
        $mitraId = $request->validated('mitra_pengguna_id');

        if (!$user->mitraPengguna()->where('mitra_pengguna.id', $mitraId)->exists()) {
            abort(403);
        }

        $this->ensureLowonganBelongsToMitra($request->validated('lowongan_id'), $mitraId);

        return DB::transaction(function () use ($request, $mitraId, $statusService) {
            $perawat = $this->resolvePerawat($request->validated());
            $perawat = Perawat::where('nik', $perawat->nik)->lockForUpdate()->firstOrFail();

            $rekrutmen = Rekrutmen::updateOrCreate([
                'mitra_pengguna_id' => $mitraId,
                'perawat_nik' => $perawat->nik,
            ], [
                'status' => Rekrutmen::STATUS_RELEASED,
                'lowongan_id' => $request->validated('lowongan_id'),
                'released_at' => now(),
            ]);

            $statusService->refresh($perawat);

            return redirect()
                ->route('mitra.kandidat.show', $perawat)
                ->with('status', 'Kandidat berhasil dilepas.');
        });
    }

    private function experienceTotalsSql(string $driver): string
    {
        $currentDate = $driver === 'sqlite' ? "date('now')" : 'CURDATE()';
        $diffExpr = $driver === 'sqlite'
            ? "(julianday(end_date) - julianday(start_date)) / 30.0"
            : "DATEDIFF(end_date, start_date) / 30.0";
        $quotedStatus = DB::connection()->getPdo()->quote(RiwayatPekerjaan::VERIF_VERIFIED);

        return <<<SQL
            WITH base AS (
                SELECT perawat_nik,
                    tanggal_mulai AS start_date,
                    COALESCE(tanggal_selesai, {$currentDate}) AS end_date
                FROM riwayat_pekerjaan
                WHERE tanggal_mulai IS NOT NULL
                  AND (verifikasi_status = {$quotedStatus} OR is_verified = 1)
            ),
            ordered AS (
                SELECT *,
                    MAX(end_date) OVER (PARTITION BY perawat_nik ORDER BY start_date
                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS running_end
                FROM base
            ),
            flagged AS (
                SELECT *,
                    CASE
                        WHEN LAG(running_end) OVER (PARTITION BY perawat_nik ORDER BY start_date) IS NULL THEN 0
                        WHEN start_date > LAG(running_end) OVER (PARTITION BY perawat_nik ORDER BY start_date) THEN 1
                        ELSE 0
                    END AS is_new_group
                FROM ordered
            ),
            grouped AS (
                SELECT *,
                    SUM(is_new_group) OVER (PARTITION BY perawat_nik ORDER BY start_date
                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS grp
                FROM flagged
            ),
            merged AS (
                SELECT perawat_nik,
                    grp,
                    MIN(start_date) AS start_date,
                    MAX(end_date) AS end_date
                FROM grouped
                GROUP BY perawat_nik, grp
            )
            SELECT perawat_nik,
                SUM({$diffExpr}) AS total_months
            FROM merged
            GROUP BY perawat_nik
            SQL;
    }

    /**
     * @return array<string,float>
     */
    private function experienceTotalsMap(string $driver): array
    {
        $sql = $this->experienceTotalsSql($driver);

        return DB::table(DB::raw("({$sql}) as exp"))
            ->pluck('total_months', 'perawat_nik')
            ->map(fn ($months) => (float) $months)
            ->all();
    }

    /**
     * @param array<string,float> $experienceTotals
     */
    private function applyNurseOrderFromExperienceMap($query, array $experienceTotals, bool $descending): void
    {
        if ($experienceTotals === []) {
            $query->latest();
            return;
        }

        $sortedNik = collect($experienceTotals)
            ->sort($descending ? fn ($a, $b) => $b <=> $a : fn ($a, $b) => $a <=> $b)
            ->keys()
            ->values()
            ->all();

        if ($sortedNik === []) {
            $query->latest();
            return;
        }

        $caseSql = 'CASE perawat.nik';
        $bindings = [];
        foreach ($sortedNik as $index => $nik) {
            $caseSql .= " WHEN ? THEN {$index}";
            $bindings[] = (string) $nik;
        }
        $caseSql .= ' ELSE '.count($sortedNik).' END';

        $query->orderByRaw($caseSql, $bindings);
    }

    private function ensureNotLockedByOtherMitra(string $perawatNik, string $mitraId): void
    {
        $lockedByOther = Rekrutmen::with('mitraPengguna:id,nama')
            ->where('perawat_nik', $perawatNik)
            ->whereIn('status', [Rekrutmen::STATUS_LOCKED, Rekrutmen::STATUS_HIRED])
            ->where('mitra_pengguna_id', '!=', $mitraId)
            ->first();

        if ($lockedByOther) {
            $mitraName = $lockedByOther->mitraPengguna?->nama ?? 'mitra lain';

            throw ValidationException::withMessages([
                'perawat_nik' => "Kandidat sedang terkunci oleh {$mitraName}.",
            ]);
        }
    }

    private function ensureLowonganBelongsToMitra(?string $lowonganId, string $mitraId): void
    {
        if (!$lowonganId) {
            return;
        }

        $exists = Lowongan::where('id', $lowonganId)
            ->where('mitra_pengguna_id', $mitraId)
            ->exists();

        if (!$exists) {
            abort(403, 'Lowongan tidak sesuai dengan mitra ini.');
        }
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

    /**
     * @param array<int,string> $nursesNik
     * @return array<string,Rekrutmen>
     */
    private function resolveActiveRecruitmentByNik(array $nursesNik): array
    {
        if ($nursesNik === []) {
            return [];
        }

        return Rekrutmen::with(['mitraPengguna:id,nama', 'lowongan:id,judul'])
            ->whereIn('perawat_nik', $nursesNik)
            ->whereIn('status', [Rekrutmen::STATUS_LOCKED, Rekrutmen::STATUS_HIRED])
            ->orderByRaw(
                'CASE
                    WHEN status = ? THEN 0
                    WHEN status = ? THEN 1
                    ELSE 2
                END',
                [Rekrutmen::STATUS_HIRED, Rekrutmen::STATUS_LOCKED]
            )
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('perawat_nik')
            ->map(fn ($rows) => $rows->first())
            ->all();
    }
}
