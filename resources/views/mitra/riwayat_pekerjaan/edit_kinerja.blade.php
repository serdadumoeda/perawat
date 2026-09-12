<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Edit Evaluasi Kinerja</h2>
                <p class="section-subtitle">Koreksi penilaian setelah kontrak selesai atau resign.</p>
            </div>
            <a href="{{ route('mitra.riwayat_kinerja.index') }}" class="btn-secondary">Kembali</a>
        </div>
    </x-slot>

    <div class="module-shell module-shell-compact">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6 space-y-4">
                <div class="rounded-2xl border border-white/70 bg-white/80 p-4">
                    <div class="text-sm font-semibold text-slate-900">{{ $riwayat->perawat?->nama ?? '-' }}</div>
                    <div class="text-xs text-slate-500">{{ $riwayat->mitraPengguna?->nama ?? '-' }} - {{ $riwayat->posisi ?? '-' }}</div>
                    <div class="mt-2 text-xs text-slate-500">Status: {{ ucfirst($riwayat->status_pekerjaan) }}</div>
                </div>

                <form method="POST" action="{{ route('mitra.riwayat_kinerja.update', $riwayat) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="penilaian_kinerja" :value="__('Penilaian Kinerja')" />
                        <x-text-input id="penilaian_kinerja" name="penilaian_kinerja" type="text" class="mt-1 w-full" :value="old('penilaian_kinerja', $riwayat->penilaian_kinerja)" placeholder="Contoh: Baik" />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <x-input-label for="kinerja_rating" :value="__('Rating Bintang (1-5)')" />
                            <div class="mt-2 star-rating" role="radiogroup" aria-label="Rating bintang">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="rating-edit-{{ $i }}" name="kinerja_rating" value="{{ $i }}" @checked(old('kinerja_rating', $riwayat->kinerja_rating) == $i)>
                                    <label for="rating-edit-{{ $i }}" title="{{ $i }} bintang">&#9733;</label>
                                @endfor
                            </div>
                        </div>
                        <div>
                            <x-input-label for="kinerja_score" :value="__('Skor (0-100)')" />
                            <x-text-input id="kinerja_score" name="kinerja_score" type="number" step="0.01" min="0" max="100" class="mt-1 w-full" :value="old('kinerja_score', $riwayat->kinerja_score)" placeholder="0 - 100" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="keterangan" :value="__('Catatan')" />
                        <x-text-input id="keterangan" name="keterangan" type="text" class="mt-1 w-full" :value="old('keterangan', $riwayat->keterangan)" placeholder="Catatan tambahan" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Perbarui Evaluasi</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
