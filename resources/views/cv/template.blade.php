@php
    $isPublic = (bool) ($isPublic ?? false);
    $documentTitle = $documentTitle ?? 'CV Terverifikasi';
    $legalitas = $perawat->dataLegalitas;

    $humanize = function (?string $value): string {
        if (!$value) {
            return '-';
        }

        return ucwords(str_replace('_', ' ', $value));
    };

    $displayFromMap = function (?string $value, array $map) use ($humanize): string {
        if (!$value) {
            return '-';
        }

        return $map[$value] ?? $humanize($value);
    };

    $formatDate = function ($value, string $format = 'd M Y'): string {
        if (!$value) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->translatedFormat($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $formatDateTime = fn ($value): string => $formatDate($value, 'd M Y H:i');

    $displayNik = '-';
    if (!empty($perawat->nik)) {
        $displayNik = $isPublic ? '****'.substr((string) $perawat->nik, -4) : (string) $perawat->nik;
    }

    $statusKesiapan = $displayFromMap($perawat->status_kesiapan ?? null, [
        \App\Models\Perawat::KESIAPAN_DRAFT => 'Draft / Incomplete',
        \App\Models\Perawat::KESIAPAN_READY => 'Ready to Hire',
    ]);

    $statusKetenagakerjaan = $displayFromMap($perawat->status_ketenagakerjaan ?? null, [
        \App\Models\Perawat::KETENAGAKERJAAN_FRESH => 'Fresh Graduate',
        \App\Models\Perawat::KETENAGAKERJAAN_BEKERJA => 'Bekerja',
        \App\Models\Perawat::KETENAGAKERJAAN_BERPENGALAMAN => 'Berpengalaman',
    ]);

    $statusKetersediaan = $displayFromMap($perawat->status_ketersediaan ?? null, [
        \App\Models\Perawat::KETERSEDIAAN_AVAILABLE => 'Available',
        \App\Models\Perawat::KETERSEDIAAN_OPEN => 'Open to Offer',
        \App\Models\Perawat::KETERSEDIAAN_UNAVAILABLE => 'Not Available',
    ]);
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --brand-700: #0f766e;
            --brand-500: #14b8a6;
            --brand-50: #ecfeff;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-100: #f1f5f9;
            --border: #e2e8f0;
            --danger-100: #ffe4e6;
            --danger-700: #be123c;
            --warning-100: #fef3c7;
            --warning-700: #92400e;
            --info-100: #e0f2fe;
            --info-700: #0369a1;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--slate-900);
            background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            line-height: 1.45;
        }

        .sheet {
            max-width: 1040px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 20px 44px rgba(2, 6, 23, 0.10);
            overflow: hidden;
        }

        .hero {
            padding: 28px 30px;
            background: linear-gradient(110deg, #f0fdfa 0%, #ffffff 65%);
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: space-between;
            align-items: flex-start;
        }

        .hero-title {
            margin: 0;
            font-size: 31px;
            line-height: 1.15;
            letter-spacing: -0.01em;
        }

        .eyebrow {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--brand-700);
        }

        .hero-meta {
            margin-top: 8px;
            color: var(--slate-600);
            font-size: 14px;
        }

        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-width: 350px;
            justify-content: flex-end;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--border);
            background: var(--slate-100);
            color: var(--slate-700);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-success {
            border-color: #99f6e4;
            background: #f0fdfa;
            color: #0f766e;
        }

        .badge-warning {
            border-color: #fcd34d;
            background: var(--warning-100);
            color: var(--warning-700);
        }

        .badge-danger {
            border-color: #fecdd3;
            background: var(--danger-100);
            color: var(--danger-700);
        }

        .badge-info {
            border-color: #bae6fd;
            background: var(--info-100);
            color: var(--info-700);
        }

        .badge-muted {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #64748b;
        }

        .content {
            padding: 22px 24px 28px;
        }

        .section {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            padding: 16px;
            margin-top: 14px;
        }

        .section:first-child {
            margin-top: 0;
        }

        .section-title {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.01em;
            color: var(--slate-900);
        }

        .summary-grid,
        .info-grid,
        .record-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .summary-card,
        .info-item,
        .record-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            padding: 12px;
        }

        .summary-label,
        .info-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--slate-500);
        }

        .summary-value,
        .info-value {
            margin-top: 4px;
            font-size: 15px;
            color: var(--slate-800, #1e293b);
            font-weight: 600;
        }

        .record-card {
            background: #fcfdff;
        }

        .record-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .record-sub {
            margin-top: 3px;
            color: var(--slate-600);
            font-size: 13px;
        }

        .record-tags {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .review-grid {
            margin-top: 10px;
            border-top: 1px dashed var(--border);
            padding-top: 8px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 6px 10px;
        }

        .review-row {
            font-size: 12px;
            color: var(--slate-700);
        }

        .review-row b {
            color: var(--slate-500);
            font-weight: 700;
            margin-right: 4px;
        }

        .empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            color: var(--slate-500);
            font-size: 14px;
            padding: 14px;
            text-align: center;
        }

        .footer {
            margin-top: 18px;
            font-size: 12px;
            color: var(--slate-500);
            text-align: right;
        }

        @media (max-width: 768px) {
            body { padding: 12px; }
            .hero { padding: 20px; }
            .hero-title { font-size: 24px; }
            .badge-list { max-width: 100%; justify-content: flex-start; }
            .content { padding: 16px; }
        }

        @media print {
            body { background: #fff; padding: 0; }
            .sheet {
                max-width: none;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }
            .section {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <header class="hero">
            <div>
                <p class="eyebrow">{{ $documentTitle }}</p>
                <h1 class="hero-title">{{ $perawat->nama ?? '-' }}</h1>
                <div class="hero-meta">NIK: {{ $displayNik }}</div>
                <div class="hero-meta">Dibuat: {{ now()->translatedFormat('d M Y H:i') }}</div>
            </div>

            <div class="badge-list">
                <span class="badge">{{ $statusKesiapan }}</span>
                <span class="badge">{{ $statusKetenagakerjaan }}</span>
                <span class="badge">{{ $statusKetersediaan }}</span>
                @if ($perawat->isVerifiedExperience())
                    <span class="badge badge-success">Verified Experience Nurse</span>
                @endif
                <x-legalitas-data-badge :status="optional($legalitas)->data_status" />
            </div>
        </header>

        <div class="content">
            <section class="section">
                <h2 class="section-title">Ringkasan Profil</h2>
                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="summary-label">Status Kesiapan</div>
                        <div class="summary-value">{{ $statusKesiapan }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Status Ketenagakerjaan</div>
                        <div class="summary-value">{{ $statusKetenagakerjaan }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Ketersediaan</div>
                        <div class="summary-value">{{ $statusKetersediaan }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Lokasi</div>
                        <div class="summary-value">{{ trim(($perawat->kota ?? '').', '.($perawat->provinsi ?? ''), ', ') ?: '-' }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Email</div>
                        <div class="summary-value">{{ $perawat->email ?? '-' }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">No. HP</div>
                        <div class="summary-value">{{ $perawat->no_hp ?? '-' }}</div>
                    </div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Legalitas</h2>
                @if ($legalitas)
                    <div class="info-grid">
                        @unless($isPublic)
                            <div class="info-item">
                                <div class="info-label">Nomor STR</div>
                                <div class="info-value">{{ $legalitas->nomor_str ?? '-' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Nomor SIPP</div>
                                <div class="info-value">{{ $legalitas->nomor_sipp ?? '-' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Izin Luar Negeri</div>
                                <div class="info-value">{{ $legalitas->nomor_izin_luar_negeri ?? '-' }}</div>
                            </div>
                        @endunless
                        <div class="info-item">
                            <div class="info-label">Status STR</div>
                            <div class="info-value">{{ $displayFromMap($legalitas->status_str ?? null, [
                                \App\Models\DataLegalitas::STATUS_STR_AKTIF => 'Aktif',
                                \App\Models\DataLegalitas::STATUS_STR_HOLD => 'Ditahan',
                                \App\Models\DataLegalitas::STATUS_STR_EXPIRED => 'Kedaluwarsa',
                            ]) }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status Data STR</div>
                            <div class="info-value">{{ $displayFromMap(\App\Models\DataLegalitas::normalizeDataStatus($legalitas->data_status), [
                                \App\Models\DataLegalitas::DATA_STATUS_DRAFT => 'Draft',
                                \App\Models\DataLegalitas::DATA_STATUS_SUBMITTED => 'Diajukan',
                                \App\Models\DataLegalitas::DATA_STATUS_PENDING_SYNC => 'Menunggu Sinkronisasi',
                                \App\Models\DataLegalitas::DATA_STATUS_VERIFIED => 'Terverifikasi',
                                \App\Models\DataLegalitas::DATA_STATUS_EXPIRED => 'Kedaluwarsa',
                                \App\Models\DataLegalitas::DATA_STATUS_REJECTED => 'Ditolak',
                            ]) }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Berlaku Sampai</div>
                            <div class="info-value">{{ $formatDate($legalitas->str_berlaku_sampai ?? null) }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status Izin LN</div>
                            <div class="info-value">{{ $displayFromMap($legalitas->status_izin_luar_negeri ?? null, [
                                'aktif' => 'Aktif',
                                'hold' => 'Ditahan',
                                'expired' => 'Kedaluwarsa',
                            ]) }}</div>
                        </div>
                    </div>

                    <div class="review-grid">
                        <div class="review-row"><b>Diverifikasi Oleh:</b>{{ optional($legalitas->dataReviewedBy)->name ?? '-' }}</div>
                        <div class="review-row"><b>Waktu Verifikasi:</b>{{ $formatDateTime($legalitas->data_reviewed_at ?? null) }}</div>
                        <div class="review-row"><b>Sistem Sumber:</b>{{ $legalitas->source_system ?? '-' }}</div>
                        <div class="review-row"><b>Aktor Sumber:</b>{{ $legalitas->source_actor ?? '-' }}</div>
                        <div class="review-row"><b>Organisasi Sumber:</b>{{ optional($legalitas->regulatorOrganisasi)->nama ?? '-' }}</div>
                        <div class="review-row"><b>Alasan Penolakan:</b>{{ $legalitas->data_rejection_note ?? '-' }}</div>
                    </div>
                @else
                    <div class="empty-state">Belum ada data legalitas.</div>
                @endif
            </section>

            <section class="section">
                <h2 class="section-title">Riwayat Pendidikan</h2>
                <div class="record-grid">
                    @forelse ($perawat->riwayatPendidikan as $riwayat)
                        <article class="record-card">
                            <h3 class="record-title">{{ $riwayat->institusiPendidikan?->nama ?? 'Institusi tidak diketahui' }}</h3>
                            <div class="record-sub">{{ $riwayat->jenjang ?? '-' }} - {{ $riwayat->program_studi ?? '-' }}</div>
                            <div class="record-sub">Tanggal Ijazah: {{ $formatDate($riwayat->tanggal_ijazah ?? null) }} | IPK: {{ $riwayat->ipk ?? '-' }}</div>
                            <div class="record-tags">
                                <span class="badge badge-info">{{ $displayFromMap($riwayat->status_verifikasi ?? null, [
                                    \App\Models\RiwayatPendidikan::VERIF_PENDING => 'Menunggu',
                                    \App\Models\RiwayatPendidikan::VERIF_VERIFIED => 'Terverifikasi',
                                    \App\Models\RiwayatPendidikan::VERIF_REJECTED => 'Ditolak',
                                ]) }}</span>
                            </div>
                            <div class="review-grid">
                                <div class="review-row"><b>Diverifikasi Oleh:</b>{{ optional($riwayat->verifiedBy)->name ?? '-' }}</div>
                                <div class="review-row"><b>Waktu Verifikasi:</b>{{ $formatDateTime($riwayat->verified_at ?? null) }}</div>
                                <div class="review-row"><b>Sistem Sumber:</b>{{ $riwayat->source_system ?? '-' }}</div>
                                <div class="review-row"><b>Aktor Sumber:</b>{{ $riwayat->source_actor ?? '-' }}</div>
                                <div class="review-row"><b>Alasan Penolakan:</b>{{ $riwayat->rejected_reason ?? '-' }}</div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">Belum ada riwayat pendidikan.</div>
                    @endforelse
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Sertifikasi Kompetensi</h2>
                <div class="record-grid">
                    @forelse ($perawat->sertifikasi as $sertifikasi)
                        <article class="record-card">
                            <h3 class="record-title">{{ $sertifikasi->nama_sertifikat ?? '-' }}</h3>
                            <div class="record-sub">{{ $sertifikasi->lembagaPelatihan?->nama ?? 'Lembaga tidak diketahui' }}</div>
                            <div class="record-sub">Terbit: {{ $formatDate($sertifikasi->tanggal_terbit ?? null) }} | Kadaluarsa: {{ $formatDate($sertifikasi->tanggal_kadaluarsa ?? null) }}</div>
                            <div class="record-sub">Jam: {{ $sertifikasi->jumlah_jam ?? '-' }} | Nilai: {{ $sertifikasi->nilai_kompetensi ?? '-' }}</div>
                            <div class="record-tags">
                                <span class="badge badge-info">{{ $displayFromMap($sertifikasi->status_verifikasi ?? null, [
                                    \App\Models\SertifikasiKompetensi::VERIF_PENDING => 'Menunggu',
                                    \App\Models\SertifikasiKompetensi::VERIF_VERIFIED => 'Terverifikasi',
                                    \App\Models\SertifikasiKompetensi::VERIF_REJECTED => 'Ditolak',
                                    \App\Models\SertifikasiKompetensi::VERIF_VALID => 'Valid',
                                ]) }}</span>
                            </div>
                            <div class="review-grid">
                                <div class="review-row"><b>Diverifikasi Oleh:</b>{{ optional($sertifikasi->verifiedBy)->name ?? '-' }}</div>
                                <div class="review-row"><b>Waktu Verifikasi:</b>{{ $formatDateTime($sertifikasi->verified_at ?? null) }}</div>
                                <div class="review-row"><b>Sistem Sumber:</b>{{ $sertifikasi->source_system ?? '-' }}</div>
                                <div class="review-row"><b>Aktor Sumber:</b>{{ $sertifikasi->source_actor ?? '-' }}</div>
                                <div class="review-row"><b>Alasan Penolakan:</b>{{ $sertifikasi->rejected_reason ?? '-' }}</div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">Belum ada data sertifikasi.</div>
                    @endforelse
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Riwayat Pengalaman Kerja</h2>
                <div class="record-grid">
                    @forelse ($perawat->riwayatPekerjaan as $riwayat)
                        <article class="record-card">
                            <h3 class="record-title">{{ $riwayat->mitraPengguna?->nama ?? 'Instansi belum tercatat' }}</h3>
                            <div class="record-sub">{{ $riwayat->posisi ?? '-' }} ({{ $displayFromMap($riwayat->status_pekerjaan ?? null, [
                                \App\Models\RiwayatPekerjaan::STATUS_AKTIF => 'Aktif',
                                \App\Models\RiwayatPekerjaan::STATUS_SELESAI => 'Selesai',
                                \App\Models\RiwayatPekerjaan::STATUS_RESIGN => 'Resign',
                            ]) }})</div>
                            <div class="record-sub">Periode: {{ $formatDate($riwayat->tanggal_mulai ?? null) }} - {{ $formatDate($riwayat->tanggal_selesai ?? null) }}</div>
                            <div class="record-sub">Rating: {{ $riwayat->kinerja_rating ?? '-' }} / 5 | Skor: {{ $riwayat->kinerja_score ?? '-' }}</div>
                            <div class="record-tags">
                                <span class="badge badge-info">{{ $displayFromMap($riwayat->verifikasi_status ?? null, [
                                    \App\Models\RiwayatPekerjaan::VERIF_PENDING => 'Menunggu',
                                    \App\Models\RiwayatPekerjaan::VERIF_VERIFIED => 'Terverifikasi',
                                    \App\Models\RiwayatPekerjaan::VERIF_REJECTED => 'Ditolak',
                                ]) }}</span>
                            </div>
                            <div class="review-grid">
                                <div class="review-row"><b>Diverifikasi Oleh:</b>{{ optional($riwayat->verifiedBy)->name ?? '-' }}</div>
                                <div class="review-row"><b>Waktu Verifikasi:</b>{{ $formatDateTime($riwayat->verified_at ?? null) }}</div>
                                <div class="review-row"><b>Sistem Sumber:</b>{{ $riwayat->source_system ?? '-' }}</div>
                                <div class="review-row"><b>Aktor Sumber:</b>{{ $riwayat->source_actor ?? '-' }}</div>
                                <div class="review-row"><b>Alasan Penolakan:</b>{{ $riwayat->rejected_reason ?? '-' }}</div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">Belum ada riwayat pengalaman kerja.</div>
                    @endforelse
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Timeline Karier</h2>
                <div class="record-grid">
                    @forelse ($perawat->careerEvents as $event)
                        <article class="record-card">
                            <h3 class="record-title">{{ $event->title ?? '-' }}</h3>
                            <div class="record-sub">Waktu Kejadian: {{ $formatDateTime($event->happened_at ?? null) }}</div>
                            <div class="record-tags">
                                <span class="badge badge-muted">{{ $displayFromMap($event->category ?? null, []) }}</span>
                                <span class="badge badge-info">{{ $displayFromMap($event->status ?? null, []) }}</span>
                            </div>
                            <div class="review-grid">
                                <div class="review-row"><b>Sistem Sumber:</b>{{ $event->source_system ?? '-' }}</div>
                                <div class="review-row"><b>Aktor Sumber:</b>{{ $event->source_actor ?? '-' }}</div>
                                <div class="review-row"><b>Aktor:</b>{{ $event->actor_name ?? '-' }}</div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">Belum ada event karier.</div>
                    @endforelse
                </div>
            </section>

            <div class="footer">
                Dokumen ini dihasilkan otomatis oleh Platform Keperawatan.
            </div>
        </div>
    </div>
</body>
</html>
