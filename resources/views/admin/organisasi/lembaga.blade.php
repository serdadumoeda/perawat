<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Lembaga Pelatihan</h2>
            <p class="section-subtitle">Kelola daftar lembaga pelatihan & sertifikasi.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="flex justify-end">
                <a href="{{ route('admin.lembaga.create') }}" class="btn-primary">Tambah Lembaga</a>
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
                                <th>Akreditasi</th>
                                <th>Kota</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lembaga as $item)
                                <tr>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->akreditasi ?? '-' }}</td>
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
                <div class="mt-6">{{ $lembaga->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
