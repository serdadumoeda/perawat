<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Pemetaan Pengguna ke Organisasi</h2>
            <p class="section-subtitle">Hubungkan akun user dengan institusi/lembaga/mitra/regulator.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                @if (session('status'))
                    <div class="alert alert-success mb-4">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.mapping.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="user_id" :value="__('User')" />
                        <select id="user_id" name="user_id" class="select-field mt-2" required>
                            <option value="" disabled selected>Pilih user</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="organisasi_type" :value="__('Tipe Organisasi')" />
                        <select id="organisasi_type" name="organisasi_type" class="select-field mt-2" required>
                            <option value="institusi">Institusi Pendidikan</option>
                            <option value="lembaga">Lembaga Pelatihan</option>
                            <option value="mitra">Mitra Pengguna</option>
                            <option value="regulator">Organisasi Regulator</option>
                        </select>
                        <x-input-error :messages="$errors->get('organisasi_type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="organisasi_id" :value="__('Organisasi')" />
                        <select id="organisasi_id" name="organisasi_id" class="select-field mt-2" required>
                            <optgroup label="Institusi Pendidikan">
                                @foreach ($institusi as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Lembaga Pelatihan">
                                @foreach ($lembaga as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Mitra Pengguna">
                                @foreach ($mitra as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Organisasi Regulator">
                                @foreach ($regulatorOrganisasi as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        <x-input-error :messages="$errors->get('organisasi_id')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
