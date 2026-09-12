<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Analitik Regulator</h2>
            <p class="section-subtitle">Ringkasan tren kebutuhan tenaga kerja dan legalitas perawat.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat-card">
                    <div class="text-xs text-slate-500">Total Perawat</div>
                    <div class="text-2xl font-semibold text-slate-900">{{ $summary['total_perawat'] }}</div>
                    <div class="text-xs text-slate-500 mt-1">Siap direkrut: {{ $summary['ready_to_hire'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-xs text-slate-500">Lowongan Dibuka</div>
                    <div class="text-2xl font-semibold text-slate-900">{{ $summary['lowongan_open'] }}</div>
                    <div class="text-xs text-slate-500 mt-1">
                        Domestik: {{ $summary['lowongan_domestik'] }} • Luar Negeri: {{ $summary['lowongan_luar_negeri'] }}
                    </div>
                </div>
                <div class="stat-card">
                    <div class="text-xs text-slate-500">Gap Kebutuhan</div>
                    <div class="text-2xl font-semibold text-slate-900">{{ $summary['gap'] }}</div>
                    <div class="text-xs text-slate-500 mt-1">Rasio pasok/kebutuhan: {{ $summary['ratio'] ?? '-' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat-card">
                    <div class="text-xs text-slate-500">Status STR</div>
                    <div class="text-sm mt-2 text-slate-700">Aktif: {{ $summary['str_aktif'] }}</div>
                    <div class="text-sm text-slate-700">Ditahan: {{ $summary['str_hold'] }}</div>
                    <div class="text-sm text-slate-700">Kedaluwarsa: {{ $summary['str_expired'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-xs text-slate-500">Status Ketenagakerjaan</div>
                    <div class="text-sm mt-2 text-slate-700">Lulusan Baru: {{ $summary['fresh'] }}</div>
                    <div class="text-sm text-slate-700">Berpengalaman: {{ $summary['berpengalaman'] }}</div>
                    <div class="text-sm text-slate-700">Bekerja: {{ $summary['bekerja'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-xs text-slate-500">Catatan</div>
                    <div class="text-sm mt-2 text-slate-700">
                        Kebutuhan tenaga kerja dihitung dari lowongan aktif vs perawat siap direkrut.
                    </div>
                </div>
            </div>

            @php
                $trendLabels = [];
                $trendSeries = [
                    'Lowongan' => [],
                    'Lamaran' => [],
                    'Direkrut' => [],
                    'Lulusan' => [],
                    'Sertifikasi' => [],
                ];
                foreach ($monthKeys as $key) {
                    $trendLabels[] = $monthLabels[$key];
                    $trendSeries['Lowongan'][] = $trends['lowongan'][$key] ?? 0;
                    $trendSeries['Lamaran'][] = $trends['lamaran'][$key] ?? 0;
                    $trendSeries['Direkrut'][] = $trends['hired'][$key] ?? 0;
                    $trendSeries['Lulusan'][] = $trends['lulusan'][$key] ?? 0;
                    $trendSeries['Sertifikasi'][] = $trends['sertifikasi'][$key] ?? 0;
                }
            @endphp

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-4">Grafik Ringkas</div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-slate-600 mb-2">Tren 6 Bulan</div>
                        <div class="relative rounded-2xl border border-slate-200 bg-white p-3">
                            <canvas id="trendChart" class="w-full h-64"></canvas>
                            <div id="trendTooltip" class="pointer-events-none absolute hidden bg-slate-900 text-white text-xs px-2 py-1 rounded"></div>
                        </div>
                        <div id="trendLegend" class="mt-2 flex flex-wrap gap-3 text-xs text-slate-700"></div>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                            <button id="trendExport" type="button" class="btn-secondary text-xs px-3 py-1.5">Ekspor PNG</button>
                            <span>Klik legenda untuk sembunyikan/tampilkan seri.</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-600 mb-2">Pasok vs Kebutuhan</div>
                        <div class="relative rounded-2xl border border-slate-200 bg-white p-3">
                            <canvas id="supplyDemandChart" class="w-full h-64"></canvas>
                            <div id="supplyTooltip" class="pointer-events-none absolute hidden bg-slate-900 text-white text-xs px-2 py-1 rounded"></div>
                        </div>
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                            <button id="supplyExport" type="button" class="btn-secondary text-xs px-3 py-1.5">Ekspor PNG</button>
                            <span>Siap direkrut dibanding lowongan aktif.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-3">Tren 6 Bulan Terakhir</div>
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Lowongan</th>
                                <th>Lamaran</th>
                                <th>Direkrut</th>
                                <th>Lulusan</th>
                                <th>Sertifikasi Terverifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthKeys as $key)
                                <tr>
                                    <td>{{ $monthLabels[$key] }}</td>
                                    <td>{{ $trends['lowongan'][$key] ?? 0 }}</td>
                                    <td>{{ $trends['lamaran'][$key] ?? 0 }}</td>
                                    <td>{{ $trends['hired'][$key] ?? 0 }}</td>
                                    <td>{{ $trends['lulusan'][$key] ?? 0 }}</td>
                                    <td>{{ $trends['sertifikasi'][$key] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="panel-title mb-3">Top Provinsi Lowongan</div>
                    @forelse ($topLowonganProvinsi as $name => $count)
                        <div class="flex justify-between text-sm py-1 text-slate-700">
                            <span>{{ $name }}</span>
                            <span class="text-slate-500">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada data.</div>
                    @endforelse
                </div>
                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="panel-title mb-3">Top Provinsi Perawat</div>
                    @forelse ($topPerawatProvinsi as $name => $count)
                        <div class="flex justify-between text-sm py-1 text-slate-700">
                            <span>{{ $name }}</span>
                            <span class="text-slate-500">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada data.</div>
                    @endforelse
                </div>
                <div class="module-card card-surface p-4 sm:p-6">
                    <div class="panel-title mb-3">Kebutuhan Sertifikat (Top)</div>
                    @forelse ($topSertifikat as $name => $count)
                        <div class="flex justify-between text-sm py-1 text-slate-700">
                            <span>{{ $name }}</span>
                            <span class="text-slate-500">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada data.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const trendCanvas = document.getElementById('trendChart');
            const supplyCanvas = document.getElementById('supplyDemandChart');
            const trendLegend = document.getElementById('trendLegend');
            const trendTooltip = document.getElementById('trendTooltip');
            const supplyTooltip = document.getElementById('supplyTooltip');
            const trendExport = document.getElementById('trendExport');
            const supplyExport = document.getElementById('supplyExport');
            if (!trendCanvas || !supplyCanvas || !trendLegend || !trendTooltip || !supplyTooltip) return;

            const trendLabels = @json($trendLabels);
            const trendSeries = @json($trendSeries);
            const colors = ['#0284c7', '#7c3aed', '#f59e0b', '#10b981', '#f43f5e'];
            const seriesKeys = Object.keys(trendSeries);
            const seriesVisible = {};
            seriesKeys.forEach((key) => { seriesVisible[key] = true; });

            function prepareCanvas(canvas) {
                const dpr = window.devicePixelRatio || 1;
                const rect = canvas.getBoundingClientRect();
                const width = rect.width || 640;
                const height = rect.height || 260;
                canvas.width = width * dpr;
                canvas.height = height * dpr;
                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);
                return { ctx, width, height };
            }

            function drawLineChart(canvas, labels, series, visible, hoverIndex) {
                const { ctx, width, height } = prepareCanvas(canvas);
                const padding = 28;
                const innerW = width - padding * 2;
                const innerH = height - padding * 2;

                const values = Object.entries(series)
                    .filter(([key]) => visible[key])
                    .flatMap(([, data]) => data);
                const maxVal = Math.max(1, ...values);

                ctx.clearRect(0, 0, width, height);
                ctx.strokeStyle = '#e5e7eb';
                ctx.lineWidth = 1;
                for (let i = 0; i <= 4; i++) {
                    const y = padding + (innerH * i / 4);
                    ctx.beginPath();
                    ctx.moveTo(padding, y);
                    ctx.lineTo(width - padding, y);
                    ctx.stroke();
                }

                ctx.strokeStyle = '#9ca3af';
                ctx.beginPath();
                ctx.moveTo(padding, padding);
                ctx.lineTo(padding, height - padding);
                ctx.lineTo(width - padding, height - padding);
                ctx.stroke();

                const stepX = labels.length > 1 ? innerW / (labels.length - 1) : innerW;
                const points = {};

                let idx = 0;
                for (const [name, data] of Object.entries(series)) {
                    if (!visible[name]) {
                        idx++;
                        continue;
                    }
                    ctx.strokeStyle = colors[idx % colors.length];
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    data.forEach((val, i) => {
                        const x = padding + stepX * i;
                        const y = padding + innerH - (val / maxVal) * innerH;
                        if (!points[name]) points[name] = [];
                        points[name].push({ x, y, val, i });
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    });
                    ctx.stroke();
                    idx++;
                }

                if (hoverIndex !== null) {
                    ctx.strokeStyle = '#94a3b8';
                    ctx.setLineDash([4, 4]);
                    ctx.beginPath();
                    ctx.moveTo(padding + stepX * hoverIndex, padding);
                    ctx.lineTo(padding + stepX * hoverIndex, height - padding);
                    ctx.stroke();
                    ctx.setLineDash([]);
                }

                return { stepX, points, padding, innerH, maxVal };
            }

            function drawBarChart(canvas, labels, values, hoverIndex) {
                const { ctx, width, height } = prepareCanvas(canvas);
                const padding = 28;
                const innerW = width - padding * 2;
                const innerH = height - padding * 2;
                const maxVal = Math.max(1, ...values);

                ctx.clearRect(0, 0, width, height);
                ctx.strokeStyle = '#e5e7eb';
                ctx.lineWidth = 1;
                for (let i = 0; i <= 4; i++) {
                    const y = padding + (innerH * i / 4);
                    ctx.beginPath();
                    ctx.moveTo(padding, y);
                    ctx.lineTo(width - padding, y);
                    ctx.stroke();
                }

                const barWidth = innerW / labels.length * 0.6;
                values.forEach((val, i) => {
                    const x = padding + innerW / labels.length * i + (innerW / labels.length - barWidth) / 2;
                    const y = padding + innerH - (val / maxVal) * innerH;
                    ctx.fillStyle = i === hoverIndex ? '#0369a1' : '#0284c7';
                    ctx.fillRect(x, y, barWidth, padding + innerH - y);
                });
            }

            function buildLegend() {
                trendLegend.innerHTML = '';
                seriesKeys.forEach((key, index) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold';
                    item.innerHTML = `<span class="inline-block h-2 w-2 rounded-full" style="background:${colors[index % colors.length]}"></span>${key}`;
                    item.style.opacity = seriesVisible[key] ? '1' : '0.4';
                    item.addEventListener('click', () => {
                        seriesVisible[key] = !seriesVisible[key];
                        renderTrend();
                    });
                    trendLegend.appendChild(item);
                });
            }

            let trendState = { points: {}, stepX: 0, padding: 0 };
            function renderTrend(hoverIndex = null) {
                trendState = drawLineChart(trendCanvas, trendLabels, trendSeries, seriesVisible, hoverIndex);
                buildLegend();
            }

            function renderSupply(hoverIndex = null) {
                const values = [{{ $summary['ready_to_hire'] }}, {{ $summary['lowongan_open'] }}];
                const labels = ['Siap Direkrut', 'Lowongan'];
                drawBarChart(supplyCanvas, labels, values, hoverIndex);
            }

            let hoverIndexTrend = null;
            trendCanvas.addEventListener('mousemove', (event) => {
                const rect = trendCanvas.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const idx = Math.round((x - trendState.padding) / trendState.stepX);
                if (Number.isNaN(idx) || idx < 0 || idx >= trendLabels.length) {
                    trendTooltip.classList.add('hidden');
                    hoverIndexTrend = null;
                    renderTrend(null);
                    return;
                }
                hoverIndexTrend = idx;
                renderTrend(idx);
                const label = trendLabels[idx];
                const values = seriesKeys
                    .filter((key) => seriesVisible[key])
                    .map((key) => `${key}: ${trendSeries[key][idx]}`)
                    .join('<br>');
                trendTooltip.innerHTML = `<strong>${label}</strong><br>${values}`;
                trendTooltip.style.left = `${x + 12}px`;
                trendTooltip.style.top = `${event.clientY - rect.top + 12}px`;
                trendTooltip.classList.remove('hidden');
            });

            trendCanvas.addEventListener('mouseleave', () => {
                hoverIndexTrend = null;
                renderTrend(null);
                trendTooltip.classList.add('hidden');
            });

            supplyCanvas.addEventListener('mousemove', (event) => {
                const rect = supplyCanvas.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const bar = Math.floor(x / (rect.width / 2));
                renderSupply(bar);
                supplyTooltip.innerHTML = bar === 0 ? 'Siap Direkrut' : 'Lowongan Aktif';
                supplyTooltip.style.left = `${x + 12}px`;
                supplyTooltip.style.top = `${event.clientY - rect.top + 12}px`;
                supplyTooltip.classList.remove('hidden');
            });

            supplyCanvas.addEventListener('mouseleave', () => {
                renderSupply(null);
                supplyTooltip.classList.add('hidden');
            });

            trendExport?.addEventListener('click', () => {
                const link = document.createElement('a');
                link.download = 'trend-analytics.png';
                link.href = trendCanvas.toDataURL('image/png');
                link.click();
            });

            supplyExport?.addEventListener('click', () => {
                const link = document.createElement('a');
                link.download = 'supply-demand.png';
                link.href = supplyCanvas.toDataURL('image/png');
                link.click();
            });

            renderTrend();
            renderSupply();
        })();
    </script>
</x-app-layout>
