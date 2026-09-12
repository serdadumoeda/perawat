<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="form-title">Buat password baru</h1>
            <p class="form-subtitle">Pastikan kombinasi yang kuat dan mudah diingat.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Kata Sandi')" />
                <x-text-input id="password" class="mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
                <x-text-input id="password_confirmation" class="mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a class="link-muted" href="{{ route('login') }}">Kembali ke login</a>
                <x-primary-button>
                    {{ __('Atur Ulang Kata Sandi') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
