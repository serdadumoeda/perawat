<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
            <div>
                <h2 style="font-family:'Playfair Display',serif; font-size:1.75rem; font-weight:700; color:#1c2621; margin:0;">Dashboard OS</h2>
                <p style="font-size:0.875rem; color:#596960; margin:0.25rem 0 0;">Ringkasan akses dan modul utama ekosistem keperawatan Anda.</p>
            </div>
            @php
                $roleStyle = match(Auth::user()->role) {
                    'kampus'     => 'background:rgba(229,184,149,0.3);border-color:rgba(161,109,73,0.4);color:#573d33;',
                    'lembaga'    => 'background:rgba(85,122,88,0.25);border-color:rgba(85,122,88,0.45);color:#1e483b;',
                    'regulator'  => 'background:rgba(176,120,80,0.25);border-color:rgba(176,120,80,0.45);color:#48342b;',
                    'mitra', 'faskes', 'headhunter' => 'background:rgba(20,58,48,0.15);border-color:rgba(20,58,48,0.3);color:#143a30;',
                    'perawat'    => 'background:rgba(216,164,127,0.3);border-color:rgba(216,164,127,0.5);color:#573d33;',
                    'admin'      => 'background:#143a30;border-color:#0f2922;color:#ffffff;',
                    default      => 'background:#e3d1b0;border-color:#cbb793;color:#324039;',
                };
            @endphp
            <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; padding:0.35rem 1rem; border-radius:9999px; border:1px solid; {{ $roleStyle }}">
                Peran: {{ strtoupper(Auth::user()->role) }}
            </span>
        </div>
    </x-slot>

    <div class="module-shell module-shell-compact" style="padding-top:1.5rem;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            <div style="display:grid; gap:1.75rem;" class="lg:grid-cols-[1.3fr,0.7fr]">

                {{-- ===== QUICK ACTIONS ===== --}}
                <div style="background:#ffffff; border:1px solid #e3d1b0; border-radius:1.75rem; padding:1.85rem; box-shadow:0 16px 48px rgba(20,58,48,0.06);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid #eae2d3;">
                        <div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#1c2621;">Modul &amp; Aksi Utama</div>
                            <div style="font-size:0.8125rem; color:#596960; margin-top:0.2rem;">Pintasan instan sesuai otorisasi peran pengguna.</div>
                        </div>
                        <span class="badge badge-teal" style="background:rgba(85,122,88,0.15); color:#2a5546; border-color:rgba(85,122,88,0.3);">Sesi Aktif</span>
                    </div>

                    <div style="display:grid; gap:1rem;" class="md:grid-cols-2">
                        @if (Auth::user()->role === 'kampus')
                            <!-- Card 1: Sage Green -->
                            <a href="{{ route('kampus.lulusan.index') }}" style="background:#557a58; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; display:flex; flex-direction:column; justify-between:space-between; position:relative; overflow:hidden; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 32px rgba(85,122,88,0.3)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(255,255,255,0.2); padding:0.2rem 0.6rem; border-radius:9999px;">Data Utama</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Kelola Lulusan</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Input dan kelola data alumni perawat.</div>
                            </a>

                            <!-- Card 2: Mocha Earth -->
                            <a href="{{ route('kampus.lulusan.import') }}" style="background:#573d33; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; display:flex; flex-direction:column; justify-between:space-between; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 32px rgba(87,61,51,0.3)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.18); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#ebd3c2;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(255,255,255,0.15); padding:0.2rem 0.6rem; border-radius:9999px;">Batch CSV</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Impor Lulusan</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.78); margin-top:0.35rem;">Unggah berkas masal alumni kampus.</div>
                            </a>

                            <!-- Card 3: Terracotta -->
                            <a href="{{ route('kampus.tracing.index') }}" style="background:#a16d49; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; display:flex; flex-direction:column; justify-between:space-between; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 32px rgba(161,109,73,0.3)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(255,255,255,0.2); padding:0.2rem 0.6rem; border-radius:9999px;">Karier</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Tracing Alumni</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Pantau perkembangan status kerja.</div>
                            </a>

                            <!-- Card 4: Sand Beige -->
                            <a href="{{ route('kampus.tracing.analytics') }}" style="background:#e3d1b0; border-radius:1.5rem; padding:1.5rem; color:#2c3732; text-decoration:none; display:flex; flex-direction:column; justify-between:space-between; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 32px rgba(227,209,176,0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:#48342b; display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#e3d1b0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 012-2h2a2 2 0 012 2v6m4 0V9a2 2 0 00-2-2h-2a2 2 0 00-2 2v10M5 19v-4a2 2 0 012-2h2a2 2 0 012 2v4"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(72,52,43,0.12); color:#48342b; padding:0.2rem 0.6rem; border-radius:9999px;">Insight</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Analitik Tracing</div>
                                <div style="font-size:0.8rem; color:#596960; margin-top:0.35rem;">Statistik keterserapan perawat.</div>
                            </a>
                        @endif

                        @if (Auth::user()->role === 'lembaga')
                            <!-- Card 1: Sage Green -->
                            <a href="{{ route('lembaga.dashboard') }}" style="background:#557a58; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Dashboard Lembaga</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Ringkasan pelatihan &amp; uji sertifikasi.</div>
                            </a>
                            <!-- Card 2: Terracotta -->
                            <a href="{{ route('lembaga.pelatihan.index') }}" style="background:#a16d49; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Katalog Pelatihan</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Atur jadwal dan kuota pelatihan.</div>
                            </a>
                            <!-- Card 3: Dark Chocolate -->
                            <a href="{{ route('lembaga.sertifikasi.create') }}" style="background:#48342b; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Input Sertifikasi</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Catat hasil uji kompetensi resmi.</div>
                            </a>
                            <!-- Card 4: Sand Beige -->
                            <a href="{{ route('lembaga.sertifikasi.pending') }}" style="background:#e3d1b0; border-radius:1.5rem; padding:1.5rem; color:#2c3732; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Verifikasi Sertifikat</div>
                                <div style="font-size:0.8rem; color:#596960; margin-top:0.35rem;">Validasi dokumen sertifikasi.</div>
                            </a>
                        @endif

                        @if (Auth::user()->role === 'regulator')
                            <!-- Card 1: Mocha Earth -->
                            <a href="{{ route('regulator.legalitas.create') }}" style="background:#573d33; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Input Legalitas</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Masukkan STR &amp; izin praktik resmi.</div>
                            </a>
                            <!-- Card 2: Terracotta -->
                            <a href="{{ route('regulator.legalitas.documents') }}" style="background:#a16d49; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Review Dokumen</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Setujui atau tolak berkas legalitas.</div>
                            </a>
                            <!-- Card 3: Sage Green -->
                            <a href="{{ route('regulator.analytics.index') }}" style="background:#557a58; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Analitik Regulator</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Pantau tren kualifikasi &amp; STR.</div>
                            </a>
                            <!-- Card 4: Dark Chocolate -->
                            <a href="{{ route('regulator.str_sync.index') }}" style="background:#48342b; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Sinkronisasi STR</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Kelola integrasi data otomatis.</div>
                            </a>
                        @endif

                        @if (in_array(Auth::user()->role, ['mitra', 'faskes', 'headhunter']))
                            @php($mitraUnread = Auth::user()->unreadNotifications()->count())
                            <!-- Card 1: Sage Green -->
                            <a href="{{ route('mitra.kandidat.index') }}" style="background:#557a58; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-text-transform:uppercase; background:rgba(255,255,255,0.2); padding:0.2rem 0.6rem; border-radius:9999px;">Verified Talent</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Bank Kandidat</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Cari kandidat terverifikasi terbaik.</div>
                            </a>

                            <!-- Card 2: Mocha Earth -->
                            <a href="{{ route('mitra.lowongan.index') }}" style="background:#573d33; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.18); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#ebd3c2;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 132000"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(255,255,255,0.15); padding:0.2rem 0.6rem; border-radius:9999px;">Rekrutmen</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Kelola Lowongan</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.78); margin-top:0.35rem;">Publikasikan posisi rekrutmen.</div>
                            </a>

                            <!-- Card 3: Terracotta -->
                            <a href="{{ route('mitra.notifikasi') }}" style="background:#a16d49; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4a2 2 0 01-.6-1.4V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5"/></svg>
                                    </div>
                                    @if ($mitraUnread > 0)
                                        <span style="font-size:0.65rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; background:#e5b895; color:#2a1f18; padding:0.2rem 0.6rem; border-radius:9999px;">{{ $mitraUnread }} Baru</span>
                                    @endif
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Notifikasi Mitras</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Pantau status sync &amp; pelamar.</div>
                            </a>

                            <!-- Card 4: Sand Beige -->
                            <a href="{{ route('mitra.riwayat_kinerja.index') }}" style="background:#e3d1b0; border-radius:1.5rem; padding:1.5rem; color:#2c3732; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:#48342b; display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#e3d1b0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(72,52,43,0.12); color:#48342b; padding:0.2rem 0.6rem; border-radius:9999px;">Evaluasi</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Riwayat Kinerja</div>
                                <div style="font-size:0.8rem; color:#596960; margin-top:0.35rem;">Evaluasi performa kontrak.</div>
                            </a>
                        @endif

                        @if (Auth::user()->role === 'perawat')
                            <!-- Card 1: Sage Green -->
                            <a href="{{ route('perawat.dashboard') }}" style="background:#557a58; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(255,255,255,0.2); padding:0.2rem 0.6rem; border-radius:9999px;">Profil Saya</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Identitas &amp; Dokumen</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Perbarui data STR &amp; sertifikasi.</div>
                            </a>

                            <!-- Card 2: Mocha Earth -->
                            <a href="{{ route('perawat.lowongan.index') }}" style="background:#573d33; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.18); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#ebd3c2;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 132000"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(255,255,255,0.15); padding:0.2rem 0.6rem; border-radius:9999px;">Peluang</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Cari Lowongan</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.78); margin-top:0.35rem;">Temukan posisi perawat terbaik.</div>
                            </a>

                            <!-- Card 3: Terracotta -->
                            <a href="{{ route('perawat.lamaran.index') }}" style="background:#a16d49; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(255,255,255,0.2); padding:0.2rem 0.6rem; border-radius:9999px;">Status</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Lamaran Saya</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Pantau status dan alur lamaran.</div>
                            </a>

                            <!-- Card 4: Sand Beige -->
                            <a href="{{ route('perawat.riwayat_pekerjaan.index') }}" style="background:#e3d1b0; border-radius:1.5rem; padding:1.5rem; color:#2c3732; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                                    <div style="width:2.5rem; height:2.5rem; border-radius:9999px; background:#48342b; display:flex; align-items:center; justify-content:center;">
                                        <svg style="width:1.25rem; height:1.25rem; color:#e3d1b0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; background:rgba(72,52,43,0.12); color:#48342b; padding:0.2rem 0.6rem; border-radius:9999px;">Pengalaman</span>
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Riwayat Kerja</div>
                                <div style="font-size:0.8rem; color:#596960; margin-top:0.35rem;">Ajukan verifikasi pengalaman.</div>
                            </a>
                        @endif

                        @if (Auth::user()->role === 'admin')
                            <!-- Card 1: Sage Green -->
                            <a href="{{ route('admin.dashboard') }}" style="background:#557a58; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Dashboard Admin</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.8); margin-top:0.35rem;">Monitor kualitas data &amp; sistem.</div>
                            </a>
                            <!-- Card 2: Mocha Earth -->
                            <a href="{{ route('admin.users.index') }}" style="background:#573d33; border-radius:1.5rem; padding:1.5rem; color:#ffffff; text-decoration:none; transition:all 0.22s;" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='translateY(0)';">
                                <div style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700;">Manajemen Pengguna</div>
                                <div style="font-size:0.8rem; color:rgba(255,255,255,0.78); margin-top:0.35rem;">Approve dan kelola perizinan.</div>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- ===== PANDUAN SINGKAT ===== --}}
                <div style="background:#ffffff; border:1px solid #e3d1b0; border-radius:1.75rem; padding:1.85rem; box-shadow:0 16px 48px rgba(20,58,48,0.06);">
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
                        <div style="width:2.25rem; height:2.25rem; border-radius:0.75rem; background:rgba(85,122,88,0.15); display:flex; align-items:center; justify-content:center;">
                            <svg style="width:1.15rem; height:1.15rem; color:#557a58;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#1c2621;">Panduan Singkat</div>
                    </div>
                    <p style="font-size:0.8125rem; color:#596960; margin:0 0 1.5rem;">Ikuti langkah berikut untuk memastikan data terverifikasi penuh.</p>

                    <div style="display:flex; flex-direction:column; gap:0.875rem;">
                        @foreach([
                            ['n'=>'1','text'=>'Lengkapi data inti & dokumen legalitas sesuai peran Anda.','bg'=>'rgba(20,58,48,0.06)','bd'=>'rgba(20,58,48,0.15)','color'=>'#143a30'],
                            ['n'=>'2','text'=>'Pastikan dokumen tervalidasi sebelum proses rekrutmen.','bg'=>'rgba(161,109,73,0.08)','bd'=>'rgba(161,109,73,0.2)','color'=>'#a16d49'],
                            ['n'=>'3','text'=>'Gunakan dashboard analitik untuk keputusan cepat & tepat.','bg'=>'rgba(85,122,88,0.08)','bd'=>'rgba(85,122,88,0.2)','color'=>'#557a58'],
                        ] as $step)
                            <div style="display:flex; align-items:start; gap:0.875rem; padding:0.875rem 1rem; border-radius:1rem; background:{{ $step['bg'] }}; border:1px solid {{ $step['bd'] }};">
                                <span style="width:1.75rem; height:1.75rem; border-radius:9999px; background:{{ $step['color'] }}; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; color:#ffffff; flex-shrink:0;">{{ $step['n'] }}</span>
                                <div style="font-size:0.8125rem; font-weight:500; color:#324039; line-height:1.6;">{{ $step['text'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- User info card -->
                    <div style="margin-top:1.5rem; padding:1.15rem; border-radius:1.25rem; background:#f4efe6; border:1px solid #e3d1b0;">
                        <div style="font-size:0.68rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#8c9b92; margin-bottom:0.5rem;">Profil Akun</div>
                        <div style="font-size:1rem; font-weight:700; color:#1c2621;">{{ Auth::user()->name }}</div>
                        <div style="font-size:0.8rem; color:#596960; margin-top:0.2rem;">{{ Auth::user()->email }}</div>
                        <a href="{{ route('profile.edit') }}" class="btn-primary" style="display:inline-flex; align-items:center; gap:0.375rem; margin-top:1rem; font-size:0.7rem; padding:0.45rem 1.25rem; text-decoration:none;">
                            Edit Profil →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
