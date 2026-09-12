<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Edit Profil Perawat</h2>
            <p class="section-subtitle">Perbarui data pribadi dan ketersediaan kerja.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
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
                <div class="mb-4 text-sm text-slate-600">
                    Status kesiapan dan ketenagakerjaan dihitung otomatis dari pendidikan, sertifikasi, dan legalitas.
                </div>

                <form method="POST" action="{{ route('perawat.profil.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="nik" :value="__('NIK (tidak dapat diubah)')" />
                        <x-text-input id="nik" class="mt-1 w-full" type="text" name="nik" :value="$perawat->nik" disabled />
                    </div>

                    <div>
                        <x-input-label for="nama" :value="__('Nama Lengkap')" />
                        <x-text-input id="nama" class="mt-1 w-full" type="text" name="nama" :value="old('nama', $perawat->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="mt-1 w-full" type="email" name="email" :value="old('email', $perawat->email)" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="no_hp" :value="__('No. HP')" />
                            <x-text-input id="no_hp" class="mt-1 w-full" type="text" name="no_hp" :value="old('no_hp', $perawat->no_hp)" />
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                            <select id="jenis_kelamin" name="jenis_kelamin" class="select-field mt-1">
                                <option value="">Pilih</option>
                                <option value="L" @selected(old('jenis_kelamin', $perawat->jenis_kelamin) === 'L')>Laki-laki</option>
                                <option value="P" @selected(old('jenis_kelamin', $perawat->jenis_kelamin) === 'P')>Perempuan</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                            <x-text-input id="tanggal_lahir" class="mt-1 w-full" type="date" name="tanggal_lahir" :value="old('tanggal_lahir', optional($perawat->tanggal_lahir)->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tempat_lahir" :value="__('Tempat Lahir')" />
                            <x-text-input id="tempat_lahir" class="mt-1 w-full" type="text" name="tempat_lahir" :value="old('tempat_lahir', $perawat->tempat_lahir)" />
                            <x-input-error :messages="$errors->get('tempat_lahir')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="status_ketersediaan" :value="__('Ketersediaan')" />
                            <select id="status_ketersediaan" name="status_ketersediaan" class="select-field mt-1">
                                <option value="available" @selected(old('status_ketersediaan', $perawat->status_ketersediaan) === 'available')>Tersedia</option>
                                <option value="open_to_offer" @selected(old('status_ketersediaan', $perawat->status_ketersediaan) === 'open_to_offer')>Terbuka untuk tawaran</option>
                                <option value="not_available" @selected(old('status_ketersediaan', $perawat->status_ketersediaan) === 'not_available')>Tidak tersedia</option>
                            </select>
                            <x-input-error :messages="$errors->get('status_ketersediaan')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="alamat" :value="__('Alamat')" />
                        <textarea id="alamat" name="alamat" class="input-field mt-1" rows="3">{{ old('alamat', $perawat->alamat) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kota" :value="__('Kota')" />
                            <x-text-input id="kota" class="mt-1 w-full" type="text" name="kota" :value="old('kota', $perawat->kota)" />
                            <x-input-error :messages="$errors->get('kota')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="provinsi" :value="__('Provinsi')" />
                            <x-text-input id="provinsi" class="mt-1 w-full" type="text" name="provinsi" :value="old('provinsi', $perawat->provinsi)" />
                            <x-input-error :messages="$errors->get('provinsi')" class="mt-2" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title">Profil Publik</div>
                <div class="panel-desc mt-1">Aktifkan untuk membagikan profil/CV terverifikasi ke pihak luar.</div>

                <form method="POST" action="{{ route('perawat.profil.share') }}" class="mt-4">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-center gap-3">
                        <input id="public_profile_enabled" type="checkbox" name="public_profile_enabled" value="1" class="checkbox-field" @checked(old('public_profile_enabled', $perawat->public_profile_enabled)) />
                        <label for="public_profile_enabled" class="text-sm text-slate-700">Aktifkan profil publik</label>
                    </div>

                    @if ($perawat->public_profile_enabled && $perawat->public_profile_token)
                        @php($publicUrl = route('public.perawat.show', $perawat->public_profile_token))
                        <div class="mt-3 text-sm">
                            <div class="text-slate-600">Tautan publik:</div>
                            <div class="text-slate-900 break-all">{{ $publicUrl }}</div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-slate-600 mb-2">QR Code:</div>
                            <div id="public-qr" class="inline-block border border-slate-200 rounded-lg p-2 bg-white" aria-label="QR Public Profile"></div>
                            <noscript>
                                <div class="text-xs text-slate-500 mt-2">Aktifkan JavaScript untuk melihat QR Code.</div>
                            </noscript>
                        </div>
                        <script src="{{ asset('js/qrcode.min.js') }}"></script>
                        <script>
                            (function () {
                                var el = document.getElementById('public-qr');
                                if (!el || typeof QRCode === 'undefined') return;
                                new QRCode(el, {
                                    text: @json($publicUrl),
                                    width: 180,
                                    height: 180,
                                    correctLevel: QRCode.CorrectLevel.M
                                });
                            })();
                        </script>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-3">
                        <x-primary-button>{{ __('Simpan Pengaturan') }}</x-primary-button>
                        <button type="submit" name="regenerate_token" value="1" class="btn-secondary">Buat Ulang Tautan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
