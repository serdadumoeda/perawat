<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Audit Dokumen Legalitas</h2>
            <p class="section-subtitle">Riwayat review dokumen STR dan izin luar negeri.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <div class="text-sm text-slate-600">Perawat:</div>
                <div class="text-lg font-semibold text-slate-900">{{ $legalitas->perawat?->nama ?? '-' }}</div>
                <div class="text-sm text-slate-600">NIK: {{ $legalitas->perawat?->nik ?? '-' }}</div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
                    <div>
                        <x-input-label for="doc_type" :value="__('Filter Dokumen')" />
                        <select id="doc_type" name="doc_type" class="select-field mt-1">
                            <option value="all" @selected($docType === 'all')>Semua</option>
                            <option value="str" @selected($docType === 'str')>STR</option>
                            <option value="izin_luar_negeri" @selected($docType === 'izin_luar_negeri')>Izin Luar Negeri</option>
                        </select>
                    </div>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Aksi</th>
                                <th>Dokumen</th>
                                <th>Metadata Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php($meta = $log->meta ?? [])
                                @php($docLabel = ($meta['document_type'] ?? '') === 'izin_luar_negeri' ? 'Izin Luar Negeri' : 'STR')
                                @php($sourceSystem = $meta['source_system'] ?? 'regulator_portal')
                                @php($sourceActor = $meta['source_actor'] ?? ($meta['actor_role'] ?? 'regulator'))
                                <tr>
                                    <td>{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ $log->action }}</td>
                                    <td>{{ $docLabel }}</td>
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
                                    <td class="empty-state-row" colspan="4">Belum ada audit.</td>
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
