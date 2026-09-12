<x-public-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Detail Lowongan</h2>
            <p class="section-subtitle">Informasi lowongan terbuka untuk publik.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="card-surface p-6">
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

            <div class="card-surface p-6">
                <div class="text-sm text-slate-600">
                    Untuk melamar lowongan, silakan login sebagai Perawat.
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
