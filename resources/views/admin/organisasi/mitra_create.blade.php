<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Tambah Mitra Pengguna</h2>
            <p class="section-subtitle">Lengkapi data RS/Agency/Industry sebagai mitra pengguna.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <form method="POST" action="{{ route('admin.mitra.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="nama" :value="__('Nama Mitra')" />
                        <x-text-input id="nama" class="mt-2" type="text" name="nama" :value="old('nama')" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="kategori" :value="__('Kategori')" />
                        <x-text-input id="kategori" class="mt-2" type="text" name="kategori" :value="old('kategori')" />
                    </div>
                    <div>
                        <x-input-label for="kota" :value="__('Kota')" />
                        <x-text-input id="kota" class="mt-2" type="text" name="kota" :value="old('kota')" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="mt-2" type="email" name="email" :value="old('email')" />
                    </div>
                    <div>
                        <x-input-label for="alamat" :value="__('Alamat')" />
                        <textarea id="alamat" name="alamat" rows="3" class="input-field mt-2">{{ old('alamat') }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
