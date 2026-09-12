<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="form-title">Konfirmasi keamanan</h1>
            <p class="form-subtitle">Masukkan kata sandi Anda untuk melanjutkan.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="password" :value="__('Kata Sandi')" />

                <x-text-input id="password" class="mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-button>
                    {{ __('Konfirmasi') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
