<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Platform Keperawatan') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="background:#f4efe6; color:#2c3732; margin:0; padding:0;">
        <div style="min-height:100vh; display:flex; flex-direction:column;">

            <!-- Navbar -->
            <header style="background:linear-gradient(135deg, #143a30 0%, #1e483b 100%); border-bottom:1px solid rgba(255,255,255,0.12); position:sticky; top:0; z-index:50; box-shadow:0 8px 32px rgba(15,41,34,0.2);">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="display:flex; align-items:center; justify-content:space-between; height:4.25rem; gap:1rem;">
                    <a href="/" style="display:flex; align-items:center; gap:0.625rem; text-decoration:none;">
                        <div style="width:2.35rem; height:2.35rem; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:0.75rem; display:flex; align-items:center; justify-content:center;">
                            <x-application-logo style="width:1.35rem; height:1.35rem; color:#e5b895;" />
                        </div>
                        <div class="hidden sm:block">
                            <div style="font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; color:#ffffff; line-height:1.1;">Platform Keperawatan</div>
                            <div style="font-size:0.62rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:rgba(229,184,149,0.9);">Portal Publik</div>
                        </div>
                    </a>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <a href="{{ route('login') }}" class="btn-outline-white" style="font-size:0.75rem; padding:0.5rem 1.35rem;">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary" style="font-size:0.75rem; padding:0.55rem 1.35rem;">Daftar</a>
                    </div>
                </div>
            </header>

            @isset($header)
                <div style="background:linear-gradient(180deg, #eae2d3 0%, #f4efe6 100%); border-bottom:1px solid #e3d1b0; padding:1.75rem 0;">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main style="flex:1; padding-bottom:3.5rem;">
                {{ $slot }}
            </main>

            <footer style="background:#143a30; padding:2.25rem 0; text-align:center; color:rgba(255,255,255,0.5);">
                <div style="font-size:0.75rem; letter-spacing:0.06em;">
                    Platform Keperawatan &copy; {{ date('Y') }} &middot; KADIN Partner Ecosystem
                </div>
            </footer>
        </div>
    </body>
</html>
