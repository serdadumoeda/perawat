<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Riwayat Verifikasi Legalitas</h2>
            <p class="section-subtitle">Catatan review data STR oleh regulator.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex flex-col gap-1">
                    <div class="text-lg font-semibold text-slate-900">{{ $perawat->nama }}</div>
                    <div class="text-sm text-slate-600">NIK: {{ $perawat->nik }}</div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
                    <span>Status STR: {{ optional($perawat->dataLegalitas)->status_str ?? '-' }}</span>
                    <span class="flex items-center gap-2">
                        <span>Status Data STR:</span>
                        <x-legalitas-data-badge :status="optional($perawat->dataLegalitas)->data_status" />
                    </span>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                    <div>
                        <div class="panel-title">Audit Log Verifikasi Data STR</div>
                        <div class="panel-desc">Menampilkan keputusan terakhir yang tercatat.</div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('perawat.legalitas.audit.read') }}">
                            @csrf
                            <button class="btn-secondary" type="submit">Tandai Semua Dibaca</button>
                        </form>
                        <a class="btn-ghost" href="{{ route('perawat.dashboard') }}">Kembali ke Dashboard</a>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    <a class="btn-ghost {{ $preset === '7d' ? 'bg-slate-100' : '' }}"
                       href="{{ route('perawat.legalitas.audit', array_filter(['preset' => '7d', 'status' => $status !== 'all' ? $status : null])) }}">
                        7 Hari
                    </a>
                    <a class="btn-ghost {{ $preset === '30d' ? 'bg-slate-100' : '' }}"
                       href="{{ route('perawat.legalitas.audit', array_filter(['preset' => '30d', 'status' => $status !== 'all' ? $status : null])) }}">
                        30 Hari
                    </a>
                    <a class="btn-ghost {{ $preset === 'month' ? 'bg-slate-100' : '' }}"
                       href="{{ route('perawat.legalitas.audit', array_filter(['preset' => 'month', 'status' => $status !== 'all' ? $status : null])) }}">
                        Bulan Ini
                    </a>
                    <a class="btn-ghost" href="{{ route('perawat.legalitas.audit') }}">Semua Waktu</a>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="select-field mt-1">
                            <option value="all" @selected($status === 'all')>Semua</option>
                            @foreach ($statusOptions as $opt)
                                <option value="{{ $opt }}" @selected($status === $opt)>{{ ucfirst(str_replace('_', ' ', $opt)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="date_from" :value="__('Dari Tanggal')" />
                        <x-text-input id="date_from" class="mt-1 w-full" type="date" name="date_from" :value="$dateFrom" />
                    </div>
                    <div>
                        <x-input-label for="date_to" :value="__('Sampai Tanggal')" />
                        <x-text-input id="date_to" class="mt-1 w-full" type="date" name="date_to" :value="$dateTo" />
                    </div>
                    <div class="flex items-end gap-2">
                        <x-primary-button class="w-full">{{ __('Filter') }}</x-primary-button>
                        <a class="btn-secondary w-full text-center" href="{{ route('perawat.legalitas.audit') }}">Atur Ulang</a>
                    </div>
                </form>

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Metadata Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php($meta = $log->meta ?? [])
                                @php($sourceSystem = $meta['source_system'] ?? 'regulator_portal')
                                @php($sourceActor = $meta['source_actor'] ?? ($meta['actor_role'] ?? 'regulator'))
                                <tr>
                                    <td>{{ optional($log->created_at)->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="min-w-[320px]">
                                        <x-review-metadata
                                            :status="$meta['status'] ?? null"
                                            :rejected-reason="$meta['note'] ?? null"
                                            :verified-by="$meta['actor_name'] ?? $log->user?->name"
                                            :verified-at="$log->created_at"
                                            :source-system="$sourceSystem"
                                            :source-actor="$sourceActor"
                                            :source-organisasi="$meta['regulator_organisasi_nama'] ?? null"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="empty-state-row">Belum ada riwayat verifikasi data STR.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
