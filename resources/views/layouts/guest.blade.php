<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Platform Keperawatan') }}</title>
        <meta name="description" content="Platform Keperawatan KADIN — Masuk ke ekosistem talent perawat Indonesia">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="background-color:#f4efe6; color:#2c3732; min-height:100vh;">
        <div style="min-height:100vh; display:grid;" class="lg:grid-cols-[1.1fr,0.9fr]">

            {{-- ===== LEFT: Botanical Forest Dark Panel ===== --}}
            <aside class="hidden lg:flex lg:flex-col lg:justify-between" style="background:linear-gradient(160deg, #0f2922 0%, #143a30 50%, #1e483b 100%); padding:3.5rem; position:relative; overflow:hidden;">

                <!-- Subtle pattern overlay -->
                <div style="position:absolute; inset:0; opacity:0.05; pointer-events:none;" aria-hidden="true">
                    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="leaf-dots" x="0" y="0" width="32" height="32" patternUnits="userSpaceOnUse">
                                <circle cx="4" cy="4" r="1.5" fill="#e5b895"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#leaf-dots)"/>
                    </svg>
                </div>

                <!-- Gold & Sage accent circle decorations -->
                <div style="position:absolute; top:-5rem; right:-5rem; width:22rem; height:22rem; border-radius:9999px; background:radial-gradient(circle, rgba(229,184,149,0.18), transparent 70%); pointer-events:none;"></div>
                <div style="position:absolute; bottom:-4rem; left:-3rem; width:18rem; height:18rem; border-radius:9999px; background:radial-gradient(circle, rgba(85,122,88,0.22), transparent 70%); pointer-events:none;"></div>

                <!-- Top: logo -->
                <div style="position:relative; z-index:1;">
                    <a href="/" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none;">
                        <div style="width:2.75rem; height:2.75rem; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:0.75rem; display:flex; align-items:center; justify-content:center;">
                            <x-application-logo style="width:1.5rem; height:1.5rem; color:#e5b895;" />
                        </div>
                        <div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#ffffff; line-height:1.2;">Platform Keperawatan</div>
                            <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:rgba(229,184,149,0.85);">Talent OS &middot; KADIN</div>
                        </div>
                    </a>
                </div>

                <!-- Middle: hero copy -->
                <div style="position:relative; z-index:1;">
                    <!-- Small label -->
                    <div style="display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem; padding:0.35rem 1rem; border-radius:9999px; border:1px solid rgba(229,184,149,0.35); background:rgba(229,184,149,0.12); font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#ebd3c2;">
                        <span style="width:0.4rem; height:0.4rem; border-radius:9999px; background:#d8a47f; animation:pulse 2s infinite;"></span>
                        Smart Hiring Workspace
                    </div>

                    <!-- Heading -->
                    <h1 style="font-family:'Playfair Display',serif; font-size:2.75rem; font-weight:700; color:#ffffff; line-height:1.15; margin:0 0 1.25rem;">
                        Rekrutmen Perawat yang<br>
                        <em style="font-style:italic; color:#e5b895;">Cepat, Terverifikasi &amp; Presisi.</em>
                    </h1>

                    <p style="font-size:0.9375rem; line-height:1.75; color:rgba(255,255,255,0.72); max-width:26rem; margin:0 0 2rem;">
                        Data legalitas, sertifikasi, pengalaman, dan performa perawat dipusatkan dalam satu alur kolaboratif — audit-ready dan real-time.
                    </p>

                    <!-- Stats grid -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.875rem;">
                        @foreach([
                            ['val' => '+10K', 'label' => 'Profil terverifikasi'],
                            ['val' => '3.2×', 'label' => 'Proses lebih cepat'],
                            ['val' => 'Real-time', 'label' => 'Sinkronisasi data'],
                            ['val' => 'Audit-ready', 'label' => 'Jejak keputusan'],
                        ] as $stat)
                        <div style="padding:1rem; border-radius:1rem; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.14); transition:border-color 0.2s;">
                            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#ebd3c2; line-height:1.2;">{{ $stat['val'] }}</div>
                            <div style="font-size:0.75rem; color:rgba(255,255,255,0.6); margin-top:0.25rem;">{{ $stat['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Bottom: copyright -->
                <div style="position:relative; z-index:1; font-size:0.7rem; color:rgba(255,255,255,0.4); letter-spacing:0.04em;">
                    Platform Keperawatan &copy; {{ date('Y') }} &middot; KADIN Partner Ecosystem
                </div>
            </aside>

            {{-- ===== RIGHT: Warm Sand Form Panel ===== --}}
            <div style="display:flex; align-items:center; justify-content:center; padding:2.5rem 1.5rem; background:#f4efe6;">
                <div style="width:100%; max-width:26rem;">

                    <!-- Mobile logo -->
                    <div class="lg:hidden" style="display:flex; align-items:center; gap:0.75rem; margin-bottom:2rem;">
                        <div style="width:2.5rem; height:2.5rem; background:#143a30; border-radius:0.625rem; display:flex; align-items:center; justify-content:center;">
                            <x-application-logo style="width:1.375rem; height:1.375rem; color:#e5b895;" />
                        </div>
                        <div>
                            <div style="font-family:'Playfair Display',serif; font-size:1rem; font-weight:700; color:#1c2621;">Platform Keperawatan</div>
                            <div style="font-size:0.65rem; color:#8c9b92; text-transform:uppercase; letter-spacing:0.1em; font-weight:600;">Talent OS &middot; KADIN</div>
                        </div>
                    </div>

                    <!-- Form card -->
                    <div class="fade-up" style="background:#ffffff; border:1px solid #e3d1b0; border-radius:1.75rem; padding:2.25rem 2rem; box-shadow:0 16px 48px rgba(20,58,48,0.08);">
                        {{ $slot }}
                    </div>

                    <div style="margin-top:1.5rem; text-align:center; font-size:0.75rem; color:#8c9b92;">
                        Butuh bantuan? Hubungi administrator platform Anda.
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
