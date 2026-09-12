<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
            <div>
                <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Bank Data Alumni &amp; Lulusan</div>
                <h2 style="font-family:'Playfair Display',serif; font-size:1.625rem; font-weight:700; color:#1a1a1a; margin:0;">Dashboard Forecasting &amp; Supply vs Demand</h2>
                <p style="font-size:0.875rem; color:#9a9a9a; margin:0.25rem 0 0;">Analisis ketersediaan lulusan keperawatan, proyeksi 3 tahun ke depan, dan pemetaan kebutuhan pasar Faskes.</p>
            </div>
            <span class="badge badge-teal">Updated: {{ $analytics['generated_at'] }}</span>
        </div>
    </x-slot>

    <div class="module-shell module-shell-compact">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 module-container" style="display:flex; flex-direction:column; gap:1.75rem;">

            {{-- ===== INSTITUSI FILTER ===== --}}
            @if ($institusiList->count() > 0)
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1rem; padding:1.25rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                    <form method="GET" action="{{ route('kampus.forecasting') }}" style="display:flex; align-items:center; gap:0.875rem; flex:1;">
                        <x-input-label for="institusi_id" :value="__('Pilih Institusi Kampus:')" style="font-weight:700; color:#1a1a1a; margin:0; white-space:nowrap;" />
                        <select id="institusi_id" name="institusi_id" class="select-field" style="max-width:24rem;" onchange="this.form.submit()">
                            @foreach ($institusiList as $inst)
                                <option value="{{ $inst->id }}" @selected($selectedId === $inst->id)>{{ $inst->nama }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div style="font-size:0.8rem; color:#9a9a9a;">
                        Filter Aktif: <strong style="color:#0f5050;">{{ $analytics['institusi']?->nama ?? 'Seluruh Bank Data Ecosystem' }}</strong>
                    </div>
                </div>
            @endif

            {{-- ===== 1. SUMMARY STATS (PALM & COAST STYLE) ===== --}}
            <div style="display:grid; gap:1.25rem;" class="lg:grid-cols-4 sm:grid-cols-2">
                <!-- Total Supply -->
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; border-top:3px solid #0f5050; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#9a9a9a;">Total Supply Lulusan</div>
                        <span class="badge badge-teal">Bank Data</span>
                    </div>
                    <div style="font-family:'Playfair Display',serif; font-size:2.25rem; font-weight:700; color:#0f5050;">
                        {{ number_format($analytics['summary']['total_supply']) }}
                    </div>
                    <div style="font-size:0.75rem; color:#5a5a5a; margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">
                        Bekerja: <strong style="color:#065f46;">{{ $analytics['summary']['bekerja'] }}</strong> &middot; Belum: <strong style="color:#92400e;">{{ $analytics['summary']['belum_bekerja'] }}</strong>
                    </div>
                </div>

                <!-- Total Demand -->
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; border-top:3px solid #e05252; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#9a9a9a;">Demand Pasar &amp; Faskes</div>
                        <span class="badge badge-coral">High Demand</span>
                    </div>
                    <div style="font-family:'Playfair Display',serif; font-size:2.25rem; font-weight:700; color:#e05252;">
                        {{ number_format($analytics['summary']['total_demand']) }}
                    </div>
                    <div style="font-size:0.75rem; color:#5a5a5a; margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">
                        Kebutuhan Kuota Lowongan Terdaftar
                    </div>
                </div>

                <!-- Absorption Rate -->
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; border-top:3px solid #065f46; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#9a9a9a;">Keterserapan Alumni</div>
                        <span class="badge badge-success">Target &gt; 70%</span>
                    </div>
                    <div style="font-family:'Playfair Display',serif; font-size:2.25rem; font-weight:700; color:#065f46;">
                        {{ $analytics['summary']['absorption_rate'] }}%
                    </div>
                    <div style="font-size:0.75rem; color:#5a5a5a; margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">
                        Status Bekerja Pasca-Kelulusan
                    </div>
                </div>

                <!-- Masa Tunggu Kerja -->
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; border-top:3px solid #d97706; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#9a9a9a;">Rata-Rata Masa Tunggu</div>
                        <span class="badge badge-warning">Cepat</span>
                    </div>
                    <div style="font-family:'Playfair Display',serif; font-size:2.25rem; font-weight:700; color:#d97706;">
                        {{ $analytics['summary']['avg_wait_months'] }} <span style="font-size:1.125rem; font-weight:600;">Bulan</span>
                    </div>
                    <div style="font-size:0.75rem; color:#5a5a5a; margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">
                        Rata-Rata Mendapatkan Kerja Pertama
                    </div>
                </div>
            </div>

            {{-- ===== 2. FORECASTING & PROYEKSI 3 TAHUN ===== --}}
            <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:2rem; box-shadow:0 8px 30px rgba(0,0,0,0.04);">
                <div style="margin-bottom:1.5rem; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
                    <div>
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.25rem;">Proyeksi 3 Tahun Ke Depan (2026 - 2028)</div>
                        <h3 style="font-family:'Playfair Display',serif; font-size:1.375rem; font-weight:700; color:#1a1a1a; margin:0;">Forecasting Pertumbuhan Supply vs Demand Perawat</h3>
                    </div>
                    <div style="font-size:0.75rem; color:#9a9a9a;">
                        Formula: Pertumbuhan Lulusan (+12%/thn) vs Kebutuhan Faskes (+18%/thn)
                    </div>
                </div>

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tahun Proyeksi</th>
                                <th>Proyeksi Supply (Lulusan Baru)</th>
                                <th>Proyeksi Demand (Kebutuhan RS/Faskes)</th>
                                <th>Proyeksi Gap (Supply vs Demand)</th>
                                <th>Status Proyeksi Pasar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($analytics['forecasting'] as $fc)
                                <tr>
                                    <td>
                                        <strong style="font-size:1rem; color:#1a1a1a;">{{ $fc['tahun'] }}</strong>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:0.75rem;">
                                            <strong style="color:#0f5050; font-size:1rem;">{{ number_format($fc['proyeksi_supply']) }}</strong>
                                            <span style="font-size:0.7rem; color:#9a9a9a;">Lulusan</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:0.75rem;">
                                            <strong style="color:#e05252; font-size:1rem;">{{ number_format($fc['proyeksi_demand']) }}</strong>
                                            <span style="font-size:0.7rem; color:#9a9a9a;">Perawat</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="height:0.5rem; border-radius:9999px; background:#f0e5cf; min-width:8rem; overflow:hidden; margin-bottom:0.25rem;">
                                            <div style="height:100%; border-radius:9999px; background:linear-gradient(90deg, #0f5050, #e05252); width:{{ min(100, round(($fc['proyeksi_supply'] / max(1, $fc['proyeksi_demand'])) * 100)) }}%;"></div>
                                        </div>
                                        <span style="font-size:0.75rem; font-weight:700; color:{{ $fc['gap'] > 0 ? '#e05252' : '#065f46' }};">
                                            {{ $fc['gap'] > 0 ? 'Defisit ' . abs($fc['gap']) . ' Perawat' : 'Surplus ' . abs($fc['gap']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $fc['gap'] > 0 ? 'badge-coral' : 'badge-teal' }}">
                                            {{ $fc['status_gap'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== 3. DISTRIBUTION BREAKDOWNS (3 COLUMNS) ===== --}}
            <div style="display:grid; gap:1.5rem;" class="lg:grid-cols-3 sm:grid-cols-1">

                <!-- Supply by Jenjang -->
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
                    <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Distribusi Supply</div>
                    <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#1a1a1a; margin-bottom:1rem;">Lulusan by Jenjang Studi</div>

                    <div style="display:flex; flex-direction:column; gap:0.875rem;">
                        @foreach ($analytics['supply_by_jenjang'] as $jenjang => $total)
                            @php($pct = $analytics['summary']['total_supply'] > 0 ? round(($total / $analytics['summary']['total_supply']) * 100, 1) : 50)
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:0.8125rem; margin-bottom:0.25rem;">
                                    <span style="font-weight:600; color:#1a1a1a;">{{ $jenjang }}</span>
                                    <strong style="color:#0f5050;">{{ number_format($total) }} ({{ $pct }}%)</strong>
                                </div>
                                <div style="height:0.4rem; border-radius:9999px; background:#f0e5cf; overflow:hidden;">
                                    <div style="height:100%; background:#0f5050; border-radius:9999px; width:{{ $pct }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Supply by Cohort -->
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
                    <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Tren Angkatan</div>
                    <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#1a1a1a; margin-bottom:1rem;">Lulusan by Cohort / Angkatan</div>

                    <div style="display:flex; flex-direction:column; gap:0.875rem;">
                        @foreach ($analytics['supply_by_cohort'] as $tahun => $total)
                            @php($pct = $analytics['summary']['total_supply'] > 0 ? min(100, round(($total / $analytics['summary']['total_supply']) * 100, 1)) : 30)
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:0.8125rem; margin-bottom:0.25rem;">
                                    <span style="font-weight:600; color:#1a1a1a;">Angkatan {{ $tahun }}</span>
                                    <strong style="color:#e05252;">{{ number_format($total) }} Alumni</strong>
                                </div>
                                <div style="height:0.4rem; border-radius:9999px; background:#f0e5cf; overflow:hidden;">
                                    <div style="height:100%; background:#e05252; border-radius:9999px; width:{{ $pct }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Demand by Specialty -->
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:1.5rem; box-shadow:0 6px 24px rgba(0,0,0,0.03);">
                    <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Kebutuhan Pasar</div>
                    <div style="font-family:'Playfair Display',serif; font-size:1.125rem; font-weight:700; color:#1a1a1a; margin-bottom:1rem;">Demand by Spesialisasi Faskes</div>

                    <div style="display:flex; flex-direction:column; gap:0.875rem;">
                        @foreach ($analytics['demand_by_specialty'] as $spec => $total)
                            @php($pct = $analytics['summary']['total_demand'] > 0 ? round(($total / $analytics['summary']['total_demand']) * 100, 1) : 20)
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:0.8125rem; margin-bottom:0.25rem;">
                                    <span style="font-weight:600; color:#1a1a1a;">{{ $spec }}</span>
                                    <strong style="color:#d97706;">{{ number_format($total) }} Kuota</strong>
                                </div>
                                <div style="height:0.4rem; border-radius:9999px; background:#f0e5cf; overflow:hidden;">
                                    <div style="height:100%; background:#d97706; border-radius:9999px; width:{{ $pct }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- ===== 4. STRATEGIC KAMPUS RECOMMENDATIONS ===== --}}
            <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1.25rem; padding:2rem; box-shadow:0 8px 30px rgba(0,0,0,0.04);">
                <div style="margin-bottom:1.5rem; display:flex; align-items:center; gap:0.625rem;">
                    <div style="width:2.25rem; height:2.25rem; border-radius:0.625rem; background:rgba(224,82,82,0.1); display:flex; align-items:center; justify-content:center; color:#e05252;">
                        <svg style="width:1.25rem; height:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:700; color:#1a1a1a;">Rekomendasi Strategis Manajemen Kampus</div>
                        <div style="font-size:0.8125rem; color:#9a9a9a;">Tindakan strategis untuk mengoptimalkan keterserapan alumni dan penyesuaian kurikulum.</div>
                    </div>
                </div>

                <div style="display:grid; gap:1.25rem;" class="lg:grid-cols-3 sm:grid-cols-1">
                    @foreach ($analytics['recommendations'] as $rec)
                        <div style="padding:1.25rem; border-radius:1rem; background:#fdfaf5; border:1px solid #f0e5cf; border-top:3px solid {{ $rec['color'] }}; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                                    <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; padding:0.25rem 0.625rem; border-radius:9999px; background:rgba(15,80,80,0.08); color:{{ $rec['color'] }};">
                                        {{ $rec['badge'] }}
                                    </span>
                                    <span style="font-size:0.7rem; font-weight:700; color:#9a9a9a;">Prioritas: {{ $rec['priority'] }}</span>
                                </div>
                                <div style="font-size:0.9375rem; font-weight:700; color:#1a1a1a; margin-bottom:0.5rem;">{{ $rec['title'] }}</div>
                                <p style="font-size:0.8125rem; color:#5a5a5a; line-height:1.65; margin:0;">{{ $rec['desc'] }}</p>
                            </div>

                            <div style="margin-top:1.25rem; padding-top:0.75rem; border-top:1px solid #f0e5cf;">
                                <a href="{{ route('kampus.lulusan.index') }}" style="font-size:0.75rem; font-weight:700; color:{{ $rec['color'] }}; text-decoration:none;">
                                    Kelola Data Lulusan &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
