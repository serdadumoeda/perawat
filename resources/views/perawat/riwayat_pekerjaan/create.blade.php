<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Tambah Riwayat Pekerjaan</h2>
            <p class="section-subtitle">Ajukan pengalaman kerja untuk diverifikasi oleh Mitra.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <form method="POST" action="{{ route('perawat.riwayat_pekerjaan.store') }}" class="space-y-4">
                    @csrf

                    @if ($mitra->isEmpty())
                        <div class="alert alert-warning">Data mitra belum tersedia. Hubungi admin.</div>
                    @endif

                    <div>
                        <x-input-label for="mitra_pengguna_id" :value="__('Mitra Pengguna')" />
                        <select id="mitra_pengguna_id" name="mitra_pengguna_id" class="select-field mt-2" required>
                            <option value="" disabled selected>Pilih mitra</option>
                            @foreach ($mitra as $item)
                                <option value="{{ $item->id }}" @selected(old('mitra_pengguna_id') === $item->id)>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('mitra_pengguna_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="posisi" :value="__('Posisi')" />
                        <x-text-input id="posisi" class="mt-2" type="text" name="posisi" :value="old('posisi')" required />
                        <x-input-error :messages="$errors->get('posisi')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="unit" :value="__('Unit')" />
                        <x-text-input id="unit" class="mt-2" type="text" name="unit" :value="old('unit')" />
                        <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_mulai" :value="__('Tanggal Mulai')" />
                            <x-text-input id="tanggal_mulai" class="mt-2" type="date" name="tanggal_mulai" :value="old('tanggal_mulai')" required />
                            <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_selesai" :value="__('Tanggal Selesai')" />
                            <x-text-input id="tanggal_selesai" class="mt-2" type="date" name="tanggal_selesai" :value="old('tanggal_selesai')" />
                            <x-input-error :messages="$errors->get('tanggal_selesai')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="status_pekerjaan" :value="__('Status Pekerjaan')" />
                        <select id="status_pekerjaan" name="status_pekerjaan" class="select-field mt-2" required>
                            <option value="aktif" @selected(old('status_pekerjaan') === 'aktif')>Aktif</option>
                            <option value="selesai" @selected(old('status_pekerjaan') === 'selesai')>Selesai</option>
                            <option value="resign" @selected(old('status_pekerjaan') === 'resign')>Resign</option>
                        </select>
                        <x-input-error :messages="$errors->get('status_pekerjaan')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="keterangan" :value="__('Keterangan (opsional)')" />
                        <textarea id="keterangan" name="keterangan" rows="3" class="input-field mt-2">{{ old('keterangan') }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Kirim untuk Verifikasi') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var statusSelect = document.getElementById('status_pekerjaan');
            var tanggalSelesaiInput = document.getElementById('tanggal_selesai');
            var labelTanggalSelesai = document.querySelector('label[for="tanggal_selesai"]');

            function toggleTanggalSelesai() {
                if (!statusSelect || !tanggalSelesaiInput) return;
                var val = statusSelect.value;
                if (val === 'aktif') {
                    tanggalSelesaiInput.value = '';
                    tanggalSelesaiInput.disabled = true;
                    tanggalSelesaiInput.required = false;
                    tanggalSelesaiInput.classList.add('bg-slate-100', 'cursor-not-allowed');
                    if (labelTanggalSelesai) {
                        labelTanggalSelesai.innerHTML = 'Tanggal Selesai';
                    }
                } else {
                    tanggalSelesaiInput.disabled = false;
                    tanggalSelesaiInput.required = true;
                    tanggalSelesaiInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
                    if (labelTanggalSelesai) {
                        labelTanggalSelesai.innerHTML = 'Tanggal Selesai <span class="text-rose-500">*</span>';
                    }
                }
            }

            if (statusSelect && tanggalSelesaiInput) {
                statusSelect.addEventListener('change', toggleTanggalSelesai);
                toggleTanggalSelesai();
            }
        });
    </script>
</x-app-layout>
