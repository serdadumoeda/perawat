<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Analitik Tracing Alumni</h2>
            <p class="section-subtitle">Ringkasan sebaran alumni berdasarkan institusi.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="module-card card-surface p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="institusi_id" :value="__('Institusi')" />
                        <select id="institusi_id" name="institusi_id" class="select-field mt-1">
                            @foreach ($institusiList as $institusi)
                                <option value="{{ $institusi->id }}" @selected($selectedInstitusi && $selectedInstitusi->id === $institusi->id)>{{ $institusi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>{{ __('Lihat') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="stat-card">
                    <div class="text-slate-500">Total Alumni</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $summary['total_alumni'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-slate-500">Sudah Tracing</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $summary['traced'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="text-slate-500">Belum Tracing</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $summary['not_traced'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="module-card card-surface p-6">
                    <div class="panel-title mb-3">Status Tracing</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($statusCounts as $status => $total)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="2">Belum ada data tracing.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="module-card card-surface p-6">
                    <div class="panel-title mb-3">Top Instansi</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Instansi</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topInstansi as $row)
                                <tr>
                                    <td>{{ $row->instansi }}</td>
                                    <td>{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="2">Belum ada data instansi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="module-card card-surface p-6">
                    <div class="panel-title mb-3">Top Kota</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kota</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topKota as $row)
                                <tr>
                                    <td>{{ $row->kota }}</td>
                                    <td>{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="2">Belum ada data kota.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="module-card card-surface p-6">
                    <div class="panel-title mb-3">Top Provinsi</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Provinsi</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProvinsi as $row)
                                <tr>
                                    <td>{{ $row->provinsi }}</td>
                                    <td>{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="2">Belum ada data provinsi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
