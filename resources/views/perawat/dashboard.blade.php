<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
            <div>
                <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Perawat</div>
                <h2 style="font-family:'Playfair Display',serif; font-size:1.625rem; font-weight:700; color:#1a1a1a; margin:0;">Profil Perawat</h2>
                <p style="font-size:0.875rem; color:#9a9a9a; margin:0.25rem 0 0;">Kelola identitas, legalitas, dan pengalaman kerja.</p>
            </div>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            @if ($errors->has('pdf'))
                <div class="mb-4 text-sm text-rose-600">{{ $errors->first('pdf') }}</div>
            @endif
            <div class="module-card card-surface p-6 sm:p-7 mb-6 shadow-lg">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div style="width:3rem; height:3rem; border-radius:1rem; background:linear-gradient(135deg,#0f5050,#1aa8a8); display:flex; align-items:center; justify-content:center; font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#ffffff; flex-shrink:0;">
                                {{ strtoupper(substr($perawat->nama ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:700; color:#1a1a1a;">{{ $perawat->nama }}</div>
                                <div style="font-size:0.75rem; color:#9a9a9a; margin-top:0.125rem;">NIK: {{ $perawat->nik }}</div>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="badge badge-info">{{ $perawat->kesiapan_label }}</span>
                            <span class="badge badge-warning">{{ $perawat->ketenagakerjaan_label }}</span>
                            <span class="badge badge-muted">{{ $perawat->ketersediaan_label }}</span>
                            @if ($perawat->isVerifiedExperience())
                                <span class="badge badge-success flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Pengalaman Terverifikasi
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        <a href="{{ route('perawat.profil.edit') }}" class="btn-secondary text-xs">Edit Profil</a>
                        <a href="{{ route('perawat.cv') }}" class="btn-secondary text-xs">CV Terverifikasi</a>
                        <a href="{{ route('perawat.cv.pdf') }}" class="btn-primary text-xs">Unduh PDF</a>
                        <a href="{{ route('perawat.notifikasi') }}" class="btn-ghost text-xs">
                            Notifikasi Legalitas
                            @if (!empty($legalitasDataUnread) && $legalitasDataUnread > 0)
                                <span class="badge badge-warning ml-1 px-2">{{ $legalitasDataUnread }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="module-card card-surface p-5 sm:p-6" style="border-top:3px solid #0f5050;">
                    <div class="flex items-center justify-between">
                        <h3 class="panel-title text-base flex items-center gap-2">
                            <svg style="width:1.25rem; height:1.25rem; color:#0f5050;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Legalitas &amp; Perizinan
                        </h3>
                        <x-legalitas-data-badge :status="optional($perawat->dataLegalitas)->data_status" />
                    </div>
                    @php($strSampai = optional($perawat->dataLegalitas)->str_berlaku_sampai)
                    @php($strMulai = optional($perawat->dataLegalitas)->str_berlaku_mulai)
                    @php($strDaysLeft = $strSampai ? now()->startOfDay()->diffInDays($strSampai, false) : null)
                    <div class="mt-4 space-y-2.5 text-sm text-slate-600">
                        <div class="flex justify-between py-1 border-b border-slate-100"><span class="font-medium text-slate-500">Nomor STR:</span> <span class="font-semibold text-slate-800">{{ optional($perawat->dataLegalitas)->nomor_str ?? '-' }}</span></div>
                        <div class="flex justify-between py-1 border-b border-slate-100"><span class="font-medium text-slate-500">Nomor SIPP:</span> <span class="font-semibold text-slate-800">{{ optional($perawat->dataLegalitas)->nomor_sipp ?? '-' }}</span></div>
                        <div class="flex justify-between py-1 border-b border-slate-100"><span class="font-medium text-slate-500">Status STR:</span> <span class="font-semibold text-slate-800">{{ optional($perawat->dataLegalitas)->status_str ?? '-' }}</span></div>
                        <div class="flex justify-between py-1 border-b border-slate-100"><span class="font-medium text-slate-500">Masa Berlaku:</span> <span class="font-semibold text-slate-800">{{ $strMulai?->format('d M Y') ?? '-' }} – {{ $strSampai?->format('d M Y') ?? '-' }}</span></div>
                        @if (!is_null($strDaysLeft))
                            <div class="pt-1">
                                <span class="badge {{ $strDaysLeft < 0 ? 'badge-danger' : ($strDaysLeft <= 30 ? 'badge-warning' : 'badge-success') }}">
                                    {{ $strDaysLeft < 0 ? 'Kedaluwarsa '.abs($strDaysLeft).' hari' : 'Sisa '.$strDaysLeft.' hari' }}
                                </span>
                            </div>
                        @endif
                        <div class="flex justify-between py-1 border-b border-slate-100"><span class="font-medium text-slate-500">Izin Luar Negeri:</span> <span class="font-semibold text-slate-800">{{ optional($perawat->dataLegalitas)->nomor_izin_luar_negeri ?? '-' }}</span></div>
                        <div class="pt-2">
                            <a class="btn-ghost text-xs" style="padding:0; color:#0f5050; font-size:0.75rem;" href="{{ route('perawat.legalitas.audit') }}">→ Lihat Riwayat Audit Verifikasi</a>
                        </div>
                    </div>
                </div>

                <div class="module-card card-surface p-4 sm:p-6">
                    <h3 class="panel-title">Pendidikan</h3>
                    <div class="mt-3 space-y-3">
                        @foreach ($perawat->riwayatPendidikan as $rp)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <div class="font-semibold text-slate-900">{{ $rp->institusiPendidikan?->nama }}</div>
                                <div class="text-sm text-slate-600">{{ $rp->jenjang }} - {{ $rp->program_studi }}</div>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    @if ($rp->transkrip_path)
                                        <a class="btn-ghost" href="{{ Storage::url($rp->transkrip_path) }}" target="_blank">Lihat Transkrip</a>
                                    @endif
                                    @if ($rp->ijazah_path)
                                        <a class="btn-ghost" href="{{ Storage::url($rp->ijazah_path) }}" target="_blank">Lihat Ijazah</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="module-card card-surface p-4 sm:p-6">
                    <h3 class="panel-title">Sertifikasi</h3>
                    <div class="table-wrap mt-3 overflow-x-auto">
                        <table class="data-table min-w-[820px]">
                            <thead>
                                <tr>
                                    <th>Sertifikat</th>
                                    <th>Lembaga</th>
                                    <th>Dokumen</th>
                                    <th>Metadata Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perawat->sertifikasi as $sk)
                                    <tr class="align-top">
                                        <td>
                                            <div class="font-semibold text-slate-900">{{ $sk->nama_sertifikat }}</div>
                                            @if ($sk->jumlah_jam || $sk->nilai_kompetensi)
                                                <div class="text-xs text-slate-500 mt-1">
                                                    @if ($sk->jumlah_jam)
                                                        Jam: {{ $sk->jumlah_jam }}
                                                    @endif
                                                    @if ($sk->nilai_kompetensi)
                                                        <span class="mx-1">|</span>
                                                        Nilai: {{ $sk->nilai_kompetensi }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $sk->lembagaPelatihan?->nama ?? '-' }}</td>
                                        <td>
                                            @if ($sk->file_sertifikat)
                                                <a class="btn-ghost" href="{{ Storage::url($sk->file_sertifikat) }}" target="_blank">Lihat Sertifikat</a>
                                            @else
                                                <span class="text-xs text-slate-500">Belum ada file</span>
                                            @endif
                                        </td>
                                        <td class="min-w-[320px]">
                                            <x-review-metadata
                                                :status="$sk->status_verifikasi"
                                                :rejected-reason="$sk->rejected_reason"
                                                :verified-by="$sk->verifiedBy?->name"
                                                :verified-at="$sk->verified_at"
                                                :source-system="$sk->source_system"
                                                :source-actor="$sk->source_actor"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state-row">Belum ada sertifikasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="module-card card-surface p-4 sm:p-6">
                    <h3 class="panel-title">Pengalaman Kerja</h3>
                    <div class="table-wrap mt-3 overflow-x-auto">
                        <table class="data-table min-w-[820px]">
                            <thead>
                                <tr>
                                    <th>Mitra</th>
                                    <th>Posisi</th>
                                    <th>Status Kerja</th>
                                    <th>Metadata Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perawat->riwayatPekerjaan as $rk)
                                    <tr class="align-top">
                                        <td>{{ $rk->mitraPengguna?->nama ?? '-' }}</td>
                                        <td>{{ $rk->posisi }}</td>
                                        <td>{{ $rk->status_pekerjaan }}</td>
                                        <td class="min-w-[320px]">
                                            <x-review-metadata
                                                :status="$rk->verifikasi_status"
                                                :rejected-reason="$rk->rejected_reason"
                                                :verified-by="$rk->verifiedBy?->name"
                                                :verified-at="$rk->verified_at"
                                                :source-system="$rk->source_system"
                                                :source-actor="$rk->source_actor"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state-row">Belum ada riwayat pekerjaan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6 mt-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="panel-title">Timeline Karier (Living CV)</h3>
                    <span class="text-xs text-slate-500">Otomatis dari proses terverifikasi</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($perawat->careerEvents as $event)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge badge-info">{{ strtoupper($event->category) }}</span>
                                @if ($event->status)
                                    <span class="chip">{{ $event->status }}</span>
                                @endif
                                <span class="text-xs text-slate-500">{{ optional($event->happened_at)->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $event->title }}</div>
                            @if ($event->description)
                                <div class="text-sm text-slate-600 mt-1">{{ $event->description }}</div>
                            @endif
                            <div class="text-xs text-slate-500 mt-1">
                                Sumber: {{ $event->source_actor ?? '-' }} / {{ $event->source_system ?? '-' }}
                                @if ($event->actor_name)
                                    · Oleh {{ $event->actor_name }}{{ $event->actor_role ? ' ('.$event->actor_role.')' : '' }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada event karier.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
