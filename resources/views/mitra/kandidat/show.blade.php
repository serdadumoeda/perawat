<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Profil Kandidat</h2>
            <p class="section-subtitle">Detail lengkap kandidat untuk keputusan rekrutmen.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-2xl font-semibold text-slate-900">{{ $perawat->nama }}</div>
                        <div class="text-sm text-slate-600">NIK: {{ $perawat->nik }}</div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="chip">{{ $perawat->kesiapan_label }}</span>
                            <span class="chip">{{ $perawat->ketenagakerjaan_label }}</span>
                            <span class="chip">{{ $perawat->ketersediaan_label }}</span>
                            @if ($perawat->isVerifiedExperience())
                                <span class="badge badge-success">Pengalaman Terverifikasi</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a class="btn-secondary" href="{{ route('mitra.kandidat.cv_pdf', $perawat) }}">Unduh CV Terverifikasi</a>
                        <a class="btn-ghost" href="{{ route('mitra.kandidat.cv_pdf', $perawat) }}" target="_blank">Pratinjau PDF</a>
                    </div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <h3 class="panel-title">Status Rekrutmen Kandidat</h3>
                @if ($activeRecruitment)
                    @php($ownedByViewer = in_array($activeRecruitment->mitra_pengguna_id, $viewerMitraIds, true))
                    <div class="mt-3 space-y-2 text-sm text-slate-700">
                        <div>
                            @if ($activeRecruitment->status === \App\Models\Rekrutmen::STATUS_HIRED)
                                <span class="badge {{ $ownedByViewer ? 'badge-success' : 'badge-danger' }}">
                                    {{ $ownedByViewer ? 'Sudah direkrut oleh mitra Anda' : 'Sudah direkrut oleh mitra lain' }}
                                </span>
                            @else
                                <span class="badge {{ $ownedByViewer ? 'badge-warning' : 'badge-info' }}">
                                    {{ $ownedByViewer ? 'Sedang dikunci oleh mitra Anda' : 'Sedang dikunci oleh mitra lain' }}
                                </span>
                            @endif
                        </div>
                        <div>Mitra: <span class="font-semibold">{{ $activeRecruitment->mitraPengguna?->nama ?? '-' }}</span></div>
                        <div>Lowongan: {{ $activeRecruitment->lowongan?->judul ?? '-' }}</div>
                        <div>Update terakhir: {{ optional($activeRecruitment->updated_at)->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                @else
                    <div class="mt-3 empty-state">Belum ada proses kunci/rekrut aktif pada kandidat ini.</div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="module-card card-surface p-4 sm:p-6">
                    <h3 class="panel-title">Legalitas</h3>
                    @php($strSampai = optional($perawat->dataLegalitas)->str_berlaku_sampai)
                    @php($strMulai = optional($perawat->dataLegalitas)->str_berlaku_mulai)
                    @php($strDaysLeft = $strSampai ? now()->startOfDay()->diffInDays($strSampai, false) : null)
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <div>STR: {{ optional($perawat->dataLegalitas)->nomor_str ?? '-' }}</div>
                        <div>SIPP: {{ optional($perawat->dataLegalitas)->nomor_sipp ?? '-' }}</div>
                        <div>Status STR: {{ optional($perawat->dataLegalitas)->status_str ?? '-' }}</div>
                        <div>STR Berlaku: {{ $strMulai?->format('d M Y') ?? '-' }} - {{ $strSampai?->format('d M Y') ?? '-' }}</div>
                        @if (!is_null($strDaysLeft))
                            <div>
                                <span class="badge {{ $strDaysLeft < 0 ? 'badge-danger' : ($strDaysLeft <= 30 ? 'badge-warning' : 'badge-success') }}">
                                    {{ $strDaysLeft < 0 ? 'Kedaluwarsa '.abs($strDaysLeft).' hari' : 'Sisa '.$strDaysLeft.' hari' }}
                                </span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <span>Status Data STR:</span>
                            <x-legalitas-data-badge :status="optional($perawat->dataLegalitas)->data_status" />
                        </div>
                        <div>Izin Luar Negeri: {{ optional($perawat->dataLegalitas)->nomor_izin_luar_negeri ?? '-' }}</div>
                        <div>Status Izin LN: {{ optional($perawat->dataLegalitas)->status_izin_luar_negeri ?? '-' }}</div>
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
                    <div class="mt-3 space-y-3">
                        @foreach ($perawat->sertifikasi as $sk)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <div class="font-semibold text-slate-900">{{ $sk->nama_sertifikat }}</div>
                                <div class="text-sm text-slate-600">
                                    {{ $sk->lembagaPelatihan?->nama }} — Status: {{ $sk->status_verifikasi ?? '-' }}
                                </div>
                                @if ($sk->jumlah_jam || $sk->nilai_kompetensi)
                                    <div class="text-xs text-slate-500">
                                        @if ($sk->jumlah_jam)
                                            Jam: {{ $sk->jumlah_jam }}
                                        @endif
                                        @if ($sk->nilai_kompetensi)
                                            <span class="mx-1">|</span>
                                            Nilai: {{ $sk->nilai_kompetensi }}
                                        @endif
                                    </div>
                                @endif
                                @if ($sk->file_sertifikat)
                                    <a class="btn-ghost mt-2" href="{{ Storage::url($sk->file_sertifikat) }}" target="_blank">Lihat Sertifikat</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="module-card card-surface p-4 sm:p-6">
                    <h3 class="panel-title">Pengalaman Kerja</h3>
                    <div class="mt-3 space-y-3">
                        @foreach ($perawat->riwayatPekerjaan as $rk)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <div class="font-semibold text-slate-900">{{ $rk->mitraPengguna?->nama }}</div>
                                <div class="text-sm text-slate-600">
                                    {{ $rk->posisi }} ({{ $rk->status_pekerjaan }}) - {{ $rk->verifikasi_status ?? '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @php($mitraId = auth()->user()->mitraPengguna->first()?->id)
            @php($isLockedByOtherMitra = $activeRecruitment && in_array($activeRecruitment->status, [\App\Models\Rekrutmen::STATUS_LOCKED, \App\Models\Rekrutmen::STATUS_HIRED], true) && !in_array($activeRecruitment->mitra_pengguna_id, $viewerMitraIds, true))
            @if ($mitraId)
                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="panel-title">Aksi Rekrutmen</div>
                            <div class="panel-desc">Kunci kandidat atau lanjutkan proses rekrutmen.</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('mitra.kandidat.lock') }}">
                                @csrf
                                <input type="hidden" name="perawat_nik" value="{{ $perawat->nik }}">
                                <input type="hidden" name="mitra_pengguna_id" value="{{ $mitraId }}">
                                <button class="btn-secondary disabled:opacity-60 disabled:cursor-not-allowed" type="submit" @disabled($isLockedByOtherMitra)>Kunci Kandidat</button>
                            </form>
                            <form method="POST" action="{{ route('mitra.kandidat.hire') }}">
                                @csrf
                                <input type="hidden" name="perawat_nik" value="{{ $perawat->nik }}">
                                <input type="hidden" name="mitra_pengguna_id" value="{{ $mitraId }}">
                                <button class="btn-primary disabled:opacity-60 disabled:cursor-not-allowed" type="submit" @disabled($isLockedByOtherMitra)>Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('mitra.kandidat.release') }}">
                                @csrf
                                <input type="hidden" name="perawat_nik" value="{{ $perawat->nik }}">
                                <input type="hidden" name="mitra_pengguna_id" value="{{ $mitraId }}">
                                <button class="btn-ghost disabled:opacity-60 disabled:cursor-not-allowed" type="submit" @disabled($isLockedByOtherMitra)>Lepas</button>
                            </form>
                        </div>
                    </div>
                    @if ($isLockedByOtherMitra)
                        <div class="mt-4 alert alert-warning">
                            Kandidat ini sedang {{ $activeRecruitment->status }} oleh {{ $activeRecruitment->mitraPengguna?->nama ?? 'mitra lain' }}.
                            Anda tidak dapat mengunci atau menyetujui sebelum status dilepas.
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning">Akun ini belum terhubung ke mitra pengguna.</div>
            @endif
        </div>
    </div>
</x-app-layout>
