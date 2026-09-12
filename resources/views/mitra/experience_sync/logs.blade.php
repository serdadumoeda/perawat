<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Log Sinkronisasi Pengalaman</h2>
            <p class="section-subtitle">Pantau hasil sinkronisasi, filter, dan ekspor CSV.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($mitraList->isEmpty())
                <div class="alert alert-warning">Akun ini belum terhubung ke mitra pengguna. Hubungi admin.</div>
            @else
                <div class="module-card card-surface p-4 sm:p-6 space-y-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="mitra_id" :value="__('Mitra')" />
                            <select id="mitra_id" name="mitra_id" class="select-field mt-2">
                                @foreach ($mitraList as $mitra)
                                    <option value="{{ $mitra->id }}" @selected($selectedId === $mitra->id)>{{ $mitra->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="select-field mt-2">
                                <option value="">Semua</option>
                                <option value="success" @selected($status === 'success')>Sukses</option>
                                <option value="failed" @selected($status === 'failed')>Gagal</option>
                                <option value="pending" @selected($status === 'pending')>Menunggu</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="q" :value="__('Cari')" />
                            <x-text-input id="q" class="mt-2" type="text" name="q" :value="$search" placeholder="Nama perawat / NIK / sumber" />
                        </div>
                        <div>
                            <x-input-label for="start_date" :value="__('Mulai Tanggal')" />
                            <x-text-input id="start_date" class="mt-2" type="date" name="start_date" :value="$startDate" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('Sampai Tanggal')" />
                            <x-text-input id="end_date" class="mt-2" type="date" name="end_date" :value="$endDate" />
                        </div>
                        <div class="flex items-end gap-3">
                            <x-primary-button>{{ __('Filter') }}</x-primary-button>
                            <a class="btn-ghost" href="{{ route('mitra.experience_sync.index', ['mitra_id' => $selectedId]) }}">Pengaturan Sinkronisasi</a>
                        </div>
                    </form>

                    @php
                        $now = now();
                        $today = $now->toDateString();
                        $range7Start = $now->copy()->subDays(6)->toDateString();
                        $range30Start = $now->copy()->subDays(29)->toDateString();
                        $monthStart = $now->copy()->startOfMonth()->toDateString();
                    @endphp
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="text-slate-600">Preset:</span>
                        <a class="chip" href="{{ route('mitra.experience_sync.logs', ['mitra_id' => $selectedId, 'status' => $status, 'q' => $search, 'start_date' => $range7Start, 'end_date' => $today]) }}">7 hari</a>
                        <a class="chip" href="{{ route('mitra.experience_sync.logs', ['mitra_id' => $selectedId, 'status' => $status, 'q' => $search, 'start_date' => $range30Start, 'end_date' => $today]) }}">30 hari</a>
                        <a class="chip" href="{{ route('mitra.experience_sync.logs', ['mitra_id' => $selectedId, 'status' => $status, 'q' => $search, 'start_date' => $monthStart, 'end_date' => $today]) }}">Bulan ini</a>
                        <a class="chip" href="{{ route('mitra.experience_sync.logs', ['mitra_id' => $selectedId, 'status' => $status, 'q' => $search]) }}">Atur Ulang</a>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('mitra.experience_sync.sync_now') }}">
                            @csrf
                            <input type="hidden" name="mitra_id" value="{{ $selectedId }}">
                            <x-primary-button type="submit">Sinkronkan Sekarang</x-primary-button>
                        </form>
                        <a class="btn-secondary" href="{{ route('mitra.experience_sync.export', ['mitra_id' => $selectedId, 'status' => $status, 'q' => $search, 'start_date' => $startDate, 'end_date' => $endDate]) }}">Ekspor CSV</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div class="stat-card">
                            <div class="text-slate-500">Total</div>
                            <div class="text-2xl font-semibold text-slate-900">{{ $summary['total'] }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="text-slate-500">Sukses</div>
                            <div class="text-2xl font-semibold text-emerald-700">{{ $summary['success'] }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="text-slate-500">Gagal</div>
                            <div class="text-2xl font-semibold text-rose-700">{{ $summary['failed'] }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="text-slate-500">Menunggu</div>
                            <div class="text-2xl font-semibold text-amber-700">{{ $summary['pending'] }}</div>
                        </div>
                    </div>

                    <div class="table-wrap overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Mitra</th>
                                    <th>Perawat</th>
                                    <th>Status</th>
                                    <th>Sumber</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    @php($statusClass = match($log->status) {
                                        'success' => 'badge badge-success',
                                        'failed' => 'badge badge-danger',
                                        default => 'badge badge-warning',
                                    })
                                    <tr class="align-top">
                                        <td>
                                            <div>{{ $log->created_at?->format('d M Y H:i') }}</div>
                                            <div class="text-xs text-slate-500">Sinkron terakhir: {{ $log->last_synced_at?->format('d M Y H:i') ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $log->mitraPengguna?->nama ?? '-' }}</div>
                                            <div class="text-xs text-slate-500">Mitra ID: {{ $log->mitra_pengguna_id ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $log->perawat?->nama ?? 'Sistem' }}</div>
                                            <div class="text-xs text-slate-500">{{ $log->perawat?->nik ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="{{ $statusClass }}">{{ strtoupper($log->status) }}</span>
                                            @if ($log->error_message)
                                                <div class="text-xs text-rose-600 mt-1">{{ \Illuminate\Support\Str::limit($log->error_message, 120) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-xs text-slate-600">{{ $log->source_system }}</div>
                                            <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($log->external_id, 24) }}</div>
                                        </td>
                                        <td class="text-xs text-slate-600">
                                            {{ \Illuminate\Support\Str::limit(json_encode($log->payload, JSON_UNESCAPED_SLASHES), 160) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="empty-state-row" colspan="6">Belum ada log.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $logs->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
