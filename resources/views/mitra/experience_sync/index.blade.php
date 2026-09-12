<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Pengaturan Sinkronisasi Pengalaman</h2>
            <p class="section-subtitle">Kelola integrasi data pengalaman kerja dari sistem Mitra/RS/Agency.</p>
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

            <div class="module-card card-surface p-4 sm:p-6 space-y-6">
                @if ($mitraList->isEmpty())
                    <div class="alert alert-warning">Akun ini belum terhubung ke mitra pengguna. Hubungi admin.</div>
                @else
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <div class="panel-title">Mitra Pengguna</div>
                            <div class="panel-desc">Pilih mitra yang akan dihubungkan ke sinkronisasi.</div>
                        </div>
                        <form method="GET" class="min-w-[220px]">
                            <x-input-label for="mitra_id" :value="__('Mitra Pengguna')" />
                            <select id="mitra_id" name="mitra_id" class="select-field mt-2" onchange="this.form.submit()">
                                @foreach ($mitraList as $mitra)
                                    <option value="{{ $mitra->id }}" @selected($selectedId === $mitra->id)>{{ $mitra->nama }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @if ($setting)
                        <form method="POST" action="{{ route('mitra.experience_sync.update') }}" class="space-y-6">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="mitra_pengguna_id" value="{{ $selectedId }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="base_url" :value="__('Base URL')" />
                                    <x-text-input id="base_url" class="mt-2" type="text" name="base_url" :value="old('base_url', $setting->base_url)" />
                                </div>
                                <div>
                                    <x-input-label for="api_key" :value="__('Kunci API')" />
                                    <x-text-input id="api_key" class="mt-2" type="text" name="api_key" :value="old('api_key', $setting->api_key)" />
                                </div>
                                <div>
                                    <x-input-label for="client_id" :value="__('Client ID')" />
                                    <x-text-input id="client_id" class="mt-2" type="text" name="client_id" :value="old('client_id', $setting->client_id)" />
                                </div>
                                <div>
                                    <x-input-label for="client_secret" :value="__('Client Secret')" />
                                    <x-text-input id="client_secret" class="mt-2" type="password" name="client_secret" :value="old('client_secret', $setting->client_secret)" />
                                </div>
                                <div>
                                    <x-input-label for="username" :value="__('Username')" />
                                    <x-text-input id="username" class="mt-2" type="text" name="username" :value="old('username', $setting->username)" />
                                </div>
                                <div>
                                    <x-input-label for="password" :value="__('Kata Sandi')" />
                                    <x-text-input id="password" class="mt-2" type="password" name="password" :value="old('password', $setting->password)" />
                                </div>
                                <div>
                                    <x-input-label for="sync_mode" :value="__('Mode Sinkronisasi')" />
                                    <select id="sync_mode" name="sync_mode" class="select-field mt-2">
                                        <option value="pull" @selected(old('sync_mode', $setting->sync_mode) === 'pull')>Pull (ambil data)</option>
                                        <option value="push" @selected(old('sync_mode', $setting->sync_mode) === 'push')>Push (kirim data)</option>
                                        <option value="both" @selected(old('sync_mode', $setting->sync_mode) === 'both')>Dua arah</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="is_active" :value="__('Aktifkan Sinkronisasi')" />
                                    <div class="mt-2 flex items-center gap-2">
                                        <input id="is_active" type="checkbox" name="is_active" value="1" class="checkbox-field" @checked(old('is_active', $setting->is_active)) />
                                        <label for="is_active" class="text-sm text-slate-600">Aktif</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="notes" :value="__('Catatan / Informasi Integrasi')" />
                                <textarea id="notes" name="notes" rows="3" class="input-field mt-2">{{ old('notes', $setting->notes) }}</textarea>
                                <div class="form-help mt-2">Catatan internal untuk tim integrasi.</div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
                                <div>
                                    Terakhir sinkronisasi: {{ $setting->last_synced_at?->format('d M Y H:i') ?? '-' }}
                                </div>
                                <a class="btn-ghost" href="{{ route('mitra.experience_sync.logs', ['mitra_id' => $selectedId]) }}">Lihat log sinkronisasi</a>
                            </div>

                            <div class="flex justify-end">
                                <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
