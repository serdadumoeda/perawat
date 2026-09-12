<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Organisasi Regulator</h2>
            <p class="section-subtitle">Kelola instansi regulator untuk governance legalitas.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="flex justify-end">
                <a href="{{ route('admin.regulator_org.create') }}" class="btn-primary">Tambah Organisasi Regulator</a>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                @if (session('status'))
                    <div class="alert alert-success mb-4">{{ session('status') }}</div>
                @endif
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kode</th>
                                <th>Tingkat</th>
                                <th>Wilayah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($organisasi as $item)
                                <tr>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->kode ?? '-' }}</td>
                                    <td>{{ $item->tingkat ?? '-' }}</td>
                                    <td>{{ $item->wilayah ?? '-' }}</td>
                                    <td>
                                        @if ($item->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-muted">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $organisasi->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
