<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="form-title">Reset akses akun</h1>
            <p class="form-subtitle">Masukkan email terdaftar, kami kirim tautan pemulihan.</p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a class="link-muted" href="{{ route('login') }}">Kembali ke login</a>
                <x-primary-button>
                    {{ __('Kirim tautan reset') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
