<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Input Legalitas & STR</h2>
            <p class="section-subtitle">Catat status STR dan izin luar negeri perawat.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                @if (session('status'))
                    <div class="alert alert-success mb-4">{{ session('status') }}</div>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <div class="panel-title">Form Legalitas</div>
                        <div class="panel-desc">Pastikan NIK sesuai data kampus.</div>
                    </div>
                    <a class="btn-secondary" href="{{ route('regulator.legalitas.import') }}">Impor Legalitas (CSV)</a>
                </div>

                <form method="POST" action="{{ route('regulator.legalitas.upsert') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="nik" :value="__('NIK Perawat')" />
                        <x-text-input id="nik" class="mt-1 w-full" type="text" name="nik" :value="old('nik')" required />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nomor_str" :value="__('Nomor STR')" />
                            <x-text-input id="nomor_str" class="mt-1 w-full" type="text" name="nomor_str" :value="old('nomor_str')" />
                            <x-input-error :messages="$errors->get('nomor_str')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nomor_sipp" :value="__('Nomor SIPP')" />
                            <x-text-input id="nomor_sipp" class="mt-1 w-full" type="text" name="nomor_sipp" :value="old('nomor_sipp')" />
                            <x-input-error :messages="$errors->get('nomor_sipp')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="status_str" :value="__('Status STR')" />
                        <select id="status_str" name="status_str" class="select-field mt-1">
                            <option value="" disabled selected>Pilih status</option>
                            <option value="aktif">Aktif</option>
                            <option value="hold">Ditahan</option>
                            <option value="expired">Kedaluwarsa</option>
                        </select>
                        <x-input-error :messages="$errors->get('status_str')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="str_berlaku_mulai" :value="__('STR Berlaku Mulai')" />
                            <x-text-input id="str_berlaku_mulai" class="mt-1 w-full" type="date" name="str_berlaku_mulai" :value="old('str_berlaku_mulai')" />
                        </div>
                        <div>
                            <x-input-label for="str_berlaku_sampai" :value="__('STR Berlaku Sampai')" />
                            <x-text-input id="str_berlaku_sampai" class="mt-1 w-full" type="date" name="str_berlaku_sampai" :value="old('str_berlaku_sampai')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nomor_izin_luar_negeri" :value="__('Nomor Izin Luar Negeri')" />
                            <x-text-input id="nomor_izin_luar_negeri" class="mt-1 w-full" type="text" name="nomor_izin_luar_negeri" :value="old('nomor_izin_luar_negeri')" />
                        </div>
                        <div>
                            <x-input-label for="status_izin_luar_negeri" :value="__('Status Izin Luar Negeri')" />
                            <x-text-input id="status_izin_luar_negeri" class="mt-1 w-full" type="text" name="status_izin_luar_negeri" :value="old('status_izin_luar_negeri')" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
