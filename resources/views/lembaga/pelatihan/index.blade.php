<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Pelatihan</h2>
                <p class="section-subtitle">Kelola katalog pelatihan dan peserta.</p>
            </div>
            <a class="btn-primary" href="{{ route('lembaga.pelatihan.create') }}">Tambah Pelatihan</a>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-6">
                @if (session('status'))
                    <div class="mb-4 alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[720px]">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Lembaga</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Peserta</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pelatihan as $item)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $item->nama }}</td>
                                    <td>{{ $item->lembagaPelatihan?->nama }}</td>
                                    <td>
                                        @if ($item->tanggal_mulai)
                                            {{ $item->tanggal_mulai->format('d M Y') }}
                                            @if ($item->tanggal_selesai)
                                                - {{ $item->tanggal_selesai->format('d M Y') }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->status === \App\Models\Pelatihan::STATUS_DIBUKA ? 'badge-success' : 'badge-warning' }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td>{{ $item->peserta_count }}</td>
                                    <td>
                                        <a class="btn-ghost" href="{{ route('lembaga.pelatihan.show', $item) }}">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="empty-state-row" colspan="6">Belum ada pelatihan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $pelatihan->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
