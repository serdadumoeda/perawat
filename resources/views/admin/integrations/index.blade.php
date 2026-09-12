<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Integrasi API (Kemenkes/KTKI)</h2>
                <p class="section-subtitle">Kelola kredensial dan status integrasi resmi.</p>
            </div>
            <a href="{{ route('admin.integration_playground.index') }}" class="btn-secondary">Playground Integrasi</a>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @foreach (['kemenkes' => 'Kemenkes', 'ktki' => 'KTKI'] as $provider => $label)
                @php($setting = $settings[$provider])
                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
                        <div class="text-lg font-semibold text-slate-900">{{ $label }}</div>
                        <span class="badge {{ $setting->is_active ? 'badge-success' : 'badge-warning' }}">
                            {{ $setting->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.integrations.update', $provider) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="provider" value="{{ $provider }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="base_url_{{ $provider }}" :value="__('Base URL')" />
                                <x-text-input id="base_url_{{ $provider }}" class="mt-1 w-full" type="text" name="base_url" :value="old('base_url', $setting->base_url)" />
                            </div>
                            <div>
                                <x-input-label for="api_key_{{ $provider }}" :value="__('Kunci API')" />
                                <x-text-input id="api_key_{{ $provider }}" class="mt-1 w-full" type="text" name="api_key" :value="old('api_key', $setting->api_key)" />
                            </div>
                            <div>
                                <x-input-label for="client_id_{{ $provider }}" :value="__('Client ID')" />
                                <x-text-input id="client_id_{{ $provider }}" class="mt-1 w-full" type="text" name="client_id" :value="old('client_id', $setting->client_id)" />
                            </div>
                            <div>
                                <x-input-label for="client_secret_{{ $provider }}" :value="__('Client Secret')" />
                                <x-text-input id="client_secret_{{ $provider }}" class="mt-1 w-full" type="password" name="client_secret" :value="old('client_secret', $setting->client_secret)" />
                            </div>
                            <div>
                                <x-input-label for="username_{{ $provider }}" :value="__('Username')" />
                                <x-text-input id="username_{{ $provider }}" class="mt-1 w-full" type="text" name="username" :value="old('username', $setting->username)" />
                            </div>
                            <div>
                                <x-input-label for="password_{{ $provider }}" :value="__('Kata Sandi')" />
                                <x-text-input id="password_{{ $provider }}" class="mt-1 w-full" type="password" name="password" :value="old('password', $setting->password)" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="notes_{{ $provider }}" :value="__('Catatan / Informasi MOU')" />
                            <textarea id="notes_{{ $provider }}" name="notes" rows="3" class="input-field mt-1">{{ old('notes', $setting->notes) }}</textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="is_active_{{ $provider }}" type="checkbox" name="is_active" value="1" class="checkbox-field" @checked(old('is_active', $setting->is_active)) />
                            <label for="is_active_{{ $provider }}" class="text-sm text-slate-700">Aktifkan integrasi (belum ada MOU = nonaktif)</label>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>Simpan</x-primary-button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
