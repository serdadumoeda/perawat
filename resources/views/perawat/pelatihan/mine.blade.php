<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Pelatihan Saya</h2>
            <p class="section-subtitle">Riwayat pelatihan yang Anda ikuti.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 alert alert-success">{{ session('status') }}</div>
                @endif
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[640px]">
                        <thead>
                            <tr>
                                <th>Pelatihan</th>
                                <th>Lembaga</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayat as $item)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $item->pelatihan?->nama }}</td>
                                    <td>{{ $item->pelatihan?->lembagaPelatihan?->nama }}</td>
                                    <td>
                                        @if ($item->pelatihan?->tanggal_mulai)
                                            {{ $item->pelatihan->tanggal_mulai->format('d M Y') }}
                                            @if ($item->pelatihan?->tanggal_selesai)
                                                - {{ $item->pelatihan->tanggal_selesai->format('d M Y') }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->status }}</td>
                                    <td>{{ $item->nilai_kompetensi ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada pelatihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $riwayat->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
