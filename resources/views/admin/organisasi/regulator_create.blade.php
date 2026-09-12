<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Tambah Organisasi Regulator</h2>
            <p class="section-subtitle">Daftarkan instansi regulator sebagai entitas governance eksplisit.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <form method="POST" action="{{ route('admin.regulator_org.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="nama" :value="__('Nama Organisasi')" />
                        <x-text-input id="nama" class="mt-2" type="text" name="nama" :value="old('nama')" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="kode" :value="__('Kode Organisasi')" />
                        <x-text-input id="kode" class="mt-2" type="text" name="kode" :value="old('kode')" />
                        <x-input-error :messages="$errors->get('kode')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tingkat" :value="__('Tingkat')" />
                        <select id="tingkat" name="tingkat" class="select-field mt-2">
                            <option value="">Pilih tingkat</option>
                            <option value="pusat" @selected(old('tingkat') === 'pusat')>Pusat</option>
                            <option value="provinsi" @selected(old('tingkat') === 'provinsi')>Provinsi</option>
                            <option value="kabupaten_kota" @selected(old('tingkat') === 'kabupaten_kota')>Kabupaten/Kota</option>
                        </select>
                        <x-input-error :messages="$errors->get('tingkat')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="wilayah" :value="__('Wilayah')" />
                        <x-text-input id="wilayah" class="mt-2" type="text" name="wilayah" :value="old('wilayah')" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="mt-2" type="email" name="email" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="telepon" :value="__('Telepon')" />
                        <x-text-input id="telepon" class="mt-2" type="text" name="telepon" :value="old('telepon')" />
                        <x-input-error :messages="$errors->get('telepon')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="alamat" :value="__('Alamat')" />
                        <textarea id="alamat" name="alamat" rows="3" class="input-field mt-2">{{ old('alamat') }}</textarea>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" class="checkbox-field" name="is_active" value="1" @checked(old('is_active', true))>
                            Aktif
                        </label>
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
