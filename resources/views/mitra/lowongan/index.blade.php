<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Lowongan</h2>
                <p class="section-subtitle">Kelola lowongan dan proses rekrutmen.</p>
            </div>
            <a href="{{ route('mitra.lowongan.create') }}" class="btn-primary">Buat Lowongan</a>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[640px]">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Penempatan</th>
                                <th>Status</th>
                                <th>Publikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowongan as $item)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $item->judul }}</td>
                                    <td>
                                        <div class="text-sm">
                                            {{ $item->tipe_penempatan === \App\Models\Lowongan::PENEMPATAN_LUAR_NEGERI ? 'Luar Negeri' : 'Domestik' }}
                                        </div>
                                        @php($lokasi = trim(($item->kota_penempatan ?? '').' '.($item->provinsi_penempatan ?? '')))
                                        <div class="text-xs text-slate-500">
                                            {{ $item->negara_penempatan ?? '-' }}{{ $lokasi !== '' ? ' • '.$lokasi : '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->status === 'dibuka' ? 'badge-success' : 'badge-warning' }}">{{ $item->status }}</span>
                                    </td>
                                    <td>{{ optional($item->published_at)->format('d M Y') }}</td>
                                    <td>
                                        <a class="btn-ghost" href="{{ route('mitra.lowongan.show', $item) }}">Lihat Detail</a>
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
