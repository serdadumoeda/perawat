<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Buat Kunci API</h2>
            <p class="section-subtitle">Tetapkan cakupan akses untuk integrasi data.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                @if ($errors->any())
                    <div class="mb-4 alert alert-danger">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-4 text-sm text-slate-600">
                    Gunakan header <span class="font-mono">X-API-KEY</span> pada request API.
                </div>

                <form method="POST" action="{{ route('admin.api_keys.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Nama Kunci')" />
                        <x-text-input id="name" class="mt-1 w-full" type="text" name="name" :value="old('name')" required />
                    </div>

                    <div>
                        <x-input-label for="owner_type" :value="__('Tipe Pemilik')" />
                        <select id="owner_type" name="owner_type" class="select-field mt-1" required>
                            <option value="" disabled selected>Pilih tipe</option>
                            <option value="institusi" @selected(old('owner_type') === 'institusi')>Institusi Pendidikan</option>
                            <option value="lembaga" @selected(old('owner_type') === 'lembaga')>Lembaga Pelatihan</option>
                            <option value="regulator" @selected(old('owner_type') === 'regulator')>Regulator (User)</option>
                            <option value="regulator_org" @selected(old('owner_type') === 'regulator_org')>Regulator (Organisasi)</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="owner_id" :value="__('Pemilik')" />
                        <select id="owner_id" name="owner_id" class="select-field mt-1" required>
                            <option value="" disabled selected>Pilih pemilik</option>
                            @foreach ($institusi as $item)
                                <option value="{{ $item->id }}" data-type="institusi" @selected(old('owner_id') === $item->id)>
                                    Institusi: {{ $item->nama }}
                                </option>
                            @endforeach
                            @foreach ($lembaga as $item)
                                <option value="{{ $item->id }}" data-type="lembaga" @selected(old('owner_id') === $item->id)>
                                    Lembaga: {{ $item->nama }}
                                </option>
                            @endforeach
                            @foreach ($regulator as $item)
                                <option value="{{ $item->id }}" data-type="regulator" @selected(old('owner_id') === $item->id)>
                                    Regulator: {{ $item->name }} ({{ $item->email }})
                                </option>
                            @endforeach
                            @foreach ($regulatorOrganisasi as $item)
                                <option value="{{ $item->id }}" data-type="regulator_org" @selected(old('owner_id') === $item->id)>
                                    Organisasi Regulator: {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-xs text-slate-500 mt-1">Pastikan tipe pemilik sesuai dengan pilihan pemilik.</div>
                    </div>

                    <div>
                        <x-input-label :value="__('Cakupan')" />
                        <div class="mt-2 space-y-2 text-sm">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="checkbox-field" name="scopes[]" value="lulusan:write" @checked(in_array('lulusan:write', old('scopes', []), true))>
                                <span>Lulusan (lulusan:write)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="checkbox-field" name="scopes[]" value="sertifikasi:write" @checked(in_array('sertifikasi:write', old('scopes', []), true))>
                                <span>Sertifikasi (sertifikasi:write)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="checkbox-field" name="scopes[]" value="legalitas:write" @checked(in_array('legalitas:write', old('scopes', []), true))>
                                <span>Legalitas (legalitas:write)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" class="checkbox-field" name="require_signature" value="1" @checked(old('require_signature'))>
                            Wajibkan HMAC signature (X-API-TIMESTAMP + X-API-SIGNATURE)
                        </label>
                        <div class="text-xs text-slate-500 mt-1">Jika aktif, API key hanya bisa dipakai dengan signature HMAC SHA256.</div>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
