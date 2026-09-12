<x-app-layout>
    <x-slot name="header">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="section-title">Dashboard Kualitas Data</h2>
                    <p class="section-subtitle">KPI eksplisit untuk kelengkapan, rasio verifikasi, dan cakupan per aktor.</p>
                </div>
                <a href="{{ route('admin.data_quality.export') }}" class="btn-secondary">Ekspor CSV</a>
            </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 text-sm">
                <div class="stat-card">
                    <div class="text-slate-500">Total Perawat</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $summary['total_perawat'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-slate-500">Siap Direkrut</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $summary['ready_to_hire'] }}</div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($summary['ready_to_hire_rate'], 2) }}%</div>
                </div>
                <div class="stat-card">
                    <div class="text-slate-500">Rata-rata Kelengkapan</div>
                    <div class="text-lg font-semibold text-slate-900">{{ number_format($summary['avg_completeness_rate'], 2) }}%</div>
                </div>
                <div class="stat-card">
                    <div class="text-slate-500">Rata-rata Rasio Verifikasi</div>
                    <div class="text-lg font-semibold text-slate-900">{{ number_format($summary['avg_verified_rate'], 2) }}%</div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">KPI Kelengkapan</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Terisi</th>
                            <th>Belum Terisi</th>
                            <th>Rasio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($completeness as $row)
                            <tr>
                                <td>
                                    <div class="font-medium text-slate-800">{{ $row['label'] }}</div>
                                    <div class="mt-1 h-2.5 w-full rounded-full bg-slate-100">
                                        <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ min(100, max(0, $row['rate'])) }}%;"></div>
                                    </div>
                                </td>
                                <td>{{ $row['filled'] }} / {{ $row['population'] }}</td>
                                <td>{{ $row['missing'] }}</td>
                                <td>{{ number_format($row['rate'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">KPI Rasio Verifikasi</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Terverifikasi</th>
                            <th>Menunggu</th>
                            <th>Ditolak</th>
                            <th>Total</th>
                            <th>Rasio Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($verification as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['verified'] }}</td>
                                <td>{{ $row['pending'] }}</td>
                                <td>{{ $row['rejected'] }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ number_format($row['rate'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Cakupan per Aktor</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aktor</th>
                            <th>Total Data</th>
                            <th>Perawat Tercover</th>
                            <th>Rasio Cakupan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($actorCoverage as $row)
                            <tr>
                                <td>
                                    <div class="font-medium text-slate-800">{{ $row['label'] }}</div>
                                    <div class="mt-1 h-2.5 w-full rounded-full bg-slate-100">
                                        <div style="height:0.625rem; border-radius:9999px; background:linear-gradient(90deg,#0f5050,#e05252);" style="width: {{ min(100, max(0, $row['coverage_rate'])) }}%;"></div>
                                    </div>
                                </td>
                                <td>{{ $row['records'] }}</td>
                                <td>{{ $row['covered_perawat'] }} / {{ $summary['total_perawat'] }}</td>
                                <td>{{ number_format($row['coverage_rate'], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
