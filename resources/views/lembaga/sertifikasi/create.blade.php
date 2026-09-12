<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Input Sertifikasi Kompetensi</h2>
            <p class="section-subtitle">Catat sertifikat perawat dan unggah dokumen pendukung.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-6">
                @if (session('status'))
                    <div class="mb-4 alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <div class="panel-title">Form Sertifikasi</div>
                        <div class="panel-desc">Pastikan data sesuai dokumen resmi.</div>
                    </div>
                    <a class="btn-secondary" href="{{ route('lembaga.sertifikasi.import') }}">Impor Sertifikasi (CSV)</a>
                </div>

                <form method="POST" action="{{ route('lembaga.sertifikasi.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    @if ($lembaga->isEmpty())
                        <div class="alert alert-danger">
                            Akun belum terhubung dengan lembaga pelatihan. Hubungi admin.
                        </div>
                    @endif

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
                        <x-input-label for="nik" :value="__('NIK Perawat')" />
                        <x-text-input id="nik" class="mt-1 w-full" type="text" name="nik" :value="old('nik')" />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nama_sertifikat" :value="__('Nama Sertifikat')" />
                        <x-text-input id="nama_sertifikat" class="mt-1 w-full" type="text" name="nama_sertifikat" :value="old('nama_sertifikat')" required />
                        <x-input-error :messages="$errors->get('nama_sertifikat')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jumlah_jam" :value="__('Jumlah Jam (JP)')" />
                            <x-text-input id="jumlah_jam" class="mt-1 w-full" type="number" min="1" name="jumlah_jam" :value="old('jumlah_jam')" />
                            <x-input-error :messages="$errors->get('jumlah_jam')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nilai_kompetensi" :value="__('Nilai Kompetensi')" />
                            <x-text-input id="nilai_kompetensi" class="mt-1 w-full" type="number" step="0.01" min="0" max="100" name="nilai_kompetensi" :value="old('nilai_kompetensi')" />
                            <x-input-error :messages="$errors->get('nilai_kompetensi')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="nomor_sertifikat" :value="__('Nomor Sertifikat')" />
                        <x-text-input id="nomor_sertifikat" class="mt-1 w-full" type="text" name="nomor_sertifikat" :value="old('nomor_sertifikat')" />
                        <x-input-error :messages="$errors->get('nomor_sertifikat')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="file_sertifikat" :value="__('File Sertifikat (PDF/JPG/PNG)')" />
                        <input id="file_sertifikat" type="file" name="file_sertifikat" class="input-field mt-1" />
                        <x-input-error :messages="$errors->get('file_sertifikat')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_terbit" :value="__('Tanggal Terbit')" />
                            <x-text-input id="tanggal_terbit" class="mt-1 w-full" type="date" name="tanggal_terbit" :value="old('tanggal_terbit')" />
                            <x-input-error :messages="$errors->get('tanggal_terbit')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_kadaluarsa" :value="__('Tanggal Kadaluarsa')" />
                            <x-text-input id="tanggal_kadaluarsa" class="mt-1 w-full" type="date" name="tanggal_kadaluarsa" :value="old('tanggal_kadaluarsa')" />
                            <x-input-error :messages="$errors->get('tanggal_kadaluarsa')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="status_verifikasi" :value="__('Status Verifikasi')" />
                        <select id="status_verifikasi" name="status_verifikasi" class="select-field mt-1">
                            <option value="pending" @selected(old('status_verifikasi', 'pending') === 'pending')>Pending</option>
                            <option value="verified" @selected(old('status_verifikasi', 'pending') === 'verified')>Terverifikasi</option>
                            <option value="rejected" @selected(old('status_verifikasi', 'pending') === 'rejected')>Ditolak</option>
                            <option value="valid" @selected(old('status_verifikasi', 'pending') === 'valid')>Valid</option>
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
