<nav x-data="{ open: false }" style="background: linear-gradient(135deg, #143a30 0%, #1e483b 100%); border-bottom: 1px solid rgba(255,255,255,0.12); position:sticky; top:0; z-index:50; box-shadow:0 8px 32px rgba(15,41,34,0.2);">
    <!-- Desktop nav -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div style="display:flex; align-items:center; justify-content:space-between; height:4.25rem; gap:1.5rem;">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:0.625rem; text-decoration:none; flex-shrink:0;">
                <div style="width:2.35rem; height:2.35rem; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); border-radius:0.75rem; display:flex; align-items:center; justify-content:center;">
                    <x-application-logo style="width:1.35rem; height:1.35rem; color:#e5b895;" />
                </div>
                <div class="hidden sm:block">
                    <div style="font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; color:#ffffff; line-height:1.1;">Platform Keperawatan</div>
                    <div style="font-size:0.62rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:rgba(229,184,149,0.9);">Talent OS &middot; KADIN</div>
                </div>
            </a>

            <!-- Center nav links — desktop -->
            <div class="hidden sm:flex items-center gap-1" style="flex:1; justify-content:center;">
                @if (Auth::user()->role === 'kampus')
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Beranda</x-nav-link>
                    <x-nav-link :href="route('kampus.lulusan.index')" :active="request()->routeIs('kampus.lulusan.*')">Lulusan</x-nav-link>
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger"><button type="button" class="nav-pill">Menu Kampus <svg class="inline h-3.5 w-3.5 ms-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('kampus.forecasting')">📊 Forecasting Supply &amp; Demand</x-dropdown-link>
                            <x-dropdown-link :href="route('kampus.gateway')">Gateway NIK Lulusan</x-dropdown-link>
                            <x-dropdown-link :href="route('kampus.lulusan.import')">Impor Lulusan</x-dropdown-link>
                            <x-dropdown-link :href="route('kampus.tracing.index')">Tracing Alumni</x-dropdown-link>
                            <x-dropdown-link :href="route('kampus.tracing.analytics')">Tracing Analitik</x-dropdown-link>
                        </x-slot>


                    </x-dropdown>

                @elseif (Auth::user()->role === 'lembaga')
                    <x-nav-link :href="route('lembaga.dashboard')" :active="request()->routeIs('lembaga.dashboard')">Dashboard</x-nav-link>
                    <x-nav-link :href="route('lembaga.sertifikasi.create')" :active="request()->routeIs('lembaga.sertifikasi.*')">Sertifikasi</x-nav-link>
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger"><button type="button" class="nav-pill">Menu Lembaga <svg class="inline h-3.5 w-3.5 ms-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('lembaga.pelatihan.index')">Pelatihan</x-dropdown-link>
                            <x-dropdown-link :href="route('lembaga.sertifikasi.pending')">Verifikasi Sertifikat</x-dropdown-link>
                            <x-dropdown-link :href="route('lembaga.sertifikasi.import')">Impor Sertifikasi</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                @elseif (Auth::user()->role === 'regulator')
                    <x-nav-link :href="route('regulator.analytics.index')" :active="request()->routeIs('regulator.analytics.*')">Analitik</x-nav-link>
                    <x-nav-link :href="route('regulator.legalitas.create')" :active="request()->routeIs('regulator.legalitas.*')">Legalitas</x-nav-link>
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger"><button type="button" class="nav-pill">Menu Regulator <svg class="inline h-3.5 w-3.5 ms-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('regulator.legalitas.data.index')">Review Data STR</x-dropdown-link>
                            <x-dropdown-link :href="route('regulator.legalitas.documents')">Review Dokumen</x-dropdown-link>
                            <x-dropdown-link :href="route('regulator.str_sync.index')">Sinkronisasi STR</x-dropdown-link>
                            <x-dropdown-link :href="route('regulator.legalitas.import')">Impor Legalitas</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                @elseif (in_array(Auth::user()->role, ['mitra', 'faskes', 'headhunter']))
                    <x-nav-link :href="route('mitra.kandidat.index')" :active="request()->routeIs('mitra.kandidat.*')">Bank Kandidat</x-nav-link>
                    <x-nav-link :href="route('mitra.lowongan.index')" :active="request()->routeIs('mitra.lowongan.*')">Lowongan</x-nav-link>
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger"><button type="button" class="nav-pill">Menu Mitra <svg class="inline h-3.5 w-3.5 ms-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('mitra.experience_sync.index')">Sinkronisasi Pengalaman</x-dropdown-link>
                            <x-dropdown-link :href="route('mitra.experience_sync.logs')">Log Sinkronisasi</x-dropdown-link>
                            <x-dropdown-link :href="route('mitra.riwayat_kinerja.index')">Riwayat Kinerja</x-dropdown-link>
                            <x-dropdown-link :href="route('mitra.riwayat_pekerjaan.active')">Riwayat Aktif</x-dropdown-link>
                            <x-dropdown-link :href="route('mitra.riwayat_pekerjaan.pending')">Verifikasi Riwayat</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                @elseif (Auth::user()->role === 'perawat')
                    <x-nav-link :href="route('perawat.dashboard')" :active="request()->routeIs('perawat.dashboard')">Profil</x-nav-link>
                    <x-nav-link :href="route('perawat.lowongan.index')" :active="request()->routeIs('perawat.lowongan.*')">Lowongan</x-nav-link>
                    <x-nav-link :href="route('perawat.lamaran.index')" :active="request()->routeIs('perawat.lamaran.*')">Lamaran</x-nav-link>
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger"><button type="button" class="nav-pill">Menu Perawat <svg class="inline h-3.5 w-3.5 ms-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('perawat.str_sync.create')">Sinkronisasi STR</x-dropdown-link>
                            <x-dropdown-link :href="route('perawat.pelatihan.index')">Pelatihan</x-dropdown-link>
                            <x-dropdown-link :href="route('perawat.riwayat_pekerjaan.index')">Riwayat Kerja</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                @elseif (Auth::user()->role === 'admin')
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Admin</x-nav-link>
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Pengguna</x-nav-link>
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger"><button type="button" class="nav-pill">Menu Admin <svg class="inline h-3.5 w-3.5 ms-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('admin.data_quality')">Kualitas Data</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.integrations.index')">Integrasi API</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.integration_playground.index')">Playground Integrasi</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.demo_scenario.index')">Skenario Demo</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.audit_logs.index')">Log Audit</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.api_keys.index')">Kunci API</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.institusi.index')">Institusi</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.lembaga.index')">Lembaga</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.mitra.index')">Mitra</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.regulator_org.index')">Regulator Org</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.mapping.create')">Pemetaan</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                @else
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                @endif
            </div>

            <!-- Right side: role badge + notif + user menu -->
            @php
                $notificationRoute = null;
                if (in_array(Auth::user()->role, ['mitra', 'faskes', 'headhunter'])) {
                    $notificationRoute = route('mitra.notifikasi');
                } elseif (Auth::user()->role === 'perawat') {
                    $notificationRoute = route('perawat.notifikasi');
                }
                $isPerawat = Auth::user()->role === 'perawat';
                $notifTipe = (string) request()->query('tipe', 'all');
                $notifLegalitasStatus = (string) request()->query('legalitas_status', 'all');
                $legalitasStatusOptions = $isPerawat
                    ? array_values(array_unique(array_merge(
                        \App\Models\DataLegalitas::dataStatusOptions(),
                        \App\Models\DataLegalitas::docStatusOptions()
                    )))
                    : [];
                $unreadCount = Auth::user()->unreadNotifications()->count();
                $latestNotificationsQuery = Auth::user()->notifications()->latest();
                if ($isPerawat && $notificationRoute) {
                    if ($notifTipe === 'legalitas') {
                        \App\Support\NotificationQuery::whereDataIn($latestNotificationsQuery, 'tipe', ['legalitas_document_status', 'legalitas_data_status']);
                    }
                    if ($notifLegalitasStatus !== 'all') {
                        \App\Support\NotificationQuery::whereDataEquals($latestNotificationsQuery, 'status', $notifLegalitasStatus);
                        \App\Support\NotificationQuery::whereDataIn($latestNotificationsQuery, 'tipe', ['legalitas_document_status', 'legalitas_data_status']);
                    }
                }
                $latestNotifications = $latestNotificationsQuery->limit(5)->get();
                $notificationViewAllUrl = $notificationRoute;
                if ($isPerawat && $notificationRoute) {
                    $notificationViewAllUrl = route('perawat.notifikasi', array_filter([
                        'tipe' => $notifTipe !== 'all' ? $notifTipe : null,
                        'legalitas_status' => $notifLegalitasStatus !== 'all' ? $notifLegalitasStatus : null,
                    ]));
                }
                $roleLabel = strtoupper((string) Auth::user()->role);
                $roleBadgeColor = match(Auth::user()->role) {
                    'kampus'     => 'background:rgba(229,184,149,0.2);border-color:rgba(229,184,149,0.4);color:#ebd3c2;',
                    'lembaga'    => 'background:rgba(161,109,73,0.25);border-color:rgba(161,109,73,0.45);color:#f4efe6;',
                    'regulator'  => 'background:rgba(176,120,80,0.25);border-color:rgba(176,120,80,0.45);color:#ebd3c2;',
                    'mitra','faskes','headhunter' => 'background:rgba(85,122,88,0.3);border-color:rgba(85,122,88,0.5);color:#e3ebe1;',
                    'perawat'    => 'background:rgba(216,164,127,0.25);border-color:rgba(216,164,127,0.45);color:#ffffff;',
                    'admin'      => 'background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.3);color:#ffffff;',
                    default      => 'background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.2);color:#e3d1b0;',
                };
            @endphp

            <div class="hidden sm:flex items-center gap-2.5" style="flex-shrink:0;">
                <!-- Role badge -->
                <span class="hidden xl:inline-flex" style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; padding:0.25rem 0.8rem; border-radius:9999px; border:1px solid; {{ $roleBadgeColor }}">{{ $roleLabel }}</span>

                <!-- Notification bell -->
                @if ($notificationRoute)
                    <x-dropdown align="right" width="w-80">
                        <x-slot name="trigger">
                            <button type="button" style="position:relative; display:inline-flex; align-items:center; justify-content:center; width:2.35rem; height:2.35rem; border-radius:9999px; border:1px solid rgba(255,255,255,0.22); background:rgba(255,255,255,0.12); color:#ffffff; transition:all 0.18s;" aria-label="Notifikasi" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                                <svg style="width:1.1rem; height:1.1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/>
                                    <path d="M9 17a3 3 0 0 0 6 0"/>
                                </svg>
                                @if ($unreadCount > 0)
                                    <span style="position:absolute; top:-3px; right:-3px; min-width:1.1rem; height:1.1rem; display:flex; align-items:center; justify-content:center; border-radius:9999px; background:#d8a47f; color:#2a1f18; font-size:0.6rem; font-weight:800; padding:0 3px;">{{ $unreadCount }}</span>
                                @endif
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div style="padding:0.875rem 1rem; border-bottom:1px solid #f0e5cf;">
                                <div style="font-size:0.7rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#9a9a9a;">Notifikasi</div>
                                <div style="font-size:0.8rem; color:#9a9a9a; margin-top:0.125rem;">{{ $unreadCount }} belum dibaca</div>
                            </div>
                            @if ($isPerawat)
                                <form method="GET" action="{{ route('perawat.notifikasi') }}" style="padding:0.75rem 1rem; border-bottom:1px solid #f8f2e6; display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
                                    <select name="tipe" class="select-field" style="font-size:0.75rem;">
                                        <option value="all" @selected($notifTipe === 'all')>Semua</option>
                                        <option value="legalitas" @selected($notifTipe === 'legalitas')>Legalitas</option>
                                    </select>
                                    <select name="legalitas_status" class="select-field" style="font-size:0.75rem;">
                                        <option value="all" @selected($notifLegalitasStatus === 'all')>Semua Status</option>
                                        @foreach ($legalitasStatusOptions as $status)
                                            <option value="{{ $status }}" @selected($notifLegalitasStatus === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn-primary" type="submit" style="font-size:0.7rem; padding:0.4rem 0.875rem; grid-column:span 1;">Filter</button>
                                    <a href="{{ route('perawat.notifikasi') }}" class="btn-secondary" style="font-size:0.7rem; padding:0.38rem 0.875rem; text-align:center;">Reset</a>
                                </form>
                            @endif
                            <div style="max-height:18rem; overflow-y:auto;">
                                @forelse ($latestNotifications as $notification)
                                    @php
                                        $title = $notification->data['title'] ?? $notification->data['message'] ?? $notification->data['status'] ?? 'Notifikasi baru';
                                    @endphp
                                    <a href="{{ $notificationViewAllUrl }}" style="display:block; padding:0.75rem 1rem; text-decoration:none; border-bottom:1px solid #f8f2e6; transition:background 0.18s; {{ $notification->read_at ? '' : 'background:#f4efe6;' }}">
                                        <div style="font-size:0.875rem; font-weight:600; color:#1a1a1a;">{{ $title }}</div>
                                        <div style="font-size:0.75rem; color:#9a9a9a; margin-top:0.2rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                    </a>
                                @empty
                                    <div style="padding:1.5rem 1rem; text-align:center; font-size:0.875rem; color:#9a9a9a;">Belum ada notifikasi.</div>
                                @endforelse
                            </div>
                            <div style="padding:0.75rem 1rem; border-top:1px solid #f0e5cf;">
                                <a href="{{ $notificationViewAllUrl }}" class="btn-secondary" style="width:100%; font-size:0.75rem;">Lihat Semua</a>
                            </div>
                        </x-slot>
                    </x-dropdown>
                @endif

                <!-- User dropdown -->
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.4rem 0.95rem; border-radius:9999px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); font-size:0.8125rem; font-weight:600; color:#ffffff; cursor:pointer; transition:all 0.18s;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                            <span style="max-width:9rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ Auth::user()->name }}</span>
                            <svg style="width:0.9rem; height:0.9rem; flex-shrink:0; opacity:0.8;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger mobile -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" style="display:inline-flex; align-items:center; justify-content:center; width:2.25rem; height:2.25rem; border-radius:0.5rem; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:#ffffff; cursor:pointer;">
                    <svg style="width:1.25rem; height:1.25rem;" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Impersonate banner -->
    @if (session()->has('impersonator_id'))
        <div style="background:#fff5f2; border-bottom:1px solid rgba(224,82,82,0.25); padding:0.625rem 1rem;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <p style="font-size:0.875rem; color:#c0392b; font-weight:500;">
                    Mode Impersonate aktif: <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->role }}) &mdash; Kembali ke: <strong>{{ session('impersonator_name', 'Admin') }}</strong>
                </p>
                <form method="POST" action="{{ route('impersonation.stop') }}">
                    @csrf
                    <button type="submit" class="btn-secondary" style="font-size:0.75rem; padding:0.35rem 1rem; color:#c0392b; border-color:rgba(224,82,82,0.35);">Hentikan</button>
                </form>
            </div>
        </div>
    @endif

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden" style="background:#fdfaf5; border-bottom:1px solid #f0e5cf; padding:1rem;">
        <div style="display:flex; flex-direction:column; gap:0.25rem;">
            @if ($notificationRoute)
                <a href="{{ $notificationViewAllUrl }}" class="mobile-nav-pill" style="display:flex; align-items:center; justify-content:space-between;">
                    <span>Notifikasi</span>
                    @if ($unreadCount > 0)
                        <span style="background:#e05252; color:#fff; font-size:0.65rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:9999px;">{{ $unreadCount }}</span>
                    @endif
                </a>
            @endif

            @if (Auth::user()->role === 'kampus')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Beranda</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kampus.gateway')">Gateway NIK Lulusan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kampus.lulusan.index')" :active="request()->routeIs('kampus.lulusan.*')">Lulusan</x-responsive-nav-link>

                <x-responsive-nav-link :href="route('kampus.lulusan.import')">Impor Lulusan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kampus.tracing.index')">Tracing Alumni</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kampus.tracing.analytics')">Tracing Analitik</x-responsive-nav-link>
            @elseif (Auth::user()->role === 'lembaga')
                <x-responsive-nav-link :href="route('lembaga.dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lembaga.sertifikasi.create')">Sertifikasi</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lembaga.pelatihan.index')">Pelatihan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lembaga.sertifikasi.pending')">Verifikasi Sertifikat</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lembaga.sertifikasi.import')">Impor Sertifikasi</x-responsive-nav-link>
            @elseif (Auth::user()->role === 'regulator')
                <x-responsive-nav-link :href="route('regulator.analytics.index')">Analitik</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('regulator.legalitas.create')">Legalitas</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('regulator.legalitas.data.index')">Review Data STR</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('regulator.legalitas.documents')">Review Dokumen</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('regulator.str_sync.index')">Sinkronisasi STR</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('regulator.legalitas.import')">Impor Legalitas</x-responsive-nav-link>
            @elseif (in_array(Auth::user()->role, ['mitra', 'faskes', 'headhunter']))
                <x-responsive-nav-link :href="route('mitra.kandidat.index')">Bank Kandidat</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.lowongan.index')">Lowongan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.experience_sync.index')">Sinkronisasi Pengalaman</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.riwayat_kinerja.index')">Riwayat Kinerja</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.riwayat_pekerjaan.active')">Riwayat Aktif</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.riwayat_pekerjaan.pending')">Verifikasi Riwayat</x-responsive-nav-link>
            @elseif (Auth::user()->role === 'perawat')
                <x-responsive-nav-link :href="route('perawat.dashboard')">Profil</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('perawat.lowongan.index')">Lowongan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('perawat.lamaran.index')">Lamaran</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('perawat.str_sync.create')">Sinkronisasi STR</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('perawat.pelatihan.index')">Pelatihan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('perawat.riwayat_pekerjaan.index')">Riwayat Kerja</x-responsive-nav-link>
            @elseif (Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')">Dashboard Admin</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')">Pengguna</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.data_quality')">Kualitas Data</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.audit_logs.index')">Log Audit</x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')">Dashboard</x-responsive-nav-link>
            @endif

            <!-- Mobile user info -->
            <div style="border-top:1px solid #f0e5cf; margin-top:0.5rem; padding-top:0.75rem;">
                <div style="padding:0.25rem 1rem; font-weight:600; color:#1a1a1a; font-size:0.9375rem;">{{ Auth::user()->name }}</div>
                <div style="padding:0.25rem 1rem; font-size:0.8125rem; color:#9a9a9a; margin-bottom:0.5rem;">{{ Auth::user()->email }}</div>
                <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
