<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Dashboard Lembaga</h2>
            <p class="section-subtitle">Ringkasan pelatihan, peserta, dan sertifikasi.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 module-container">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
                <div class="stat-card">
                    <div class="text-slate-500">Total Pelatihan</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $stats['pelatihan_total'] }}</div>
                    <div class="text-xs text-slate-500">Dibuka: {{ $stats['pelatihan_dibuka'] }} • Ditutup: {{ $stats['pelatihan_ditutup'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-slate-500">Peserta Pelatihan</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $stats['peserta_total'] }}</div>
                    <div class="text-xs text-slate-500">Lulus: {{ $stats['peserta_lulus'] }} • Tidak Lulus: {{ $stats['peserta_tidak_lulus'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-slate-500">Sertifikasi</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $stats['sertifikasi_total'] }}</div>
                    <div class="text-xs text-slate-500">Verified: {{ $stats['sertifikasi_verified'] }} • Pending: {{ $stats['sertifikasi_pending'] }} • Rejected: {{ $stats['sertifikasi_rejected'] }}</div>
                </div>
            </div>

            <div class="module-card card-surface p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="panel-title">Pelatihan Terpopuler</div>
                        <div class="panel-desc">Berdasarkan jumlah peserta.</div>
                    </div>
                    <a class="btn-secondary" href="{{ route('lembaga.pelatihan.index') }}">Lihat Semua</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pelatihan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Peserta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topPelatihan as $item)
                            <tr>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->tanggal_mulai?->format('d M Y') ?? '-' }}</td>
                                <td>{{ $item->status }}</td>
                                <td>{{ $item->peserta_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="empty-state-row" colspan="4">Belum ada pelatihan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
