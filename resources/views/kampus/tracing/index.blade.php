<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Tracing Alumni</h2>
            <p class="section-subtitle">Pantau status karier alumni dan update tracing.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 module-container">
            @if (session('status'))
                <div class="mb-4 alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($institusiList->isEmpty())
                <div class="module-card card-surface p-6">
                    Akun kampus belum terhubung dengan institusi pendidikan. Hubungi admin.
                </div>
            @else
                <div class="module-card card-surface p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <x-input-label for="institusi_id" :value="__('Institusi')" />
                            <select id="institusi_id" name="institusi_id" class="select-field mt-1" required>
                                @foreach ($institusiList as $item)
                                    <option value="{{ $item->id }}" @selected(optional($selectedInstitusi)->id === $item->id)>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="status" :value="__('Status Tracing')" />
                            <select id="status" name="status" class="select-field mt-1">
                                <option value="">Semua</option>
                                @foreach ($statusOptions as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="q" :value="__('Cari NIK/Nama')" />
                            <x-text-input id="q" class="mt-1 w-full" type="text" name="q" :value="request('q')" />
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <x-primary-button>{{ __('Terapkan') }}</x-primary-button>
                        </div>
                    </form>

                    <div class="table-wrap overflow-x-auto">
                        <table class="data-table min-w-[860px]">
                            <thead>
                                <tr>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Status Tracing</th>
                                    <th>Instansi</th>
                                    <th>Posisi</th>
                                    <th>Lokasi</th>
                                    <th>Update</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perawat as $item)
                                    @php($tracing = $item->tracingAlumni->first())
                                    <tr>
                                        <td>{{ $item->nik }}</td>
                                        <td class="font-semibold text-slate-900">{{ $item->nama }}</td>
                                        <td>{{ $tracing?->status ? ucfirst(str_replace('_', ' ', $tracing->status)) : '-' }}</td>
                                        <td>{{ $tracing?->instansi ?? '-' }}</td>
                                        <td>{{ $tracing?->posisi ?? '-' }}</td>
                                        <td>
                                            @php($lokasi = trim(($tracing?->kota ?? '').' '.($tracing?->provinsi ?? '')))
                                            {{ $lokasi !== '' ? $lokasi : '-' }}
                                        </td>
                                        <td>{{ $tracing?->updated_at?->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <a class="btn-ghost" href="{{ route('kampus.tracing.edit', ['perawat' => $item->id, 'institusi_id' => optional($selectedInstitusi)->id]) }}">Update</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="empty-state-row" colspan="8">Belum ada data alumni.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $perawat->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
