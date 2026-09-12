<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Tambah Pelatihan</h2>
            <p class="section-subtitle">Lengkapi data pelatihan untuk dibuka ke peserta.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-6">
                @if (session('status'))
                    <div class="mb-4 alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('lembaga.pelatihan.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="lembaga_pelatihan_id" :value="__('Lembaga Pelatihan')" />
                        <select id="lembaga_pelatihan_id" name="lembaga_pelatihan_id" class="select-field mt-1" required>
                            <option value="" disabled selected>Pilih lembaga</option>
                            @foreach ($lembaga as $item)
                                <option value="{{ $item->id }}" @selected(old('lembaga_pelatihan_id') === $item->id)>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lembaga_pelatihan_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nama" :value="__('Nama Pelatihan')" />
                        <x-text-input id="nama" class="mt-1 w-full" type="text" name="nama" :value="old('nama')" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="deskripsi" :value="__('Deskripsi')" />
                        <textarea id="deskripsi" name="deskripsi" class="input-field mt-1" rows="3">{{ old('deskripsi') }}</textarea>
                        <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jumlah_jam" :value="__('Jumlah Jam (JP)')" />
                            <x-text-input id="jumlah_jam" class="mt-1 w-full" type="number" min="1" name="jumlah_jam" :value="old('jumlah_jam')" />
                            <x-input-error :messages="$errors->get('jumlah_jam')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="kuota" :value="__('Kuota')" />
                            <x-text-input id="kuota" class="mt-1 w-full" type="number" min="1" name="kuota" :value="old('kuota')" />
                            <x-input-error :messages="$errors->get('kuota')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_mulai" :value="__('Tanggal Mulai')" />
                            <x-text-input id="tanggal_mulai" class="mt-1 w-full" type="date" name="tanggal_mulai" :value="old('tanggal_mulai')" />
                            <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_selesai" :value="__('Tanggal Selesai')" />
                            <x-text-input id="tanggal_selesai" class="mt-1 w-full" type="date" name="tanggal_selesai" :value="old('tanggal_selesai')" />
                            <x-input-error :messages="$errors->get('tanggal_selesai')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="lokasi" :value="__('Lokasi')" />
                        <x-text-input id="lokasi" class="mt-1 w-full" type="text" name="lokasi" :value="old('lokasi')" />
                        <x-input-error :messages="$errors->get('lokasi')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="select-field mt-1">
                            <option value="dibuka" @selected(old('status', 'dibuka') === 'dibuka')>Dibuka</option>
                            <option value="ditutup" @selected(old('status', 'dibuka') === 'ditutup')>Ditutup</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
