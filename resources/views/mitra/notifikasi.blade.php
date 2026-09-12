<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Notifikasi Mitra</h2>
            <p class="section-subtitle">Pantau lamaran masuk, status rekrutmen, dan sinkronisasi pengalaman.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <div class="panel-title">Daftar Notifikasi</div>
                        <div class="panel-desc">Terbaru di bagian atas.</div>
                    </div>
                    <span class="chip chip-accent">{{ $notifications->total() }} notifikasi</span>
                </div>

                <div class="space-y-4">
                    @forelse ($notifications as $notification)
                        @php
                            $data = $notification->data ?? [];
                            $type = $data['tipe'] ?? 'umum';
                            $badgeClass = match ($type) {
                                'lamaran_masuk' => 'badge badge-success',
                                'experience_sync_failed' => 'badge badge-danger',
                                default => 'badge badge-warning',
                            };
                            $badgeLabel = match ($type) {
                                'lamaran_masuk' => 'Lamaran',
                                'experience_sync_failed' => 'Sinkronisasi Gagal',
                                default => 'Info',
                            };
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="font-semibold text-slate-900">
                                    @if ($type === 'lamaran_masuk')
                                        Lamaran Masuk
                                    @elseif ($type === 'experience_sync_failed')
                                        Sinkronisasi Pengalaman Gagal
                                    @else
                                        Notifikasi
                                    @endif
                                </div>
                                <span class="{{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </div>

                            <div class="mt-2 text-sm text-slate-600 space-y-1">
                                @if ($type === 'lamaran_masuk')
                                    <div>Perawat: {{ $data['perawat_nama'] ?? '-' }}</div>
                                    <div>Lowongan: {{ $data['judul_lowongan'] ?? '-' }}</div>
                                    <div>Status: {{ $data['status'] ?? '-' }}</div>
                                @elseif ($type === 'experience_sync_failed')
                                    <div>{{ $data['message'] ?? '' }}</div>
                                    <div>Mitra: {{ $data['mitra_nama'] ?? '-' }}</div>
                                    @if (!empty($data['latest_error']))
                                        <div class="text-rose-600">Galat: {{ $data['latest_error'] }}</div>
                                    @endif
                                @else
                                    <div>{{ json_encode($data, JSON_UNESCAPED_UNICODE) }}</div>
                                @endif
                            </div>

                            <div class="mt-3 text-xs text-slate-500">{{ $notification->created_at->format('d M Y H:i') }}</div>
                        </div>
                    @empty
                        <div class="empty-state">Tidak ada notifikasi.</div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
