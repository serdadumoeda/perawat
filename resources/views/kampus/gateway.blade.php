<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
            <div>
                <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Kampus Gateway API</div>
                <h2 style="font-family:'Playfair Display',serif; font-size:1.625rem; font-weight:700; color:#1a1a1a; margin:0;">Gateway Awal Data Perawat (by NIK)</h2>
                <p style="font-size:0.875rem; color:#9a9a9a; margin:0.25rem 0 0;">Pencarian, validasi, dan penarikan otomatis data awal perawat dari Kampus / PDDikti berdasarkan NIK 16 digit.</p>
            </div>
            <span class="badge badge-teal">PDDikti &amp; Kampus API Active</span>
        </div>
    </x-slot>

    <div class="module-shell module-shell-compact">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 module-container" style="display:flex; flex-direction:column; gap:1.75rem;">

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            {{-- ===== 1. SEARCH FORM BY NIK (WITH AUTO-FORMATTING & REAL-TIME VALIDATION) ===== --}}
            <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:2rem; box-shadow:0 8px 30px rgba(0,0,0,0.04);">
                <div style="margin-bottom:1.25rem; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
                    <div>
                        <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#1a1a1a;">Pencarian Data Lulusan by NIK</div>
                        <div style="font-size:0.8125rem; color:#9a9a9a; margin-top:0.25rem;">Masukkan 16 digit NIK perawat untuk menarik data ijazah &amp; status akademik resmi dari institusi.</div>
                    </div>
                    <!-- Live NIK Digits Indicator -->
                    <div id="nik-validation-badge" class="badge badge-muted" style="font-size:0.75rem; padding:0.4rem 1rem;">
                        <span>0 / 16 Digit NIK</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('kampus.gateway') }}" style="display:flex; flex-direction:column; gap:1rem;">
                    <div style="display:flex; gap:0.75rem;" class="flex-col sm:flex-row">
                        <div style="flex:1; position:relative;">
                            <input 
                                type="text"
                                id="nik-gateway-input" 
                                name="nik" 
                                value="{{ $nik ?? old('nik') }}" 
                                placeholder="3578 0000 0000 0001" 
                                maxlength="19" 
                                autocomplete="off"
                                required 
                                class="input-field"
                                style="font-size:1.125rem; letter-spacing:0.08em; font-weight:700; color:#0f5050; padding:0.875rem 1.25rem;"
                            />
                        </div>
                        <button type="submit" class="btn-primary" style="padding:0.75rem 2rem; white-space:nowrap; font-size:0.875rem;">
                            <svg style="width:1.1rem; height:1.1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Cari Data Kampus &rarr;
                        </button>
                    </div>

                    <!-- Sample NIK Quick Test Chips -->
                    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5rem; margin-top:0.5rem;">
                        <span style="font-size:0.7rem; font-weight:700; color:#9a9a9a; text-transform:uppercase; letter-spacing:0.08em;">Contoh NIK Simulasi:</span>
                        @foreach([
                            '3578000000000001' => 'Nursi Rahma (UNAIR)',
                            '3171000000000002' => 'Siti Nur Aini (UI)',
                            '3273000000000003' => 'Budi Santoso (UNPAD)',
                        ] as $sampleNik => $label)
                            <a href="{{ route('kampus.gateway', ['nik' => $sampleNik]) }}" style="font-size:0.7rem; font-weight:600; padding:0.25rem 0.75rem; border-radius:9999px; border:1px solid #f0e5cf; background:#fdfaf5; color:#0f5050; text-decoration:none; transition:all 0.18s;" onmouseover="this.style.borderColor='#e05252';this.style.color='#e05252';" onmouseout="this.style.borderColor='#f0e5cf';this.style.color='#0f5050';">
                                {{ $sampleNik }} ({{ $label }})
                            </a>
                        @endforeach
                    </div>
                </form>
            </div>

            {{-- ===== 2. CAREER STEPPER 5-STAGE ===== --}}
            @if ($nik && $result && $result['status'] === 'success' && $result['found'])
                <x-career-stepper :result="$result" />
            @endif

            {{-- ===== 3. GATEWAY RESULT PREVIEW ===== --}}
            @if ($nik)
                @if ($result && $result['status'] === 'success' && $result['found'])
                    <div class="fade-up" style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; overflow:hidden; box-shadow:0 12px 40px rgba(0,0,0,0.06);">
                        
                        <!-- Gateway Status Banner -->
                        <div style="background:linear-gradient(135deg, #0b3535 0%, #0d4040 100%); color:#ffffff; padding:1.5rem 2rem; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
                            <div>
                                <div style="display:inline-flex; align-items:center; gap:0.5rem; font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#f7a58a; margin-bottom:0.25rem;">
                                    <span style="width:0.4rem; height:0.4rem; border-radius:9999px; background:#10b981; display:inline-block;"></span>
                                    {{ $result['source_label'] }}
                                </div>
                                <div style="font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:700; color:#ffffff;">{{ $result['nama'] }}</div>
                                <div style="font-size:0.8125rem; color:rgba(255,255,255,0.7); margin-top:0.125rem;">NIK: <strong>{{ $result['nik'] }}</strong> &middot; Email: {{ $result['email'] ?? '-' }}</div>
                            </div>
                            <span class="badge badge-teal" style="background:rgba(255,255,255,0.15); border-color:rgba(255,255,255,0.3); color:#ffffff; font-size:0.75rem; padding:0.4rem 1rem;">
                                {{ $result['status_kesiapan_label'] }}
                            </span>
                        </div>

                        <div style="padding:2rem;">
                            <div style="display:grid; gap:2rem;" class="lg:grid-cols-2">

                                {{-- Identitas Institusi --}}
                                <div style="background:#fdfaf5; border:1px solid #f0e5cf; border-radius:1rem; padding:1.5rem;">
                                    <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e05252; margin-bottom:0.875rem;">1. Data Institusi Pendidikan</div>
                                    <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#1a1a1a; margin-bottom:0.75rem;">
                                        {{ $result['institusi']['nama'] }}
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.8125rem; color:#5a5a5a;">
                                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0e5cf; padding-bottom:0.375rem;">
                                            <span>Kode Perguruan Tinggi:</span> <strong style="color:#1a1a1a;">{{ $result['institusi']['kode_pt'] }}</strong>
                                        </div>
                                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0e5cf; padding-bottom:0.375rem;">
                                            <span>Akreditasi Institusi:</span> <strong style="color:#065f46;">{{ $result['institusi']['akreditasi'] }}</strong>
                                        </div>
                                        <div style="display:flex; justify-content:space-between;">
                                            <span>Status Verifikasi Kampus:</span> <strong style="color:#0f5050;">Official Academic Issuer</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Data Akademik --}}
                                <div style="background:#fdfaf5; border:1px solid #f0e5cf; border-radius:1rem; padding:1.5rem;">
                                    <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e05252; margin-bottom:0.875rem;">2. Catatan Akademik &amp; Ijazah</div>
                                    <div style="font-size:0.9375rem; font-weight:700; color:#1a1a1a; margin-bottom:0.75rem;">
                                        {{ $result['akademik']['jenjang'] }} &middot; {{ $result['akademik']['program_studi'] }}
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.8125rem; color:#5a5a5a;">
                                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0e5cf; padding-bottom:0.375rem;">
                                            <span>Nomor Ijazah Resmi:</span> <strong style="color:#1a1a1a;">{{ $result['akademik']['nomor_ijazah'] }}</strong>
                                        </div>
                                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0e5cf; padding-bottom:0.375rem;">
                                            <span>Masa Studi:</span> <strong style="color:#1a1a1a;">{{ $result['akademik']['tahun_masuk'] }} &ndash; {{ $result['akademik']['tahun_lulus'] }}</strong>
                                        </div>
                                        <div style="display:flex; justify-content:space-between;">
                                            <span>Indeks Prestasi (IPK):</span> <strong style="color:#e05252; font-size:0.9375rem;">{{ $result['akademik']['ipk'] }} / 4.00</strong>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Verification Hash & Ingestion Action --}}
                            <div style="margin-top:2rem; padding:1.25rem 1.5rem; border-radius:1rem; background:#f8f2e6; border:1px solid #f0e5cf; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1.25rem;">
                                <div>
                                    <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#9a9a9a;">Digital Hash Token</div>
                                    <code style="font-size:0.75rem; color:#0f5050; word-break:break-all;">{{ $result['verification_hash'] }}</code>
                                </div>

                                <form method="POST" action="{{ route('kampus.gateway.ingest') }}" style="margin:0;">
                                    @csrf
                                    <input type="hidden" name="nik" value="{{ $result['nik'] }}">
                                    <input type="hidden" name="nama" value="{{ $result['nama'] }}">
                                    <input type="hidden" name="institusi_id" value="{{ $result['institusi']['id'] ?? '' }}">
                                    <input type="hidden" name="jenjang" value="{{ $result['akademik']['jenjang'] }}">
                                    <input type="hidden" name="program_studi" value="{{ $result['akademik']['program_studi'] }}">
                                    <input type="hidden" name="nomor_ijazah" value="{{ $result['akademik']['nomor_ijazah'] }}">
                                    <input type="hidden" name="ipk" value="{{ $result['akademik']['ipk'] }}">

                                    <button type="submit" class="btn-primary" style="padding:0.75rem 1.75rem; white-space:nowrap;">
                                        Impor &amp; Verifikasi Profil Perawat &rarr;
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                    {{-- ===== 4. DIGITAL IDENTITY CARD PREVIEW ===== --}}
                    <div style="margin-top:2rem;">
                        <div style="text-align:center; margin-bottom:1.5rem;">
                            <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e05252;">Preview Cetak ID Card</div>
                            <h3 style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#1a1a1a;">Kartu Identitas Digital Perawat</h3>
                        </div>

                        <x-digital-id-card 
                            :nik="$result['nik']" 
                            :nama="$result['nama']" 
                            :kampus="$result['institusi']['nama']" 
                            :ijazah="$result['akademik']['nomor_ijazah']" 
                            :hash="$result['verification_hash']" 
                        />
                    </div>
                @else
                    <div class="empty-state">
                        <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#1a1a1a; margin-bottom:0.5rem;">
                            Data NIK {{ $nik }} tidak ditemukan di Kampus Gateway.
                        </div>
                        <p style="margin:0;">Pastikan NIK 16 digit telah didaftarkan oleh institusi pendidikan atau PDDikti.</p>
                    </div>
                @endif
            @endif

            {{-- ===== 5. API USAGE DOCUMENTATION ===== --}}
            <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.75rem;">
                <div style="display:flex; align-items:center; gap:0.625rem; margin-bottom:1rem;">
                    <div style="width:2rem; height:2rem; border-radius:0.5rem; background:rgba(15,80,80,0.1); display:flex; align-items:center; justify-content:center;">
                        <svg style="width:1rem; height:1rem; color:#0f5050;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#1a1a1a;">Dokumentasi REST API Gateway (by NIK)</div>
                </div>

                <p style="font-size:0.8125rem; color:#5a5a5a; line-height:1.6; margin:0 0 1rem;">
                    Sistem luar (Rumah Sakit, Agency, atau Sistem Kampus) dapat melakukan query data ijazah &amp; legalitas perawat berbasis NIK secara programmatic melalui endpoint JSON berikut:
                </p>

                <div style="padding:1rem; border-radius:0.75rem; background:#0b3535; color:#f7a58a; font-family:monospace; font-size:0.8rem; overflow-x:auto;">
                    GET /api/v1/kampus/gateway/{nik}
                </div>
            </div>

        </div>
    </div>

    {{-- NIK Auto-formatting & Validation Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('nik-gateway-input');
            const badge = document.getElementById('nik-validation-badge');

            if (!input || !badge) return;

            function formatAndValidateNIK() {
                let raw = input.value.replace(/[^0-9]/g, '');
                if (raw.length > 16) raw = raw.substring(0, 16);

                const digits = raw.length;
                let formatted = '';
                for (let i = 0; i < raw.length; i++) {
                    if (i > 0 && i % 4 === 0) formatted += ' ';
                    formatted += raw[i];
                }

                if (digits === 16) {
                    badge.className = 'badge badge-teal';
                    badge.innerHTML = '✓ 16 Digit NIK Valid';
                } else if (digits > 0) {
                    badge.className = 'badge badge-coral';
                    badge.innerHTML = digits + ' / 16 Digit NIK';
                } else {
                    badge.className = 'badge badge-muted';
                    badge.innerHTML = '0 / 16 Digit NIK';
                }
            }

            input.addEventListener('input', formatAndValidateNIK);
            formatAndValidateNIK();
        });
    </script>
</x-app-layout>
