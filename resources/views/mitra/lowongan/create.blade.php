<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Buat Lowongan</h2>
            <p class="section-subtitle">Lengkapi kebutuhan posisi dan kriteria kandidat.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('mitra.lowongan.store') }}" class="space-y-4">
                    @csrf

                    @if (auth()->user()->mitraPengguna->isEmpty())
                        <div class="alert alert-danger">
                            Akun belum terhubung dengan mitra pengguna. Hubungi admin.
                        </div>
                    @endif

                    <div>
                        <x-input-label for="mitra_pengguna_id" :value="__('Mitra Pengguna')" />
                        <select id="mitra_pengguna_id" name="mitra_pengguna_id" class="select-field mt-1" required>
                            <option value="" disabled selected>Pilih mitra</option>
                            @foreach (auth()->user()->mitraPengguna as $item)
                                <option value="{{ $item->id }}" @selected(old('mitra_pengguna_id') === $item->id)>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('mitra_pengguna_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="judul" :value="__('Judul')" />
                        <x-text-input id="judul" class="mt-1 w-full" type="text" name="judul" :value="old('judul')" required />
                        <x-input-error :messages="$errors->get('judul')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="deskripsi" :value="__('Deskripsi')" />
                        <textarea id="deskripsi" name="deskripsi" class="input-field mt-1" rows="4">{{ old('deskripsi') }}</textarea>
                    </div>

                    <div>
                        <x-input-label for="tipe_penempatan" :value="__('Tipe Penempatan')" />
                        <select id="tipe_penempatan" name="tipe_penempatan" class="select-field mt-1" required>
                            <option value="domestik" @selected(old('tipe_penempatan', 'domestik') === 'domestik')>Domestik (Dalam Negeri)</option>
                            <option value="luar_negeri" @selected(old('tipe_penempatan') === 'luar_negeri')>Luar Negeri</option>
                        </select>
                        <x-input-error :messages="$errors->get('tipe_penempatan')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="negara_penempatan" :value="__('Negara Penempatan (wajib untuk luar negeri)')" />
                            <x-text-input id="negara_penempatan" class="mt-1 w-full" type="text" name="negara_penempatan" :value="old('negara_penempatan')" />
                            <x-input-error :messages="$errors->get('negara_penempatan')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="provinsi_penempatan" :value="__('Provinsi Penempatan')" />
                            <x-text-input id="provinsi_penempatan" class="mt-1 w-full" type="text" name="provinsi_penempatan" :value="old('provinsi_penempatan')" />
                            <x-input-error :messages="$errors->get('provinsi_penempatan')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="kota_penempatan" :value="__('Kota Penempatan')" />
                            <x-text-input id="kota_penempatan" class="mt-1 w-full" type="text" name="kota_penempatan" :value="old('kota_penempatan')" />
                            <x-input-error :messages="$errors->get('kota_penempatan')" class="mt-2" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="panel-title">Kriteria Kandidat</div>
                        <div class="panel-desc mt-1">Opsional, isi untuk mempersempit kandidat.</div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <x-input-label for="kriteria_status_str" :value="__('Kriteria Status STR')" />
                                <select id="kriteria_status_str" name="kriteria[status_str]" class="select-field mt-1">
                                    <option value="">Bebas</option>
                                    <option value="aktif" @selected(old('kriteria.status_str') === 'aktif')>Aktif</option>
                                    <option value="hold" @selected(old('kriteria.status_str') === 'hold')>Ditahan</option>
                                    <option value="expired" @selected(old('kriteria.status_str') === 'expired')>Kedaluwarsa</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label for="kriteria_sertifikat" :value="__('Kriteria Sertifikat (contoh: BTCLS)')" />
                                <x-text-input id="kriteria_sertifikat" class="mt-1 w-full" type="text" name="kriteria[sertifikat]" :value="old('kriteria.sertifikat')" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tipeSelect = document.getElementById('tipe_penempatan');
            var negaraInput = document.getElementById('negara_penempatan');
            var labelNegara = document.querySelector('label[for="negara_penempatan"]');

            function toggleNegaraPenempatan() {
                if (!tipeSelect || !negaraInput) return;
                var val = tipeSelect.value;
                if (val === 'domestik') {
                    negaraInput.value = '';
                    negaraInput.disabled = true;
                    negaraInput.required = false;
                    negaraInput.placeholder = 'Indonesia (Otomatis)';
                    negaraInput.classList.add('bg-slate-100', 'cursor-not-allowed');
                    if (labelNegara) {
                        labelNegara.innerHTML = 'Negara Penempatan';
                    }
                } else {
                    negaraInput.disabled = false;
                    negaraInput.required = true;
                    negaraInput.placeholder = 'Masukkan negara tujuan';
                    negaraInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
                    if (labelNegara) {
                        labelNegara.innerHTML = 'Negara Penempatan <span class="text-rose-500">*</span>';
                    }
                }
            }

            if (tipeSelect && negaraInput) {
                tipeSelect.addEventListener('change', toggleNegaraPenempatan);
                toggleNegaraPenempatan();
            }
        });
    </script>
</x-app-layout>
