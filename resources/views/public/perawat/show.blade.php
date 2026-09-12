<x-public-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Profil Perawat (Publik)</h2>
            <p class="section-subtitle">Ringkasan data dan pengalaman terverifikasi.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-surface p-6">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="text-lg font-semibold text-slate-900">{{ $perawat->nama }}</div>
                    @if ($perawat->isVerifiedExperience())
                        <span class="badge badge-success">Verified Experience Nurse</span>
                    @endif
                </div>
                <div class="text-sm text-slate-600 mt-1">NIK: ****{{ substr($perawat->nik, -4) }}</div>
                <div class="mt-3 text-sm text-slate-700">
                    <div>Status Kesiapan: {{ $perawat->kesiapan_label }}</div>
                    <div>Status Ketenagakerjaan: {{ $perawat->ketenagakerjaan_label }}</div>
                    <div>Ketersediaan: {{ $perawat->ketersediaan_label }}</div>
                </div>
            </div>

            <div class="card-surface p-6">
                <div class="panel-title mb-3">Legalitas</div>
                <div class="text-sm text-slate-700 space-y-1">
                    <div>Status STR: {{ optional($perawat->dataLegalitas)->status_str ?? '-' }}</div>
                    <div class="flex items-center gap-2">
                        <span>Status Data STR:</span>
                        <x-legalitas-data-badge :status="optional($perawat->dataLegalitas)->data_status" />
                    </div>
                    <div>STR Berlaku Sampai: {{ optional($perawat->dataLegalitas)->str_berlaku_sampai?->format('d M Y') ?? '-' }}</div>
                    <div>Izin Luar Negeri: {{ optional($perawat->dataLegalitas)->status_izin_luar_negeri ?? '-' }}</div>
                </div>
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Metadata Review Data STR</div>
                    <x-review-metadata
                        :status="optional($perawat->dataLegalitas)->data_status"
                        :rejected-reason="optional($perawat->dataLegalitas)->data_rejection_note"
                        :verified-by="optional(optional($perawat->dataLegalitas)->dataReviewedBy)->name"
                        :verified-at="optional($perawat->dataLegalitas)->data_reviewed_at"
                        :source-system="optional($perawat->dataLegalitas)->source_system"
                        :source-actor="optional($perawat->dataLegalitas)->source_actor"
                        :source-organisasi="optional(optional($perawat->dataLegalitas)->regulatorOrganisasi)->nama"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card-surface p-6">
                    <div class="panel-title mb-3">Pendidikan</div>
                    <div class="space-y-2 text-sm text-slate-700">
                        @forelse ($perawat->riwayatPendidikan as $rp)
                            <div>
                                <div>{{ $rp->institusiPendidikan?->nama }}</div>
                                <div class="text-xs text-slate-500">{{ $rp->jenjang }} - {{ $rp->program_studi }}</div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">Belum ada data pendidikan.</div>
                        @endforelse
                    </div>
                </div>
                <div class="card-surface p-6">
                    <div class="panel-title mb-3">Sertifikasi</div>
                    <div class="space-y-3 text-sm text-slate-700">
                        @forelse ($perawat->sertifikasi as $sk)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                <div>{{ $sk->nama_sertifikat }}</div>
                                <div class="text-xs text-slate-500">{{ $sk->lembagaPelatihan?->nama }} — {{ $sk->status_verifikasi ?? '-' }}</div>
                                <div class="mt-2">
                                    <x-review-metadata
                                        :status="$sk->status_verifikasi"
                                        :rejected-reason="$sk->rejected_reason"
                                        :verified-by="$sk->verifiedBy?->name"
                                        :verified-at="$sk->verified_at"
                                        :source-system="$sk->source_system"
                                        :source-actor="$sk->source_actor"
                                    />
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">Belum ada sertifikasi.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card-surface p-6">
                <div class="panel-title mb-3">Pengalaman Kerja (Terverifikasi)</div>
                <div class="space-y-3 text-sm text-slate-700">
                    @forelse ($perawat->riwayatPekerjaan as $rk)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                            <div>{{ $rk->mitraPengguna?->nama }}</div>
                            <div class="text-xs text-slate-500">{{ $rk->posisi }} ({{ $rk->status_pekerjaan }})</div>
                            <div class="mt-2">
                                <x-review-metadata
                                    :status="$rk->verifikasi_status"
                                    :rejected-reason="$rk->rejected_reason"
                                    :verified-by="$rk->verifiedBy?->name"
                                    :verified-at="$rk->verified_at"
                                    :source-system="$rk->source_system"
                                    :source-actor="$rk->source_actor"
                                />
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">Belum ada pengalaman terverifikasi.</div>
                    @endforelse
                </div>
            </div>

            <div class="card-surface p-6">
                <div class="panel-title mb-3">Timeline Karier</div>
                <div class="space-y-3 text-sm text-slate-700">
                    @forelse ($perawat->careerEvents as $event)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span class="badge badge-info">{{ strtoupper($event->category) }}</span>
                                @if ($event->status)
                                    <span class="chip">{{ $event->status }}</span>
                                @endif
                                <span>{{ optional($event->happened_at)->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $event->title }}</div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $event->source_actor ?? '-' }} / {{ $event->source_system ?? '-' }}
                                @if ($event->actor_name)
                                    · {{ $event->actor_name }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">Belum ada event karier.</div>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a class="btn-primary" href="{{ route('public.perawat.cv', $perawat->public_profile_token) }}">Unduh CV (HTML)</a>
                <a class="btn-secondary" href="{{ route('public.perawat.cv_pdf', $perawat->public_profile_token) }}">Unduh CV (PDF)</a>
            </div>
        </div>
    </div>
</x-public-layout>
