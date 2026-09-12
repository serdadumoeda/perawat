<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Input Lulusan Baru</h2>
            <p class="section-subtitle">Tambahkan data lulusan beserta dokumen akademik.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="module-card card-surface p-6 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
                    <div>Butuh upload banyak data?</div>
                    <a class="btn-ghost" href="{{ route('kampus.lulusan.import') }}">Impor CSV</a>
                </div>

                <form method="POST" action="{{ route('kampus.lulusan.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    @if ($institusi->isEmpty())
                        <div class="alert alert-warning">Akun belum terhubung dengan institusi pendidikan. Hubungi admin.</div>
                    @endif

                    <div>
                        <x-input-label for="institusi_pendidikan_id" :value="__('Institusi Pendidikan')" />
                        <select id="institusi_pendidikan_id" name="institusi_pendidikan_id" class="select-field mt-2" required>
                            <option value="" disabled selected>Pilih institusi</option>
                            @foreach ($institusi as $item)
                                <option value="{{ $item->id }}" @selected(old('institusi_pendidikan_id') === $item->id)>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('institusi_pendidikan_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nik" :value="__('NIK')" />
                        <x-text-input id="nik" class="mt-2" type="text" name="nik" :value="old('nik')" required />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nama" :value="__('Nama')" />
                        <x-text-input id="nama" class="mt-2" type="text" name="nama" :value="old('nama')" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="mt-2" type="email" name="email" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_hp" :value="__('No HP')" />
                        <x-text-input id="no_hp" class="mt-2" type="text" name="no_hp" :value="old('no_hp')" />
                        <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                            <select id="jenis_kelamin" name="jenis_kelamin" class="select-field mt-2">
                                <option value="">Pilih</option>
                                <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                                <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                            <x-text-input id="tanggal_lahir" class="mt-2" type="date" name="tanggal_lahir" :value="old('tanggal_lahir')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                        <x-text-input id="tempat_lahir" class="mt-2" type="text" name="tempat_lahir" :value="old('tempat_lahir')" />
                    </div>

                    <div>
                        <x-input-label for="alamat" :value="__('Alamat')" />
                        <textarea id="alamat" name="alamat" class="input-field mt-2" rows="2">{{ old('alamat') }}</textarea>
                    </div>

                    <div>
                        <x-input-label for="jenjang" :value="__('Jenjang')" />
                        <x-text-input id="jenjang" class="mt-2" type="text" name="jenjang" :value="old('jenjang')" />
                        <x-input-error :messages="$errors->get('jenjang')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="program_studi" :value="__('Program Studi')" />
                        <x-text-input id="program_studi" class="mt-2" type="text" name="program_studi" :value="old('program_studi')" />
                        <x-input-error :messages="$errors->get('program_studi')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tahun_masuk" :value="__('Tahun Masuk')" />
                            <x-text-input id="tahun_masuk" class="mt-2" type="text" name="tahun_masuk" :value="old('tahun_masuk')" />
                        </div>
                        <div>
                            <x-input-label for="tahun_lulus" :value="__('Tahun Lulus')" />
                            <x-text-input id="tahun_lulus" class="mt-2" type="text" name="tahun_lulus" :value="old('tahun_lulus')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="nomor_ijazah" :value="__('Nomor Ijazah')" />
                        <x-text-input id="nomor_ijazah" class="mt-2" type="text" name="nomor_ijazah" :value="old('nomor_ijazah')" />
                        <x-input-error :messages="$errors->get('nomor_ijazah')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="transkrip" :value="__('Transkrip (PDF/JPG/PNG)')" />
                        <input id="transkrip" type="file" name="transkrip" class="input-field mt-2" />
                        <x-input-error :messages="$errors->get('transkrip')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="ijazah" :value="__('Ijazah (PDF/JPG/PNG)')" />
                        <input id="ijazah" type="file" name="ijazah" class="input-field mt-2" />
                        <x-input-error :messages="$errors->get('ijazah')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_ijazah" :value="__('Tanggal Ijazah')" />
                            <x-text-input id="tanggal_ijazah" class="mt-2" type="date" name="tanggal_ijazah" :value="old('tanggal_ijazah')" />
                            <x-input-error :messages="$errors->get('tanggal_ijazah')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="ipk" :value="__('IPK')" />
                            <x-text-input id="ipk" class="mt-2" type="text" name="ipk" :value="old('ipk')" />
                            <x-input-error :messages="$errors->get('ipk')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
