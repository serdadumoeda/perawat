<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>CV Terverifikasi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; margin: 24px; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .subtitle { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; }
        .meta { color: #4b5563; margin: 2px 0; }
        .section { margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px; background: #f9fafb; }
        .section-title { font-size: 13px; font-weight: 700; margin-bottom: 6px; }
        .row { margin: 2px 0; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; border: 1px solid #e5e7eb; font-size: 10px; font-weight: 700; color: #374151; background: #ffffff; }
        .badge-success { border-color: #cfe9dd; color: #147a4c; background: #ecf8f1; }
        .badge-warning { border-color: #fde68a; color: #92400e; background: #fffbeb; }
        .badge-danger { border-color: #fecaca; color: #be123c; background: #fff1f2; }
        .badge-info { border-color: #bae6fd; color: #0369a1; background: #f0f9ff; }
        .badge-muted { border-color: #e2e8f0; color: #64748b; background: #f8fafc; }
        ul { margin: 6px 0 0; padding-left: 16px; }
        li { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        .meta-table { width: 100%; margin-top: 4px; border-collapse: collapse; }
        .meta-table td { padding: 1px 0; font-size: 10px; color: #374151; }
        .meta-label { width: 105px; color: #6b7280; font-weight: 700; }
        .divider { height: 1px; background: #e5e7eb; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="subtitle">CV Terverifikasi</div>
    <div class="title">{{ $perawat->nama }}</div>
    <div class="meta">NIK: {{ $perawat->nik }}</div>
    <div class="meta">Status Kesiapan: {{ $perawat->kesiapan_label }}</div>
    <div class="meta">Status Ketenagakerjaan: {{ $perawat->ketenagakerjaan_label }}</div>
    <div class="meta">Ketersediaan: {{ $perawat->ketersediaan_label }}</div>
    <div class="divider"></div>
    <div>
        <span class="badge">{{ $perawat->kesiapan_label }}</span>
        <span class="badge">{{ $perawat->ketenagakerjaan_label }}</span>
        <span class="badge">{{ $perawat->ketersediaan_label }}</span>
        @if ($perawat->isVerifiedExperience())
            <span class="badge badge-success">Pengalaman Terverifikasi Perawat</span>
        @endif
        <x-legalitas-data-badge :status="optional($perawat->dataLegalitas)->data_status" />
    </div>

    <div class="section">
        <div class="section-title">Legalitas</div>
        <table>
            <tr><td class="row">STR</td><td class="row">: {{ optional($perawat->dataLegalitas)->nomor_str ?? '-' }}</td></tr>
            <tr><td class="row">SIPP</td><td class="row">: {{ optional($perawat->dataLegalitas)->nomor_sipp ?? '-' }}</td></tr>
            <tr><td class="row">Status STR</td><td class="row">: {{ optional($perawat->dataLegalitas)->status_str ?? '-' }}</td></tr>
            <tr><td class="row">Status Data STR</td><td class="row">: {{ \App\Models\DataLegalitas::normalizeDataStatus(optional($perawat->dataLegalitas)->data_status) }}</td></tr>
            <tr><td class="row">Izin Luar Negeri</td><td class="row">: {{ optional($perawat->dataLegalitas)->nomor_izin_luar_negeri ?? '-' }}</td></tr>
            <tr><td class="row">Status Izin LN</td><td class="row">: {{ optional($perawat->dataLegalitas)->status_izin_luar_negeri ?? '-' }}</td></tr>
            <tr><td class="row">Diverifikasi Oleh</td><td class="row">: {{ optional(optional($perawat->dataLegalitas)->dataReviewedBy)->name ?? '-' }}</td></tr>
            <tr><td class="row">Waktu Verifikasi</td><td class="row">: {{ optional(optional($perawat->dataLegalitas)->data_reviewed_at)->format('d M Y H:i') ?? '-' }}</td></tr>
            <tr><td class="row">Sistem Sumber</td><td class="row">: {{ optional($perawat->dataLegalitas)->source_system ?? '-' }}</td></tr>
            <tr><td class="row">Aktor Sumber</td><td class="row">: {{ optional($perawat->dataLegalitas)->source_actor ?? '-' }}</td></tr>
            <tr><td class="row">Alasan Penolakan</td><td class="row">: {{ optional($perawat->dataLegalitas)->data_rejection_note ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Pendidikan</div>
        <ul>
            @foreach ($perawat->riwayatPendidikan as $rp)
                <li>{{ $rp->institusiPendidikan?->nama }} - {{ $rp->jenjang }} - {{ $rp->program_studi }}</li>
            @endforeach
        </ul>
    </div>

    <div class="section">
        <div class="section-title">Sertifikasi</div>
        <ul>
            @foreach ($perawat->sertifikasi as $sk)
                <li>
                    {{ $sk->nama_sertifikat }} ({{ $sk->lembagaPelatihan?->nama }}) - {{ $sk->status_verifikasi ?? '-' }}
                    @if ($sk->jumlah_jam)
                        | Jam: {{ $sk->jumlah_jam }}
                    @endif
                    @if ($sk->nilai_kompetensi)
                        | Nilai: {{ $sk->nilai_kompetensi }}
                    @endif
                    <table class="meta-table">
                        <tr><td class="meta-label">Diverifikasi Oleh</td><td>: {{ $sk->verifiedBy?->name ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Waktu Verifikasi</td><td>: {{ optional($sk->verified_at)->format('d M Y H:i') ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Sistem Sumber</td><td>: {{ $sk->source_system ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Aktor Sumber</td><td>: {{ $sk->source_actor ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Alasan Penolakan</td><td>: {{ $sk->rejected_reason ?? '-' }}</td></tr>
                    </table>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="section">
        <div class="section-title">Pengalaman Kerja</div>
        <ul>
            @foreach ($perawat->riwayatPekerjaan as $rk)
                <li>
                    {{ $rk->mitraPengguna?->nama }} - {{ $rk->posisi }} ({{ $rk->status_pekerjaan }}) - {{ $rk->verifikasi_status ?? '-' }}
                    <table class="meta-table">
                        <tr><td class="meta-label">Diverifikasi Oleh</td><td>: {{ $rk->verifiedBy?->name ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Waktu Verifikasi</td><td>: {{ optional($rk->verified_at)->format('d M Y H:i') ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Sistem Sumber</td><td>: {{ $rk->source_system ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Aktor Sumber</td><td>: {{ $rk->source_actor ?? '-' }}</td></tr>
                        <tr><td class="meta-label">Alasan Penolakan</td><td>: {{ $rk->rejected_reason ?? '-' }}</td></tr>
                    </table>
                </li>
            @endforeach
        </ul>
    </div>
</body>
</html>
