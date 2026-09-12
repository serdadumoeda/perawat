<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Detail Lowongan</h2>
            <p class="section-subtitle">Informasi lengkap lowongan dan status lamaran Anda.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
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
                <div class="text-2xl font-semibold text-slate-900">{{ $lowongan->judul }}</div>
                <div class="text-sm text-slate-600">Mitra: {{ $lowongan->mitraPengguna?->nama ?? '-' }}</div>
                <div class="text-sm text-slate-600">
                    Penempatan:
                    {{ $lowongan->tipe_penempatan === \App\Models\Lowongan::PENEMPATAN_LUAR_NEGERI ? 'Luar Negeri' : 'Domestik' }}
                    @if ($lowongan->negara_penempatan)
                        • {{ $lowongan->negara_penempatan }}
                    @endif
                    @php($lokasi = trim(($lowongan->kota_penempatan ?? '').' '.($lowongan->provinsi_penempatan ?? '')))
                    @if ($lokasi !== '')
                        • {{ $lokasi }}
                    @endif
                </div>
                <div class="mt-4 whitespace-pre-line text-sm text-slate-700">{{ $lowongan->deskripsi }}</div>
                <div class="mt-4">
                    <div class="panel-title">Kriteria</div>
                    @php($kriteria = $lowongan->kriteria ?? [])
                    <div class="text-sm text-slate-600">Status STR: {{ $kriteria['status_str'] ?? '-' }}</div>
                    <div class="text-sm text-slate-600">Sertifikat: {{ $kriteria['sertifikat'] ?? '-' }}</div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-2">Lamaran Anda</div>

                @if ($lamaran)
                    <div class="text-sm text-slate-700">Status: {{ $lamaran->status }}</div>
                    @if ($lamaran->catatan)
                        <div class="mt-2 text-sm text-slate-600">Catatan: {{ $lamaran->catatan }}</div>
                    @endif
                    @if (in_array($lamaran->status, [\App\Models\Lamaran::STATUS_DIAJUKAN, \App\Models\Lamaran::STATUS_DIPROSES], true))
                        <form class="mt-4" method="POST" action="{{ route('perawat.lamaran.cancel', $lamaran) }}">
                            @csrf
                            <x-primary-button>{{ __('Batalkan Lamaran') }}</x-primary-button>
                        </form>
                    @endif
                @else
                    @if ($perawat->status_kesiapan !== \App\Models\Perawat::KESIAPAN_READY)
                        <div class="text-sm text-amber-700">Lengkapi profil agar bisa melamar.</div>
                    @else
                        <form method="POST" action="{{ route('perawat.lowongan.apply') }}" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="lowongan_id" value="{{ $lowongan->id }}">
                            <div>
                                <x-input-label for="catatan" :value="__('Catatan (opsional)')" />
                                <textarea id="catatan" name="catatan" rows="4" class="input-field mt-1">{{ old('catatan') }}</textarea>
                            </div>
                            <div class="flex items-center justify-end">
                                <x-primary-button>{{ __('Kirim Lamaran') }}</x-primary-button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
