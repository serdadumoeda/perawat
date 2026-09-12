<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Katalog Pelatihan</h2>
                <p class="section-subtitle">Daftar pelatihan yang sedang dibuka.</p>
            </div>
            <a class="btn-secondary" href="{{ route('perawat.pelatihan.mine') }}">Pelatihan Saya</a>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="q" :value="__('Cari Pelatihan')" />
                        <x-text-input id="q" class="mt-1 w-full" type="text" name="q" :value="request('q')" />
                    </div>
                    <div>
                        <x-input-label for="lembaga" :value="__('Lembaga')" />
                        <x-text-input id="lembaga" class="mt-1 w-full" type="text" name="lembaga" :value="request('lembaga')" />
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>{{ __('Cari') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 alert alert-success">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 alert alert-danger">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[720px]">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Lembaga</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pelatihan as $item)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $item->nama }}</td>
                                    <td>{{ $item->lembagaPelatihan?->nama }}</td>
                                    <td>
                                        @if ($item->tanggal_mulai)
                                            {{ $item->tanggal_mulai->format('d M Y') }}
                                            @if ($item->tanggal_selesai)
                                                - {{ $item->tanggal_selesai->format('d M Y') }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->jumlah_jam ?? '-' }}</td>
                                    <td>{{ $item->lokasi ?? '-' }}</td>
                                    <td>
                                        @if (isset($enrolled[$item->id]))
                                            <span class="badge badge-success">Terdaftar ({{ $enrolled[$item->id] }})</span>
                                        @else
                                            <form method="POST" action="{{ route('perawat.pelatihan.daftar') }}">
                                                @csrf
                                                <input type="hidden" name="pelatihan_id" value="{{ $item->id }}">
                                                <x-primary-button>{{ __('Daftar') }}</x-primary-button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="6">Belum ada pelatihan dibuka.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $pelatihan->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
