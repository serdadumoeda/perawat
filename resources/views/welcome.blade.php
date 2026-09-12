<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Platform Keperawatan') }} — Talent OS KADIN</title>
        <meta name="description" content="Platform Keperawatan KADIN — Rekrutmen perawat cepat, terverifikasi, dan penuh insight.">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="background:#f4efe6; color:#2c3732; margin:0; padding:0; overflow-x:hidden;">

        {{-- ===== TOP NAVIGATION ===== --}}
        <header style="background:linear-gradient(135deg, #143a30 0%, #1e483b 100%); border-bottom:1px solid rgba(255,255,255,0.12); position:sticky; top:0; z-index:50; box-shadow:0 8px 32px rgba(15,41,34,0.2);">
            <div class="max-w-7xl mx-auto px-6 lg:px-8" style="display:flex; align-items:center; justify-content:space-between; height:4.25rem;">
                <!-- Logo -->
                <a href="/" style="display:flex; align-items:center; gap:0.625rem; text-decoration:none;">
                    <div style="width:2.35rem; height:2.35rem; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:0.75rem; display:flex; align-items:center; justify-content:center;">
                        <x-application-logo style="width:1.35rem; height:1.35rem; color:#e5b895;" />
                    </div>
                    <div>
                        <div style="font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; color:#ffffff; line-height:1.1;">Platform Keperawatan</div>
                        <div style="font-size:0.62rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:rgba(229,184,149,0.9);">Talent OS &middot; KADIN</div>
                    </div>
                </a>

                <!-- Center nav links -->
                <nav class="hidden lg:flex items-center gap-6">
                    @foreach (['Beranda'=>'#hero','Layanan'=>'#services','Ekosistem'=>'#ecosystem','Alur'=>'#process','Fitur'=>'#features','Gabung'=>'#cta'] as $label => $href)
                        <a href="{{ $href }}" style="font-size:0.7rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:rgba(255,255,255,0.85); text-decoration:none; transition:color 0.18s;" onmouseover="this.style.color='#e5b895'" onmouseout="this.style.color='rgba(255,255,255,0.85)'">{{ $label }}</a>
                    @endforeach
                </nav>

                <!-- CTAs -->
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <a href="{{ route('login') }}" class="btn-outline-white" style="font-size:0.75rem; padding:0.45rem 1.25rem;">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary" style="font-size:0.75rem; padding:0.55rem 1.4rem;">Daftar Sekarang &rarr;</a>
                </div>
            </div>
        </header>

        <main style="margin:0; padding:0;">

            {{-- ============================================================ --}}
            {{-- 1. HERO SECTION (Dark Green + Botanical Nurse Image)         --}}
            {{-- ============================================================ --}}
            <section id="hero" style="background:linear-gradient(160deg, #0f2922 0%, #143a30 40%, #1e483b 100%); position:relative; overflow:hidden; min-height:85vh; display:flex; align-items:center;">

                {{-- Hero image - nurse with botanical overlay --}}
                <div style="position:absolute; top:-5%; right:-5%; width:60%; height:110%; pointer-events:none; z-index:1;">
                    <img src="/images/hero_nursing_botanical.png" alt="" style="width:100%; height:100%; object-fit:cover; opacity:0.7; filter:brightness(0.85);">
                </div>

                {{-- Gradient overlay for text readability --}}
                <div style="position:absolute; inset:0; background:linear-gradient(90deg, rgba(15,41,34,0.95) 0%, rgba(15,41,34,0.7) 40%, rgba(15,41,34,0.2) 70%, transparent 100%); z-index:2;"></div>

                <div class="max-w-7xl mx-auto px-6 lg:px-8" style="position:relative; z-index:3; width:100%; padding-top:4rem; padding-bottom:5rem;">
                    <div style="max-width:38rem;">

                        <h1 style="font-family:'Playfair Display',serif; font-size:clamp(3rem, 6vw, 4.75rem); font-weight:700; color:#f4efe6; line-height:1.08; margin:0 0 1.75rem;" class="fade-up">
                            Rekrutmen<br>Perawat<br>
                            <em style="font-style:italic; color:#e5b895;">Terverifikasi.</em>
                        </h1>

                        <p style="font-size:1.0625rem; line-height:1.85; color:rgba(244,239,230,0.75); max-width:30rem; margin:0 0 2.5rem;" class="fade-up-delayed">
                            Platform Keperawatan menyatukan kampus, lembaga pelatihan, regulator, dan mitra rekrutmen dalam satu ekosistem digital — data terverifikasi, audit-ready, real-time.
                        </p>

                        <div style="display:flex; flex-wrap:wrap; gap:1rem;" class="fade-up-delayed">
                            <a href="{{ route('register') }}" style="display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; background:#e3d1b0; color:#2a1f18; font-size:0.8125rem; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; padding:0.85rem 2.25rem; border-radius:9999px; border:none; cursor:pointer; text-decoration:none; transition:all 0.22s ease; box-shadow:0 6px 20px rgba(227,209,176,0.3);" onmouseover="this.style.background='#d8a47f';this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 28px rgba(216,164,127,0.4)';" onmouseout="this.style.background='#e3d1b0';this.style.transform='translateY(0)';this.style.boxShadow='0 6px 20px rgba(227,209,176,0.3)';">
                                Mulai Sekarang &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- 2. FEATURE PANEL (Teal Rounded — 4 Peran Utama)             --}}
            {{-- ============================================================ --}}
            <section id="services" style="background:#f4efe6; margin:0; padding:0; position:relative;">
                <div class="max-w-7xl mx-auto px-6 lg:px-8" style="padding-top:0; padding-bottom:0; position:relative; z-index:5;">
                    {{-- Pull up over the hero --}}
                    <div style="margin-top:-4rem; background:linear-gradient(145deg, #1a5c4e 0%, #1e7a6a 30%, #237a6d 70%, #1a5c4e 100%); border-radius:2rem; padding:2.5rem 2rem; box-shadow:0 24px 64px rgba(20,58,48,0.2); position:relative; overflow:hidden;">

                        {{-- Subtle pattern overlay --}}
                        <div style="position:absolute; inset:0; opacity:0.06; background-image:url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 400 200\"><path d=\"M0,100 Q50,50 100,100 T200,100 T300,100 T400,100\" stroke=\"white\" fill=\"none\" stroke-width=\"2\"/></svg>'); background-size:400px 200px; pointer-events:none;"></div>

                        <div style="display:grid; gap:2rem; position:relative; z-index:1;" class="lg:grid-cols-4 sm:grid-cols-2">
                            @foreach([
                                ['icon'=>'<svg style="width:1.5rem; height:1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>','title'=>'Kampus','desc'=>'Input data lulusan keperawatan, impor batch CSV, lacak alumni, dan analitik keterserapan kerja.'],
                                ['icon'=>'<svg style="width:1.5rem; height:1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>','title'=>'Regulator','desc'=>'Verifikasi STR/SIPP, review dokumen legalitas, sinkronisasi data, dan impor legalitas batch.'],
                                ['icon'=>'<svg style="width:1.5rem; height:1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>','title'=>'Lembaga Pelatihan','desc'=>'Kelola pelatihan kompetensi, input sertifikasi BNSP, verifikasi sertifikat, dan impor batch.'],
                                ['icon'=>'<svg style="width:1.5rem; height:1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>','title'=>'Mitra & Faskes','desc'=>'Bank kandidat terverifikasi, publikasi lowongan, kelola lamaran, dan evaluasi riwayat kinerja.'],
                            ] as $svc)
                                <div style="text-align:left;">
                                    <div style="width:3rem; height:3rem; border-radius:9999px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; margin-bottom:1rem; color:#ffffff;">
                                        {!! $svc['icon'] !!}
                                    </div>
                                    <div style="font-family:'Playfair Display',serif; font-size:1.0625rem; font-weight:700; color:#ffffff; margin-bottom:0.625rem;">{{ $svc['title'] }}</div>
                                    <p style="font-size:0.8125rem; color:rgba(255,255,255,0.7); line-height:1.7; margin:0;">{{ $svc['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- 3. CARD GRID (3×2 — Fitur Utama Aplikasi)                   --}}
            {{-- ============================================================ --}}
            <section id="ecosystem" style="background:#f4efe6; margin:0; padding:0; position:relative;">
                <div class="max-w-7xl mx-auto px-6 lg:px-8" style="padding-top:3.5rem; padding-bottom:4rem;">

                    <div style="display:grid; gap:1.25rem;" class="lg:grid-cols-3 sm:grid-cols-2">

                        {{-- Card 1: Tim Perawat Image --}}
                        <div style="border-radius:1.75rem; overflow:hidden; position:relative; aspect-ratio:1/1; box-shadow:0 12px 36px rgba(20,58,48,0.12);">
                            <img src="/images/card_nursing_team.png" alt="Tim Perawat Profesional" style="width:100%; height:100%; object-fit:cover; display:block;">
                            <div style="position:absolute; inset:0; background:linear-gradient(180deg, transparent 40%, rgba(15,41,34,0.6) 100%);"></div>
                            <div style="position:absolute; bottom:1.5rem; left:1.5rem; right:1.5rem; z-index:2;">
                                <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#ffffff;">Ekosistem Perawat Indonesia</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.25rem;">Multi-stakeholder dalam satu platform</div>
                            </div>
                        </div>

                        {{-- Card 2: Sage Green — Verifikasi STR --}}
                        <div style="background:linear-gradient(145deg, #6b9a6e 0%, #557a58 100%); border-radius:1.75rem; padding:2rem; color:#ffffff; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; aspect-ratio:1/1; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 40px rgba(85,122,88,0.3)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                            <div style="width:3.5rem; height:3.5rem; border-radius:9999px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                                <svg style="width:1.5rem; height:1.5rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Verifikasi STR</div>
                            <p style="font-size:0.8rem; color:rgba(255,255,255,0.8); line-height:1.6; margin:0;">Sinkronisasi dan validasi Surat Tanda Registrasi perawat secara otomatis dari database regulator.</p>
                        </div>

                        {{-- Card 3: Deep Teal — Bank Kandidat --}}
                        <div style="background:linear-gradient(145deg, #1a5c4e 0%, #143a30 100%); border-radius:1.75rem; padding:2rem; color:#ffffff; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; aspect-ratio:1/1; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 40px rgba(20,58,48,0.3)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                            <div style="width:3.5rem; height:3.5rem; border-radius:9999px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                                <svg style="width:1.5rem; height:1.5rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Bank Kandidat</div>
                            <p style="font-size:0.8rem; color:rgba(255,255,255,0.75); line-height:1.6; margin:0;">Filter kandidat perawat berdasarkan kompetensi, lokasi, sertifikasi, dan pengalaman kerja terverifikasi.</p>
                        </div>

                        {{-- Card 4: Mocha — Sertifikasi & Pelatihan --}}
                        <div style="background:linear-gradient(145deg, #7a5544 0%, #573d33 100%); border-radius:1.75rem; padding:2rem; color:#ffffff; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; aspect-ratio:1/1; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 40px rgba(87,61,51,0.3)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                            <div style="width:3.5rem; height:3.5rem; border-radius:9999px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                                <svg style="width:1.5rem; height:1.5rem; color:#ebd3c2;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Sertifikasi & Pelatihan</div>
                            <p style="font-size:0.8rem; color:rgba(255,255,255,0.75); line-height:1.6; margin:0;">Kelola pelatihan kompetensi, input sertifikasi BNSP, dan verifikasi otomatis dari lembaga resmi.</p>
                        </div>

                        {{-- Card 5: Sand — Tracing Alumni --}}
                        <div style="background:linear-gradient(145deg, #e3d1b0 0%, #d8c4a0 100%); border-radius:1.75rem; padding:2rem; color:#2c3732; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; aspect-ratio:1/1; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 40px rgba(227,209,176,0.5)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                            <div style="width:3.5rem; height:3.5rem; border-radius:9999px; background:rgba(72,52,43,0.15); display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                                <svg style="width:1.5rem; height:1.5rem; color:#573d33;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6m4 0V9a2 2 0 00-2-2h-2a2 2 0 00-2 2v10M5 19v-4a2 2 0 012-2h2a2 2 0 012 2v4"/></svg>
                            </div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Tracing Alumni</div>
                            <p style="font-size:0.8rem; color:rgba(44,55,50,0.7); line-height:1.6; margin:0;">Pantau status keterserapan kerja lulusan, analitik forecasting supply & demand perawat.</p>
                        </div>

                        {{-- Card 6: Dark Chocolate — Lowongan & Lamaran --}}
                        <div style="background:linear-gradient(145deg, #5a3f32 0%, #48342b 100%); border-radius:1.75rem; padding:2rem; color:#ffffff; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; aspect-ratio:1/1; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 40px rgba(72,52,43,0.3)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                            <div style="width:3.5rem; height:3.5rem; border-radius:9999px; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                                <svg style="width:1.5rem; height:1.5rem; color:#ebd3c2;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; margin-bottom:0.5rem;">Lowongan & Lamaran</div>
                            <p style="font-size:0.8rem; color:rgba(255,255,255,0.7); line-height:1.6; margin:0;">Publikasi lowongan, kelola lamaran masuk, dan pantau status rekrutmen end-to-end.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- 4. ALUR PROSES PLATFORM                                      --}}
            {{-- ============================================================ --}}
            <section id="process" style="background:#f4efe6; margin:0; padding:0; position:relative;">
                <div class="max-w-7xl mx-auto px-6 lg:px-8" style="padding-top:1rem; padding-bottom:4rem;">
                    <div style="text-align:center; margin-bottom:3.5rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:#a16d49; margin-bottom:0.75rem;">Alur Proses</div>
                        <h2 style="font-family:'Playfair Display',serif; font-size:2.25rem; font-weight:700; color:#1c2621; margin:0;">Dari Pendaftaran hingga Rekrutmen</h2>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:2rem; position:relative;" class="sm:grid-cols-4 grid-cols-2">
                        <div class="hidden lg:block" style="position:absolute; top:2rem; left:12.5%; right:12.5%; height:1px; background:linear-gradient(90deg, #e3d1b0, #557a58, #e3d1b0); pointer-events:none;"></div>

                        @foreach([
                            ['n'=>'1','title'=>'Registrasi Data','desc'=>'Kampus input data lulusan, regulator validasi STR, lembaga catat sertifikasi.'],
                            ['n'=>'2','title'=>'Verifikasi','desc'=>'Dokumen legalitas, STR, dan sertifikat kompetensi diverifikasi secara digital.'],
                            ['n'=>'3','title'=>'Matching','desc'=>'Mitra & faskes filter kandidat terverifikasi, publikasi lowongan, dan terima lamaran.'],
                            ['n'=>'4','title'=>'Onboarding','desc'=>'Perawat diterima, riwayat pekerjaan dicatat, kinerja dievaluasi berkelanjutan.'],
                        ] as $p)
                            <div style="text-align:center; position:relative; z-index:1;">
                                <div style="width:4rem; height:4rem; border-radius:9999px; background:#ffffff; border:1.5px solid #e3d1b0; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; box-shadow:0 6px 20px rgba(20,58,48,0.06);">
                                    <span style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#557a58;">{{ $p['n'] }}</span>
                                </div>
                                <div style="font-size:0.875rem; font-weight:700; color:#1c2621; margin-bottom:0.5rem;">{{ $p['title'] }}</div>
                                <p style="font-size:0.8rem; color:#596960; line-height:1.6; margin:0;">{{ $p['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- WAVE: #f4efe6 -> #eae2d3 -->
                <div style="line-height:0; width:100%; margin:0; padding:0; overflow:hidden; background:#f4efe6;">
                    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" style="width:100%; height:70px; display:block; vertical-align:bottom;">
                        <path d="M0,50 C280,15 560,60 840,25 C1120,0 1320,45 1440,30 L1440,80 L0,80 Z" fill="#eae2d3"></path>
                    </svg>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- 5. FITUR UNGGULAN                                            --}}
            {{-- ============================================================ --}}
            <section id="features" style="background:#eae2d3; margin:0; padding:0; position:relative;">
                <div class="max-w-7xl mx-auto px-6 lg:px-8" style="padding-top:3rem; padding-bottom:4rem;">
                    <div style="display:grid; gap:4rem; align-items:center;" class="lg:grid-cols-2">

                        <!-- Left: Arched Photo -->
                        <div style="position:relative; display:flex; justify-content:center;">
                            <div style="width:100%; max-width:26rem; border-radius:180px 180px 24px 24px; overflow:hidden; border:6px solid #ffffff; box-shadow:0 20px 48px rgba(20,58,48,0.1); aspect-ratio:4/5;">
                                <img src="/images/card_nursing_team.png" alt="Tim Keperawatan" style="width:100%; height:100%; object-fit:cover; display:block;">
                            </div>
                        </div>

                        <!-- Right: Fitur Unggulan -->
                        <div>
                            <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:#a16d49; margin-bottom:0.875rem;">Fitur Unggulan</div>
                            <h2 style="font-family:'Playfair Display',serif; font-size:2.5rem; font-weight:700; color:#1c2621; line-height:1.2; margin:0 0 1.25rem;">
                                Satu Platform.<br>
                                <em style="font-style:italic; color:#557a58;">Seluruh Ekosistem.</em>
                            </h2>
                            <p style="font-size:0.9375rem; line-height:1.8; color:#596960; margin:0 0 2.5rem;">
                                Mengintegrasikan seluruh pemangku kepentingan keperawatan Indonesia dalam satu alur yang transparan, terverifikasi, dan audit-ready.
                            </p>

                            <div style="display:flex; flex-direction:column; gap:1.5rem;">
                                @foreach([
                                    ['n'=>'01','title'=>'Living CV & Data Terverifikasi','desc'=>'Profil perawat yang terus diperbarui — STR, sertifikasi, pengalaman kerja — semua tervalidasi dari sumber resmi.'],
                                    ['n'=>'02','title'=>'Audit Trail Otomatis','desc'=>'Setiap perubahan status lamaran, verifikasi dokumen, dan keputusan rekrutmen tercatat transparan.'],
                                    ['n'=>'03','title'=>'Forecasting & Analitik','desc'=>'Prediksi supply-demand perawat, analitik keterserapan alumni, dan insight rekrutmen berbasis data.'],
                                ] as $step)
                                    <div style="display:flex; gap:1.25rem; align-items:start;">
                                        <div style="width:2.5rem; height:2.5rem; border-radius:9999px; border:1.5px solid #e3d1b0; background:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 12px rgba(20,58,48,0.05);">
                                            <span style="font-family:'Playfair Display',serif; font-size:0.875rem; font-weight:700; color:#557a58;">{{ $step['n'] }}</span>
                                        </div>
                                        <div>
                                            <div style="font-size:0.9375rem; font-weight:700; color:#1c2621; margin-bottom:0.375rem;">{{ $step['title'] }}</div>
                                            <div style="font-size:0.8125rem; color:#596960; line-height:1.65;">{{ $step['desc'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WAVE: #eae2d3 -> #143a30 -->
                <div style="line-height:0; width:100%; margin:0; padding:0; overflow:hidden; background:#eae2d3;">
                    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" style="width:100%; height:70px; display:block; vertical-align:bottom;">
                        <path d="M0,30 C360,70 720,15 1080,55 C1280,75 1380,35 1440,45 L1440,80 L0,80 Z" fill="#143a30"></path>
                    </svg>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- 6. CTA SECTION                                               --}}
            {{-- ============================================================ --}}
            <section id="cta" style="background:#143a30; color:#ffffff; margin:0; padding:0; position:relative;">
                <div class="max-w-7xl mx-auto px-6 lg:px-8" style="padding-top:4rem; padding-bottom:5rem;">
                    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:2rem;">
                        <div>
                            <h2 style="font-family:'Playfair Display',serif; font-size:2.35rem; font-weight:700; color:#ffffff; margin:0 0 0.5rem; line-height:1.25;">Siap bergabung dengan ekosistem keperawatan?</h2>
                            <p style="font-size:0.9375rem; color:rgba(255,255,255,0.75); margin:0;">Daftar sebagai kampus, lembaga, regulator, mitra, atau perawat — mulai kolaborasi hari ini.</p>
                        </div>
                        <a href="{{ route('register') }}" class="btn-primary" style="padding:0.95rem 2.25rem;">
                            Daftar Sekarang &rarr;
                        </a>
                    </div>
                </div>
            </section>
        </main>

        {{-- ============================================================ --}}
        {{-- 7. FOOTER                                                    --}}
        {{-- ============================================================ --}}
        <footer style="background:#0f2922; color:rgba(255,255,255,0.6); margin:0; padding:3.5rem 0 2rem; border-top:1px solid rgba(255,255,255,0.08);">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div style="display:grid; gap:2.5rem; margin-bottom:3rem;" class="lg:grid-cols-4 sm:grid-cols-2">
                    <!-- Brand -->
                    <div>
                        <div style="display:flex; align-items:center; gap:0.625rem; margin-bottom:1rem;">
                            <div style="width:2.25rem; height:2.25rem; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:0.5rem; display:flex; align-items:center; justify-content:center;">
                                <x-application-logo style="width:1.25rem; height:1.25rem; color:#e5b895;" />
                            </div>
                            <div style="font-family:'Playfair Display',serif; font-size:0.9375rem; font-weight:700; color:#ffffff;">Platform Keperawatan</div>
                        </div>
                        <p style="font-size:0.8125rem; line-height:1.7; color:rgba(255,255,255,0.5);">Menghadirkan inovasi dalam rekrutmen dan manajemen talent perawat Indonesia.</p>
                    </div>
                    <!-- Untuk Pengguna -->
                    <div>
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e5b895; margin-bottom:1rem;">Untuk Pengguna</div>
                        @foreach(['Kampus & Tracing Alumni','Regulator & Legalitas','Lembaga Pelatihan','Mitra & Faskes','Perawat Profesional'] as $l)
                            <a href="{{ route('register') }}" style="display:block; font-size:0.8125rem; color:rgba(255,255,255,0.5); text-decoration:none; margin-bottom:0.5rem; transition:color 0.18s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">{{ $l }}</a>
                        @endforeach
                    </div>
                    <!-- Fitur -->
                    <div>
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e5b895; margin-bottom:1rem;">Fitur</div>
                        @foreach(['Verifikasi STR','Bank Kandidat','Lowongan Publik','Sertifikasi BNSP','Forecasting'] as $l)
                            <a href="#" style="display:block; font-size:0.8125rem; color:rgba(255,255,255,0.5); text-decoration:none; margin-bottom:0.5rem; transition:color 0.18s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">{{ $l }}</a>
                        @endforeach
                    </div>
                    <!-- Kontak -->
                    <div>
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e5b895; margin-bottom:1rem;">Kontak</div>
                        <div style="font-size:0.8125rem; color:rgba(255,255,255,0.5); line-height:1.8;">
                            info@platformkeperawatan.id<br>
                            Jakarta, Indonesia<br>
                            KADIN Partner Ecosystem
                        </div>
                    </div>
                </div>
                <div style="border-top:1px solid rgba(255,255,255,0.08); padding-top:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
                    <div style="font-size:0.75rem; color:rgba(255,255,255,0.35);">&copy; {{ date('Y') }} Platform Keperawatan. All Rights Reserved.</div>
                    <div style="font-size:0.75rem; color:rgba(255,255,255,0.35);">KADIN Partner Ecosystem &middot; Talent OS</div>
                </div>
            </div>
        </footer>

    </body>
</html>
