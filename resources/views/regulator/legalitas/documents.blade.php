<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Review Dokumen Legalitas</h2>
            <p class="section-subtitle">Setujui atau tolak dokumen STR dan izin luar negeri.</p>
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
                        <x-input-label for="doc_type" :value="__('Tipe Dokumen')" />
                        <select id="doc_type" name="doc_type" class="select-field mt-1">
                            <option value="all" @selected($docType === 'all')>Semua</option>
                            <option value="str" @selected($docType === 'str')>STR</option>
                            <option value="izin_luar_negeri" @selected($docType === 'izin_luar_negeri')>Izin Luar Negeri</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="select-field mt-1">
                            <option value="pending" @selected($status === 'pending')>Menunggu</option>
                            <option value="approved" @selected($status === 'approved')>Disetujui</option>
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
                    <table class="data-table min-w-[1220px]">
                        <thead>
                            <tr>
                                <th>Perawat</th>
                                <th>Dokumen STR</th>
                                <th>Dokumen Izin LN</th>
                                <th>Metadata Verifikasi</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documents as $item)
                                @php($strStatus = \App\Models\DataLegalitas::normalizeDocStatus($item->str_document_status))
                                @php($izinStatus = \App\Models\DataLegalitas::normalizeDocStatus($item->izin_luar_negeri_document_status))
                                <tr class="align-top">
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $item->perawat?->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->perawat?->nik ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @if ($item->str_document_path)
                                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                                <a class="btn-ghost" href="{{ Storage::url($item->str_document_path) }}" target="_blank">Lihat Dokumen</a>
                                                <a class="btn-ghost" href="{{ route('regulator.legalitas.documents.audit', ['legalitas' => $item->id, 'doc_type' => 'str']) }}">Riwayat</a>
                                            </div>
                                            <div class="text-xs text-slate-500 mb-2">Status: {{ $strStatus }}</div>
                                            @if ($strStatus === \App\Models\DataLegalitas::DOC_STATUS_PENDING)
                                                <form class="inline-block mr-2" method="POST" action="{{ route('regulator.legalitas.documents.review', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="document_type" value="str">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button class="table-action table-action-approve" type="submit">Setujui</button>
                                                </form>
                                                <form class="mt-2" method="POST" action="{{ route('regulator.legalitas.documents.review', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="document_type" value="str">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="text" name="note" placeholder="Catatan penolakan" class="input-field text-xs mb-2" />
                                                    <button class="table-action table-action-reject" type="submit">Tolak</button>
                                                </form>
                                            @else
                                                <form class="mt-2" method="POST" action="{{ route('regulator.legalitas.documents.reopen', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="document_type" value="str">
                                                    <input type="text" name="note" placeholder="Alasan buka ulang" class="input-field text-xs mb-2" />
                                                    <button class="table-action table-action-reopen" type="submit">Buka Ulang</button>
                                                </form>
                                            @endif
                                        @else
                                            <div class="text-xs text-slate-500">Tidak ada dokumen.</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->izin_luar_negeri_document_path)
                                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                                <a class="btn-ghost" href="{{ Storage::url($item->izin_luar_negeri_document_path) }}" target="_blank">Lihat Dokumen</a>
                                                <a class="btn-ghost" href="{{ route('regulator.legalitas.documents.audit', ['legalitas' => $item->id, 'doc_type' => 'izin_luar_negeri']) }}">Riwayat</a>
                                            </div>
                                            <div class="text-xs text-slate-500 mb-2">Status: {{ $izinStatus }}</div>
                                            @if ($izinStatus === \App\Models\DataLegalitas::DOC_STATUS_PENDING)
                                                <form class="inline-block mr-2" method="POST" action="{{ route('regulator.legalitas.documents.review', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="document_type" value="izin_luar_negeri">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button class="table-action table-action-approve" type="submit">Setujui</button>
                                                </form>
                                                <form class="mt-2" method="POST" action="{{ route('regulator.legalitas.documents.review', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="document_type" value="izin_luar_negeri">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="text" name="note" placeholder="Catatan penolakan" class="input-field text-xs mb-2" />
                                                    <button class="table-action table-action-reject" type="submit">Tolak</button>
                                                </form>
                                            @else
                                                <form class="mt-2" method="POST" action="{{ route('regulator.legalitas.documents.reopen', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="document_type" value="izin_luar_negeri">
                                                    <input type="text" name="note" placeholder="Alasan buka ulang" class="input-field text-xs mb-2" />
                                                    <button class="table-action table-action-reopen" type="submit">Buka Ulang</button>
                                                </form>
                                            @endif
                                        @else
                                            <div class="text-xs text-slate-500">Tidak ada dokumen.</div>
                                        @endif
                                    </td>
                                    <td class="min-w-[360px]">
                                        <div class="space-y-4">
                                            @if ($item->str_document_path)
                                                <div class="rounded-lg border border-slate-200 p-3">
                                                    <div class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">STR</div>
                                                    <x-review-metadata
                                                        :status="$strStatus"
                                                        :rejected-reason="$item->str_document_note"
                                                        :verified-by="$item->strDocumentReviewedBy?->name"
                                                        :verified-at="$item->str_document_reviewed_at"
                                                        :source-system="$item->source_system"
                                                        :source-actor="$item->source_actor"
                                                        :source-organisasi="$item->regulatorOrganisasi?->nama"
                                                    />
                                                </div>
                                            @endif
                                            @if ($item->izin_luar_negeri_document_path)
                                                <div class="rounded-lg border border-slate-200 p-3">
                                                    <div class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Izin Luar Negeri</div>
                                                    <x-review-metadata
                                                        :status="$izinStatus"
                                                        :rejected-reason="$item->izin_luar_negeri_document_note"
                                                        :verified-by="$item->izinLuarNegeriDocumentReviewedBy?->name"
                                                        :verified-at="$item->izin_luar_negeri_document_reviewed_at"
                                                        :source-system="$item->source_system"
                                                        :source-actor="$item->source_actor"
                                                        :source-organisasi="$item->regulatorOrganisasi?->nama"
                                                    />
                                                </div>
                                            @endif
                                            @if (!$item->str_document_path && !$item->izin_luar_negeri_document_path)
                                                <div class="text-xs text-slate-500">Belum ada metadata review.</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-xs text-slate-600">
                                        {{ $item->updated_at?->format('d M Y') ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada dokumen untuk direview.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $documents->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
