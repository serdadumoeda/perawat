<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Edit Data Lulusan</h2>
            <p class="section-subtitle">Perbarui data lulusan dan dokumen akademik.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 module-container">
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

            <div class="module-card card-surface p-6">
                <div class="text-sm text-slate-600">Institusi: {{ $selectedInstitusi->nama }}</div>
                <div class="text-lg font-semibold text-slate-900 mt-1">{{ $perawat->nama }}</div>
                <div class="text-sm text-slate-600">NIK: {{ $perawat->nik }}</div>
            </div>

            <div class="module-card card-surface p-6">
                <form method="POST" action="{{ route('kampus.lulusan.update', $perawat) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="institusi_pendidikan_id" value="{{ $selectedInstitusi->id }}">

                    <div>
                        <x-input-label for="nik" :value="__('NIK (tidak dapat diubah)')" />
                        <x-text-input id="nik" class="mt-2" type="text" name="nik" :value="$perawat->nik" disabled />
                    </div>

                    <div>
                        <x-input-label for="nama" :value="__('Nama Lengkap')" />
                        <x-text-input id="nama" class="mt-2" type="text" name="nama" :value="old('nama', $perawat->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="mt-2" type="email" name="email" :value="old('email', $perawat->email)" />
                    </div>

                    <div>
                        <x-input-label for="no_hp" :value="__('No. HP')" />
                        <x-text-input id="no_hp" class="mt-2" type="text" name="no_hp" :value="old('no_hp', $perawat->no_hp)" />
                    </div>

                    <div>
                        <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                        <select id="jenis_kelamin" name="jenis_kelamin" class="select-field mt-2">
                            <option value="">Pilih</option>
                            <option value="L" @selected(old('jenis_kelamin', $perawat->jenis_kelamin) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $perawat->jenis_kelamin) === 'P')>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                        <x-text-input id="tempat_lahir" class="mt-2" type="text" name="tempat_lahir" :value="old('tempat_lahir', $perawat->tempat_lahir)" />
                    </div>

                    <div>
                        <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                        <x-text-input id="tanggal_lahir" class="mt-2" type="date" name="tanggal_lahir" :value="old('tanggal_lahir', optional($perawat->tanggal_lahir)->format('Y-m-d'))" />
                    </div>

                    <div>
                        <x-input-label for="alamat" :value="__('Alamat')" />
                        <textarea id="alamat" name="alamat" class="input-field mt-2" rows="3">{{ old('alamat', $perawat->alamat) }}</textarea>
                    </div>

                    <div class="border-t border-slate-200 pt-6">
                        <div class="panel-title mb-4">Data Akademik</div>

                        <div>
                            <x-input-label for="jenjang" :value="__('Jenjang')" />
                            <x-text-input id="jenjang" class="mt-2" type="text" name="jenjang" :value="old('jenjang', $riwayat->jenjang)" />
                        </div>

                        <div>
                            <x-input-label for="program_studi" :value="__('Program Studi')" />
                            <x-text-input id="program_studi" class="mt-2" type="text" name="program_studi" :value="old('program_studi', $riwayat->program_studi)" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nomor_ijazah" :value="__('Nomor Ijazah')" />
                                <x-text-input id="nomor_ijazah" class="mt-2" type="text" name="nomor_ijazah" :value="old('nomor_ijazah', $riwayat->nomor_ijazah)" />
                            </div>
                            <div>
                                <x-input-label for="tanggal_ijazah" :value="__('Tanggal Ijazah')" />
                                <x-text-input id="tanggal_ijazah" class="mt-2" type="date" name="tanggal_ijazah" :value="old('tanggal_ijazah', optional($riwayat->tanggal_ijazah)->format('Y-m-d'))" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="tahun_masuk" :value="__('Tahun Masuk')" />
                                <x-text-input id="tahun_masuk" class="mt-2" type="number" name="tahun_masuk" :value="old('tahun_masuk', $riwayat->tahun_masuk)" />
                            </div>
                            <div>
                                <x-input-label for="tahun_lulus" :value="__('Tahun Lulus')" />
                                <x-text-input id="tahun_lulus" class="mt-2" type="number" name="tahun_lulus" :value="old('tahun_lulus', $riwayat->tahun_lulus)" />
                            </div>
                            <div>
                                <x-input-label for="ipk" :value="__('IPK')" />
                                <x-text-input id="ipk" class="mt-2" type="number" step="0.01" name="ipk" :value="old('ipk', $riwayat->ipk)" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="transkrip" :value="__('Transkrip (PDF/JPG/PNG)')" />
                            <input id="transkrip" type="file" name="transkrip" class="input-field mt-2" />
                            @if ($riwayat->transkrip_path)
                                <a class="link-muted mt-2 inline-flex" href="{{ Storage::url($riwayat->transkrip_path) }}" target="_blank">Lihat Transkrip Saat Ini</a>
                            @endif
                        </div>

                        <div>
                            <x-input-label for="ijazah" :value="__('Ijazah (PDF/JPG/PNG)')" />
                            <input id="ijazah" type="file" name="ijazah" class="input-field mt-2" />
                            @if ($riwayat->ijazah_path)
                                <a class="link-muted mt-2 inline-flex" href="{{ Storage::url($riwayat->ijazah_path) }}" target="_blank">Lihat Ijazah Saat Ini</a>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <a class="btn-ghost" href="{{ route('kampus.lulusan.index', ['institusi_id' => $selectedInstitusi->id]) }}">Kembali</a>
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
