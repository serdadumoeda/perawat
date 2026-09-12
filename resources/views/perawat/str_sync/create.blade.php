<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Sinkronisasi STR</h2>
            <p class="section-subtitle">Ajukan pembaruan status STR ke regulator.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
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
                <div class="panel-title mb-2">Data legalitas saat ini</div>
                <div class="text-sm text-slate-600 space-y-1">
                    <div>Nomor STR: {{ $legalitas?->nomor_str ?? '-' }}</div>
                    <div>Status STR: {{ $legalitas?->status_str ?? '-' }}</div>
                    <div class="flex items-center gap-2">
                        <span>Status Data STR:</span>
                        <x-legalitas-data-badge :status="$legalitas?->data_status" />
                    </div>
                    @if ($legalitas?->str_document_path)
                        <div>Dokumen STR: <a class="btn-ghost" href="{{ Storage::url($legalitas->str_document_path) }}" target="_blank">Lihat Dokumen</a></div>
                    @endif
                    <div>Nomor Izin Luar Negeri: {{ $legalitas?->nomor_izin_luar_negeri ?? '-' }}</div>
                    @if ($legalitas?->izin_luar_negeri_document_path)
                        <div>Dokumen Izin LN: <a class="btn-ghost" href="{{ Storage::url($legalitas->izin_luar_negeri_document_path) }}" target="_blank">Lihat Dokumen</a></div>
                    @endif
                </div>
            </div>

            @if ($latestRequest)
                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="panel-title mb-2">Permintaan Terakhir</div>
                    <div class="text-sm text-slate-600 space-y-1">
                        <div>Status: {{ $latestRequest->status }}</div>
                        <div>Nomor STR: {{ $latestRequest->nomor_str }}</div>
                        <div>Catatan: {{ $latestRequest->catatan ?? '-' }}</div>
                        <div>Respon: {{ $latestRequest->response_note ?? '-' }}</div>
                    </div>
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <form method="POST" action="{{ route('perawat.str_sync.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="nomor_str" :value="__('Nomor STR')" />
                        <x-text-input id="nomor_str" class="mt-1 w-full" type="text" name="nomor_str" :value="old('nomor_str')" required />
                        <x-input-error :messages="$errors->get('nomor_str')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="str_document" :value="__('Upload Dokumen STR (PDF/JPG/PNG)')" />
                        <input id="str_document" type="file" name="str_document" class="input-field mt-1" />
                        <x-input-error :messages="$errors->get('str_document')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nomor_izin_luar_negeri" :value="__('Nomor Izin Luar Negeri (opsional)')" />
                        <x-text-input id="nomor_izin_luar_negeri" class="mt-1 w-full" type="text" name="nomor_izin_luar_negeri" :value="old('nomor_izin_luar_negeri')" />
                        <x-input-error :messages="$errors->get('nomor_izin_luar_negeri')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="izin_luar_negeri_document" :value="__('Upload Dokumen Izin Luar Negeri (PDF/JPG/PNG)')" />
                        <input id="izin_luar_negeri_document" type="file" name="izin_luar_negeri_document" class="input-field mt-1" />
                        <x-input-error :messages="$errors->get('izin_luar_negeri_document')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="catatan" :value="__('Catatan (opsional)')" />
                        <textarea id="catatan" name="catatan" rows="4" class="input-field mt-1">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Ajukan Sinkronisasi') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
