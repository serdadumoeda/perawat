<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Sinkronisasi STR</h2>
            <p class="section-subtitle">Kelola permintaan sinkronisasi STR dari perawat.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            @if (session('status'))
                <div class="mb-4 alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
                    <form method="GET" class="flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="select-field mt-1">
                                <option value="pending" @selected($status === 'pending')>Menunggu</option>
                                <option value="synced" @selected($status === 'synced')>Tersinkron</option>
                                <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                            </select>
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    </form>

                    <form method="POST" action="{{ route('regulator.str_sync.sync_all') }}">
                        @csrf
                        <x-primary-button>Sinkronkan Otomatis (Mock)</x-primary-button>
                    </form>
                </div>

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>Perawat</th>
                                <th>Nomor STR</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $item)
                                <tr class="align-top">
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $item->perawat?->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->perawat?->nik ?? '-' }}</div>
                                    </td>
                                    <td>{{ $item->nomor_str }}</td>
                                    <td>{{ $item->status }}</td>
                                    <td>
                                        @if ($item->status === \App\Models\StrSyncRequest::STATUS_PENDING)
                                            <details class="mb-3">
                                                <summary class="cursor-pointer text-sm font-semibold text-slate-700">Proses Sinkronisasi</summary>
                                                <form class="mt-3 space-y-4" method="POST" action="{{ route('regulator.str_sync.sync', $item) }}">
                                                    @csrf
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <x-input-label for="status_str_{{ $item->id }}" :value="__('Status STR')" />
                                                            <select id="status_str_{{ $item->id }}" name="status_str" class="select-field mt-1" required>
                                                                <option value="aktif">Aktif</option>
                                                                <option value="hold">Tahan</option>
                                                                <option value="expired">Kedaluwarsa</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <x-input-label for="nomor_sipp_{{ $item->id }}" :value="__('Nomor SIPP')" />
                                                            <x-text-input id="nomor_sipp_{{ $item->id }}" class="mt-1 w-full" type="text" name="nomor_sipp" />
                                                        </div>
                                                        <div>
                                                            <x-input-label for="str_mulai_{{ $item->id }}" :value="__('STR Berlaku Mulai')" />
                                                            <x-text-input id="str_mulai_{{ $item->id }}" class="mt-1 w-full" type="date" name="str_berlaku_mulai" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label for="str_sampai_{{ $item->id }}" :value="__('STR Berlaku Sampai')" />
                                                            <x-text-input id="str_sampai_{{ $item->id }}" class="mt-1 w-full" type="date" name="str_berlaku_sampai" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label for="izin_ln_{{ $item->id }}" :value="__('Nomor Izin LN')" />
                                                            <x-text-input id="izin_ln_{{ $item->id }}" class="mt-1 w-full" type="text" name="nomor_izin_luar_negeri" />
                                                        </div>
                                                        <div>
                                                            <x-input-label for="status_izin_ln_{{ $item->id }}" :value="__('Status Izin LN')" />
                                                            <x-text-input id="status_izin_ln_{{ $item->id }}" class="mt-1 w-full" type="text" name="status_izin_luar_negeri" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <x-input-label for="response_note_{{ $item->id }}" :value="__('Catatan')" />
                                                        <textarea id="response_note_{{ $item->id }}" name="response_note" rows="2" class="input-field mt-1"></textarea>
                                                    </div>
                                                    <x-primary-button>{{ __('Sinkronkan') }}</x-primary-button>
                                                </form>
                                            </details>
                                            <details>
                                                <summary class="cursor-pointer text-sm font-semibold text-rose-700">Tolak</summary>
                                                <form class="mt-3 space-y-3" method="POST" action="{{ route('regulator.str_sync.reject', $item) }}">
                                                    @csrf
                                                    <div>
                                                        <x-input-label for="reject_note_{{ $item->id }}" :value="__('Catatan Penolakan')" />
                                                        <textarea id="reject_note_{{ $item->id }}" name="response_note" rows="2" class="input-field mt-1" required></textarea>
                                                    </div>
                                                    <button class="table-action table-action-reject" type="submit">Tolak</button>
                                                </form>
                                            </details>
                                        @else
                                            <div class="text-xs text-slate-600">Diproses: {{ optional($item->processed_at)->format('d M Y') ?? '-' }}</div>
                                            <div class="text-xs text-slate-600">Catatan: {{ $item->response_note ?? '-' }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="4">Belum ada permintaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $requests->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
