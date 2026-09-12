<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Input Riwayat Pekerjaan</h2>
            <p class="section-subtitle">Tambahkan pengalaman kerja perawat dari sisi Mitra.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <form method="POST" action="{{ route('mitra.riwayat_pekerjaan.store') }}" class="space-y-4">
                    @csrf

                    @if ($mitra->isEmpty())
                        <div class="alert alert-warning">Akun belum terhubung dengan mitra pengguna. Hubungi admin.</div>
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
                        <x-input-label for="nik" :value="__('NIK Perawat')" />
                        <x-text-input id="nik" class="mt-2" type="text" name="nik" :value="old('nik')" required />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="posisi" :value="__('Posisi')" />
                            <x-text-input id="posisi" class="mt-2" type="text" name="posisi" :value="old('posisi')" />
                        </div>
                        <div>
                            <x-input-label for="unit" :value="__('Unit')" />
                            <x-text-input id="unit" class="mt-2" type="text" name="unit" :value="old('unit')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_mulai" :value="__('Tanggal Mulai')" />
                            <x-text-input id="tanggal_mulai" class="mt-2" type="date" name="tanggal_mulai" :value="old('tanggal_mulai')" required />
                        </div>
                        <div>
                            <x-input-label for="tanggal_selesai" :value="__('Tanggal Selesai')" />
                            <x-text-input id="tanggal_selesai" class="mt-2" type="date" name="tanggal_selesai" :value="old('tanggal_selesai')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="status_pekerjaan" :value="__('Status Pekerjaan')" />
                        <select id="status_pekerjaan" name="status_pekerjaan" class="select-field mt-2">
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                            <option value="resign">Resign</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="penilaian_kinerja" :value="__('Penilaian Kinerja')" />
                        <x-text-input id="penilaian_kinerja" class="mt-2" type="text" name="penilaian_kinerja" :value="old('penilaian_kinerja')" />
                    </div>

                    <div>
                        <x-input-label for="keterangan" :value="__('Keterangan (opsional)')" />
                        <textarea id="keterangan" name="keterangan" rows="3" class="input-field mt-2">{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Status verifikasi akan otomatis diset ke <span class="font-semibold">Menunggu</span> dan harus direview oleh mitra melalui menu review.
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
