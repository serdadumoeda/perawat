<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Mitra Pengguna</h2>
            <p class="section-subtitle">Kelola data rumah sakit, agency, dan mitra pengguna lainnya.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="flex justify-end">
                <a href="{{ route('admin.mitra.create') }}" class="btn-primary">Tambah Mitra</a>
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
                                <th>Kategori</th>
                                <th>Kota</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mitra as $item)
                                <tr>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->kategori ?? '-' }}</td>
                                    <td>{{ $item->kota }}</td>
                                    <td>{{ $item->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="4">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $mitra->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
