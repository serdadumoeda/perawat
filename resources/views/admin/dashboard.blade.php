<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
            <div>
                <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Administrator</div>
                <h2 style="font-family:'Playfair Display',serif; font-size:1.625rem; font-weight:700; color:#1a1a1a; margin:0;">Admin Dashboard</h2>
                <p style="font-size:0.875rem; color:#9a9a9a; margin:0.25rem 0 0;">Monitoring pergerakan data perawat, kualitas data, dan risiko operasional.</p>
            </div>
            <span class="badge badge-teal">System Admin</span>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Filter Rentang Tanggal</div>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        @php($presetRanges = ['7d' => '7 Hari', '30d' => '30 Hari', 'this_month' => 'Bulan Ini', 'custom' => 'Kustom'])
                        @foreach ($presetRanges as $key => $label)
                            <button
                                type="submit"
                                name="range"
                                value="{{ $key }}"
                                class="{{ $rangePreset === $key ? 'btn-primary' : 'btn-ghost' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="text-xs text-slate-500">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ $rangeStart->toDateString() }}" class="input-modern mt-1">
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Tanggal Selesai</label>
                            <input type="date" name="end_date" value="{{ $rangeEnd->toDateString() }}" class="input-modern mt-1">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="range" value="custom" class="btn-secondary w-full">Terapkan Kustom</button>
                        </div>
                        <div class="flex items-end">
                            <a href="{{ route('admin.dashboard', ['range' => '30d']) }}" class="btn-ghost w-full text-center">Atur Ulang</a>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500">
                        Rentang aktif: <span class="font-medium text-slate-700">{{ $rangeLabel }}</span>
                    </div>
                </form>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Laporan & Tools</div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.reports.perawat') }}" class="btn-secondary">Laporan Perawat</a>
                    <a href="{{ route('admin.reports.lowongan') }}" class="btn-secondary">Laporan Lowongan</a>
                    <a href="{{ route('admin.reports.rekrutmen') }}" class="btn-secondary">Laporan Rekrutmen</a>
                    <a href="{{ route('admin.reports.lamaran') }}" class="btn-secondary">Laporan Lamaran</a>
                    <a href="{{ route('admin.reports.legalitas') }}" class="btn-secondary">Laporan Legalitas</a>
                    <a href="{{ route('admin.reports.riwayat_pekerjaan') }}" class="btn-secondary">Laporan Riwayat Kerja</a>
                    <a href="{{ route('admin.reports.audit_logs') }}" class="btn-secondary">Laporan Log Audit</a>
                    <a href="{{ route('admin.data_quality') }}" class="btn-ghost">Kualitas Data</a>
                    <a href="{{ route('admin.audit_logs.index') }}" class="btn-ghost">Log Audit</a>
                    <a href="{{ route('admin.api_keys.index') }}" class="btn-ghost">Kunci API</a>
                    <a href="{{ route('admin.regulator_org.index') }}" class="btn-ghost">Organisasi Regulator</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1rem; padding:1.25rem; border-top:3px solid #0f5050;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#9a9a9a;">Total Perawat</div>
                        <span class="badge badge-teal">Database</span>
                    </div>
                    <div style="font-family:'Playfair Display',serif; font-size:2rem; font-weight:700; color:#0f5050;">{{ $stats['perawat_total'] }}</div>
                    <div style="font-size:0.75rem; color:#9a9a9a; margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">
                        Siap: <strong style="color:#1a1a1a;">{{ $stats['perawat_ready'] }}</strong> &nbsp;·&nbsp; Fresh: <strong style="color:#1a1a1a;">{{ $stats['perawat_fresh'] }}</strong>
                    </div>
                </div>
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1rem; padding:1.25rem; border-top:3px solid #d97706;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#9a9a9a;">Status Legalitas STR</div>
                        <span class="badge badge-warning">Regulator</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.375rem; font-size:0.75rem;">
                        <div style="display:flex; justify-content:space-between;"><span style="color:#5a5a5a;">Aktif:</span> <strong style="color:#065f46;">{{ $stats['str_aktif'] }}</strong></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#5a5a5a;">Ditahan:</span> <strong style="color:#92400e;">{{ $stats['str_hold'] }}</strong></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#5a5a5a;">Kedaluwarsa:</span> <strong style="color:#be123c;">{{ $stats['str_expired'] }}</strong></div>
                    </div>
                    <div style="font-size:0.7rem; font-weight:700; color:#92400e; margin-top:0.625rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">Kedaluwarsa ≤ 30 hari: {{ $stats['str_expiring_30'] }}</div>
                </div>
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1rem; padding:1.25rem; border-top:3px solid #e05252;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#9a9a9a;">Antrean Verifikasi</div>
                        <span class="badge badge-danger">Pending</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.375rem; font-size:0.75rem; color:#5a5a5a;">
                        <div style="display:flex; justify-content:space-between;"><span>Legalitas:</span> <strong style="color:#1a1a1a;">{{ $stats['legalitas_pending'] }}</strong></div>
                        <div style="display:flex; justify-content:space-between;"><span>Sertifikasi:</span> <strong style="color:#1a1a1a;">{{ $stats['sertifikasi_pending'] }}</strong></div>
                        <div style="display:flex; justify-content:space-between;"><span>Pengalaman:</span> <strong style="color:#1a1a1a;">{{ $stats['riwayat_pending'] }}</strong></div>
                    </div>
                    <div style="font-size:0.7rem; font-weight:700; color:#be123c; margin-top:0.625rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">User Pending Approval: {{ $stats['users_pending'] }}</div>
                </div>
                <div style="background:#ffffff; border:1px solid #f0e5cf; border-radius:1rem; padding:1.25rem; border-top:3px solid #0b3535;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                        <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#9a9a9a;">Rekrutmen & Lowongan</div>
                        <span class="badge badge-teal">Hiring</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.375rem; font-size:0.75rem; color:#5a5a5a;">
                        <div style="display:flex; justify-content:space-between;"><span>Lowongan Dibuka:</span> <strong style="color:#0f5050;">{{ $stats['lowongan_dibuka'] }}</strong></div>
                        <div style="display:flex; justify-content:space-between;"><span>Locked Talent:</span> <strong style="color:#1a1a1a;">{{ $stats['rekrutmen_locked'] }}</strong></div>
                        <div style="display:flex; justify-content:space-between;"><span>Total Hired:</span> <strong style="color:#065f46;">{{ $stats['rekrutmen_hired'] }}</strong></div>
                    </div>
                    <div style="font-size:0.7rem; color:#9a9a9a; margin-top:0.625rem; padding-top:0.5rem; border-top:1px solid #f0e5cf;">Lamaran Diterima: {{ $stats['lamaran_diterima'] }} / {{ $stats['lamaran_diajukan'] }}</div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Pergerakan Data ({{ $rangeLabel }})</div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                    <div class="stat-card">
                        <div class="text-sm text-slate-600">Perawat Baru</div>
                        <div class="text-2xl font-semibold text-slate-900 mt-2">{{ $stats['movement_perawat_baru'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-sm text-slate-600">Legalitas Ditinjau</div>
                        <div class="text-2xl font-semibold text-slate-900 mt-2">{{ $stats['movement_legalitas_reviewed'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-sm text-slate-600">Pengalaman Terverifikasi</div>
                        <div class="text-2xl font-semibold text-slate-900 mt-2">{{ $stats['movement_pengalaman_verified'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-sm text-slate-600">Rekrutmen</div>
                        <div class="text-2xl font-semibold text-slate-900 mt-2">{{ $stats['movement_hired'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-sm text-slate-600">Aktivitas Karier</div>
                        <div class="text-2xl font-semibold text-slate-900 mt-2">{{ $stats['movement_events'] }}</div>
                    </div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Ringkasan Kualitas Data</div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="stat-card">
                        <div class="text-slate-500">Rasio Siap Direkrut</div>
                        <div class="text-xl font-semibold text-slate-900 mt-2">{{ number_format($qualitySummary['ready_to_hire_rate'], 2) }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-slate-500">Rata-rata Kelengkapan</div>
                        <div class="text-xl font-semibold text-slate-900 mt-2">{{ number_format($qualitySummary['avg_completeness_rate'], 2) }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-slate-500">Rata-rata Rasio Verifikasi</div>
                        <div class="text-xl font-semibold text-slate-900 mt-2">{{ number_format($qualitySummary['avg_verified_rate'], 2) }}%</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="panel-title mb-3">STR Akan Habis Masa Berlaku (≤ 30 Hari)</div>
                    <div class="table-wrap overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Perawat</th>
                                    <th>No STR</th>
                                    <th>Berlaku Sampai</th>
                                    <th>Sisa Hari</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($strExpiringSoon as $item)
                                    @php($daysLeft = now()->startOfDay()->diffInDays($item->str_berlaku_sampai, false))
                                    <tr>
                                        <td>
                                            <div class="font-medium text-slate-900">{{ $item->perawat?->nama ?? '-' }}</div>
                                            <div class="text-xs text-slate-500">{{ $item->perawat?->nik ?? '-' }}</div>
                                        </td>
                                        <td>{{ $item->nomor_str ?? '-' }}</td>
                                        <td>{{ optional($item->str_berlaku_sampai)->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $daysLeft <= 7 ? 'badge-danger' : 'badge-warning' }}">
                                                {{ $daysLeft }} hari
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state-row">Tidak ada STR yang akan kedaluwarsa dalam 30 hari.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="panel-title mb-3">STR Sudah Kedaluwarsa (Terbaru)</div>
                    <div class="table-wrap overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Perawat</th>
                                    <th>No STR</th>
                                    <th>Masa Berlaku</th>
                                    <th>Terlambat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($strExpiredRecent as $item)
                                    @php($expiredDays = now()->startOfDay()->diffInDays($item->str_berlaku_sampai, false))
                                    <tr>
                                        <td>
                                            <div class="font-medium text-slate-900">{{ $item->perawat?->nama ?? '-' }}</div>
                                            <div class="text-xs text-slate-500">{{ $item->perawat?->nik ?? '-' }}</div>
                                        </td>
                                        <td>{{ $item->nomor_str ?? '-' }}</td>
                                        <td>{{ optional($item->str_berlaku_mulai)->format('d M Y') ?? '-' }} - {{ optional($item->str_berlaku_sampai)->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-danger">{{ abs($expiredDays) }} hari</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state-row">Belum ada STR kedaluwarsa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Tren Pergerakan Data ({{ $rangeLabel }})</div>
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[1000px]">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pendidikan</th>
                                <th>Sertifikasi</th>
                                <th>Legalitas</th>
                                <th>Pengalaman</th>
                                <th>Total Aktivitas</th>
                                <th>Intensitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trendRows as $row)
                                @php($width = $trendMax > 0 ? round(($row['total'] / $trendMax) * 100, 2) : 0)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $row['pendidikan'] }}</td>
                                    <td>{{ $row['sertifikasi'] }}</td>
                                    <td>{{ $row['legalitas'] }}</td>
                                    <td>{{ $row['pengalaman'] }}</td>
                                    <td>{{ $row['total'] }}</td>
                                    <td>
                                        <div style="height:0.5rem; border-radius:9999px; background:#f0e5cf; min-width:10rem; overflow:hidden;">
                                            <div style="height:100%; border-radius:9999px; background:linear-gradient(90deg,#0f5050,#e05252); width:{{ $width }}%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Aktivitas Data Terbaru ({{ $rangeLabel }})</div>
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[1000px]">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Perawat</th>
                                <th>Kategori</th>
                                <th>Event</th>
                                <th>Status</th>
                                <th>Sumber</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestEvents as $event)
                                <tr>
                                    <td>{{ optional($event->happened_at)->format('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="font-medium text-slate-900">{{ $event->perawat?->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $event->perawat_nik }}</div>
                                    </td>
                                    <td><span class="badge badge-info">{{ strtoupper($event->category) }}</span></td>
                                    <td>
                                        <div class="font-medium text-slate-900">{{ $event->title }}</div>
                                        <div class="text-xs text-slate-500">{{ $event->event_key }}</div>
                                    </td>
                                    <td>{{ $event->status ?? '-' }}</td>
                                    <td>{{ $event->source_actor ?? '-' }} / {{ $event->source_system ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-state-row">Belum ada aktivitas data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
