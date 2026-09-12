<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Update Tracing Alumni</h2>
            <p class="section-subtitle">Perbarui status alumni setelah lulus.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 module-container">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card card-surface p-6">
                <div class="text-sm text-slate-600">Institusi: {{ $selectedInstitusi->nama }}</div>
                <div class="text-2xl font-semibold text-slate-900 mt-1">{{ $perawat->nama }}</div>
                <div class="text-sm text-slate-600">NIK: {{ $perawat->nik }}</div>
                <div class="text-sm text-slate-600">Status Ketenagakerjaan: {{ $perawat->status_ketenagakerjaan ?? '-' }}</div>
            </div>

            <div class="module-card card-surface p-6">
                <form method="POST" action="{{ route('kampus.tracing.update', $perawat) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="institusi_pendidikan_id" value="{{ $selectedInstitusi->id }}">

                    <div>
                        <x-input-label for="status" :value="__('Status Tracing')" />
                        <select id="status" name="status" class="select-field mt-1" required>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" @selected(old('status', $tracing->status ?? '') === $status)>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="instansi" :value="__('Instansi/Tempat Kerja/Studi')" />
                        <x-text-input id="instansi" class="mt-1 w-full" type="text" name="instansi" :value="old('instansi', $tracing->instansi)" />
                        <x-input-error :messages="$errors->get('instansi')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="posisi" :value="__('Posisi')" />
                        <x-text-input id="posisi" class="mt-1 w-full" type="text" name="posisi" :value="old('posisi', $tracing->posisi)" />
                        <x-input-error :messages="$errors->get('posisi')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kota" :value="__('Kota')" />
                            <x-text-input id="kota" class="mt-1 w-full" type="text" name="kota" :value="old('kota', $tracing->kota)" />
                            <x-input-error :messages="$errors->get('kota')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="provinsi" :value="__('Provinsi')" />
                            <x-text-input id="provinsi" class="mt-1 w-full" type="text" name="provinsi" :value="old('provinsi', $tracing->provinsi)" />
                            <x-input-error :messages="$errors->get('provinsi')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="tanggal_mulai" :value="__('Tanggal Mulai')" />
                        <x-text-input id="tanggal_mulai" class="mt-1 w-full" type="date" name="tanggal_mulai" :value="old('tanggal_mulai', optional($tracing->tanggal_mulai)->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="catatan" :value="__('Catatan')" />
                        <textarea id="catatan" name="catatan" class="input-field mt-1" rows="4">{{ old('catatan', $tracing->catatan) }}</textarea>
                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <a class="btn-ghost" href="{{ route('kampus.tracing.index', ['institusi_id' => $selectedInstitusi->id]) }}">Kembali</a>
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
