<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Lowongan Tersedia</h2>
            <p class="section-subtitle">Temukan lowongan sesuai profil Anda.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            @if ($perawat->status_kesiapan !== \App\Models\Perawat::KESIAPAN_READY)
                <div class="mb-4 alert alert-warning">
                    Profil Anda belum ready to hire. Lengkapi pendidikan, sertifikasi, dan legalitas untuk dapat melamar.
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <form method="GET" class="flex flex-wrap items-center gap-3 mb-6">
                    <x-text-input class="flex-1" type="text" name="q" :value="request('q')" placeholder="Cari judul lowongan" />
                    <select name="tipe_penempatan" class="select-field w-full sm:w-auto">
                        <option value="">Semua penempatan</option>
                        <option value="domestik" @selected(request('tipe_penempatan') === 'domestik')>Domestik</option>
                        <option value="luar_negeri" @selected(request('tipe_penempatan') === 'luar_negeri')>Luar Negeri</option>
                    </select>
                    <x-primary-button>{{ __('Cari') }}</x-primary-button>
                </form>

                <div class="table-wrap overflow-x-auto">
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
                                        <a class="btn-ghost" href="{{ route('perawat.lowongan.show', $item) }}">Lihat Detail</a>
                                        @if (in_array($item->id, $appliedIds, true))
                                            <span class="badge badge-success ml-2">Sudah melamar</span>
                                        @endif
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
</x-app-layout>
