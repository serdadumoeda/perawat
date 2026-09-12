<x-public-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Lowongan Terbuka</h2>
            <p class="section-subtitle">Lowongan publik dapat diakses semua orang.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="card-surface p-6">
                <form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
                    <x-text-input class="flex-1" type="text" name="q" :value="request('q')" placeholder="Cari judul lowongan" />
                    <select name="tipe_penempatan" class="select-field w-full sm:w-auto">
                        <option value="">Semua penempatan</option>
                        <option value="domestik" @selected(request('tipe_penempatan') === 'domestik')>Domestik</option>
                        <option value="luar_negeri" @selected(request('tipe_penempatan') === 'luar_negeri')>Luar Negeri</option>
                    </select>
                    <x-primary-button>{{ __('Cari') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="data-table min-w-[640px]">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Mitra</th>
                                <th>Penempatan</th>
                                <th>Publikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowongan as $item)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $item->judul }}</td>
                                    <td>{{ $item->mitraPengguna?->nama ?? '-' }}</td>
                                    <td>
                                        {{ $item->tipe_penempatan === \App\Models\Lowongan::PENEMPATAN_LUAR_NEGERI ? 'Luar Negeri' : 'Domestik' }}
                                        @if ($item->negara_penempatan)
                                            <div class="text-xs text-slate-500">{{ $item->negara_penempatan }}</div>
                                        @endif
                                    </td>
                                    <td>{{ optional($item->published_at)->format('d M Y') }}</td>
                                    <td>
                                        <a class="btn-ghost" href="{{ route('public.lowongan.show', $item) }}">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada lowongan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $lowongan->links() }}</div>
            </div>
        </div>
    </div>
</x-public-layout>
