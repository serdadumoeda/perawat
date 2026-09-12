<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Lamaran Saya</h2>
            <p class="section-subtitle">Pantau status lamaran dan tindak lanjut rekrutmen.</p>
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
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Lowongan</th>
                                <th>Mitra</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lamaran as $item)
                                @php($statusClass = match($item->status) {
                                    \App\Models\Lamaran::STATUS_DIAJUKAN => 'badge badge-warning',
                                    \App\Models\Lamaran::STATUS_DIPROSES => 'badge badge-warning',
                                    \App\Models\Lamaran::STATUS_DITERIMA => 'badge badge-success',
                                    \App\Models\Lamaran::STATUS_DITOLAK => 'badge badge-danger',
                                    default => 'badge badge-warning',
                                })
                                <tr>
                                    <td>{{ $item->lowongan?->judul ?? '-' }}</td>
                                    <td>{{ $item->lowongan?->mitraPengguna?->nama ?? '-' }}</td>
                                    <td><span class="{{ $statusClass }}">{{ $item->status }}</span></td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <a class="btn-ghost" href="{{ route('perawat.lowongan.show', $item->lowongan_id) }}">Lihat Detail</a>
                                            @if (in_array($item->status, [\App\Models\Lamaran::STATUS_DIAJUKAN, \App\Models\Lamaran::STATUS_DIPROSES], true))
                                                <form method="POST" action="{{ route('perawat.lamaran.cancel', $item) }}">
                                                    @csrf
                                                    <button type="submit" class="table-action table-action-reject">Batalkan</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="4">Belum ada lamaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $lamaran->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
