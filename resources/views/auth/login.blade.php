<x-guest-layout>
    <div style="space:1rem;">
        <!-- Heading -->
        <div style="margin-bottom:1.5rem;">
            <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.5rem;">Platform Keperawatan</div>
            <h1 style="font-family:'Playfair Display',serif; font-size:1.75rem; font-weight:700; color:#1a1a1a; margin:0 0 0.375rem; line-height:1.2;">Selamat datang kembali</h1>
            <p style="font-size:0.875rem; color:#9a9a9a; margin:0;">Masuk untuk melihat profil terverifikasi dan aktivitas rekrutmen terbaru.</p>
        </div>

        <!-- Feature chips -->
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:0.75rem; margin-bottom:1.5rem;">
            @foreach([
                ['icon'=>'M13 10V3L4 14h7v7l9-11h-7z','label'=>'Profil Hidup','sub'=>'Selalu terbarui','c'=>'#e05252','bg'=>'rgba(224,82,82,0.08)'],
                ['icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','label'=>'Terverifikasi','sub'=>'Audit trail aman','c'=>'#0f5050','bg'=>'rgba(15,80,80,0.08)'],
                ['icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','label'=>'Hiring Cepat','sub'=>'Proses efisien','c'=>'#d97706','bg'=>'rgba(217,119,6,0.08)'],
            ] as $feat)
                <div style="padding:0.875rem 0.75rem; border-radius:0.875rem; border:1px solid #f0e5cf; background:{{ $feat['bg'] }}; text-align:center;">
                    <div style="width:2rem; height:2rem; border-radius:0.5rem; background:rgba(255,255,255,0.8); display:flex; align-items:center; justify-content:center; margin:0 auto 0.5rem;">
                        <svg style="width:1rem; height:1rem; color:{{ $feat['c'] }};" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feat['icon'] }}"/>
                        </svg>
                    </div>
                    <div style="font-size:0.7rem; font-weight:700; color:#1a1a1a;">{{ $feat['label'] }}</div>
                    <div style="font-size:0.65rem; color:#9a9a9a; margin-top:0.125rem;">{{ $feat['sub'] }}</div>
                </div>
            @endforeach
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" style="display:flex; flex-direction:column; gap:1rem;">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" style="margin-top:0.375rem; width:100%;" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Kata Sandi')" />
                <x-text-input id="password" style="margin-top:0.375rem; width:100%;" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:0.75rem;">
                <label for="remember_me" style="display:inline-flex; align-items:center; gap:0.5rem; font-size:0.875rem; color:#5a5a5a; cursor:pointer;">
                    <input id="remember_me" type="checkbox" class="checkbox-field" name="remember">
                    <span>{{ __('Ingat saya') }}</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="link-muted" href="{{ route('password.request') }}" style="font-size:0.875rem;">{{ __('Lupa kata sandi?') }}</a>
                @endif
            </div>

            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:0.875rem; margin-top:0.25rem;">
                <p style="font-size:0.8rem; color:#9a9a9a; margin:0;">Belum punya akun? <a href="{{ route('register') }}" style="color:#e05252; font-weight:600; text-decoration:none;">Daftar sekarang</a></p>
                <x-primary-button>{{ __('Masuk') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
