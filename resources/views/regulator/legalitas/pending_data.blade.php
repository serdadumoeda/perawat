<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Review Data Legalitas</h2>
            <p class="section-subtitle">Verifikasi data STR & izin kerja (bukan dokumen).</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="select-field mt-1">
                            <option value="submitted" @selected($status === 'submitted')>Diajukan</option>
                            <option value="pending_sync" @selected($status === 'pending_sync')>Menunggu Sinkronisasi</option>
                            <option value="verified" @selected($status === 'verified')>Terverifikasi</option>
                            <option value="expired" @selected($status === 'expired')>Kedaluwarsa</option>
                            <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                            <option value="all" @selected($status === 'all')>Semua</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="q" :value="__('Cari Perawat')" />
                        <x-text-input id="q" class="mt-1 w-full" type="text" name="q" placeholder="Nama / NIK" :value="$search" />
                    </div>
                    <div class="flex items-end">
                        <x-primary-button class="w-full">{{ __('Filter') }}</x-primary-button>
                    </div>
                </form>

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[1250px]">
                        <thead>
                            <tr>
                                <th>Perawat</th>
                                <th>Nomor STR</th>
                                <th>Status STR</th>
                                <th>Periode Berlaku</th>
                                <th>Status Data</th>
                                <th>Metadata Verifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $item)
                                @php($dataStatus = \App\Models\DataLegalitas::normalizeDataStatus($item->data_status))
                                <tr class="align-top">
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $item->perawat?->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->perawat?->nik ?? '-' }}</div>
                                    </td>
                                    <td>{{ $item->nomor_str ?? '-' }}</td>
                                    <td>{{ $item->status_str ?? '-' }}</td>
                                    <td>
                                        {{ optional($item->str_berlaku_mulai)->format('d M Y') ?? '-' }}
                                        -
                                        {{ optional($item->str_berlaku_sampai)->format('d M Y') ?? '-' }}
                                    </td>
                                    <td><x-legalitas-data-badge :status="$item->data_status" /></td>
                                    <td class="min-w-[300px]">
                                        <x-review-metadata
                                            :status="$item->data_status"
                                            :rejected-reason="$item->data_rejection_note"
                                            :verified-by="$item->dataReviewedBy?->name"
                                            :verified-at="$item->data_reviewed_at"
                                            :source-system="$item->source_system"
                                            :source-actor="$item->source_actor"
                                            :source-organisasi="$item->regulatorOrganisasi?->nama"
                                        />
                                    </td>
                                    <td>
                                        @if (in_array($dataStatus, [\App\Models\DataLegalitas::DATA_STATUS_SUBMITTED, \App\Models\DataLegalitas::DATA_STATUS_PENDING_SYNC], true))
                                            <form class="inline-block mr-2" method="POST" action="{{ route('regulator.legalitas.data.review', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="approve">
                                                <button class="table-action table-action-approve" type="submit">Setujui</button>
                                            </form>
                                            <form class="mt-2" method="POST" action="{{ route('regulator.legalitas.data.review', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="reject">
                                                <input type="text" name="note" placeholder="Catatan penolakan" class="input-field text-xs mb-2" />
                                                <button class="table-action table-action-reject" type="submit">Tolak</button>
                                            </form>
                                        @else
                                            <div class="text-xs text-slate-500 mb-2">Sudah direview.</div>
                                            <form method="POST" action="{{ route('regulator.legalitas.data.reopen', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="note" placeholder="Alasan buka ulang" class="input-field text-xs mb-2" />
                                                <button class="table-action table-action-reopen" type="submit">Buka Ulang</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="7">Belum ada data legalitas yang menunggu verifikasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $records->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
