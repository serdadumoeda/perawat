<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Detail Pelatihan</h2>
            <p class="section-subtitle">Pantau status pelatihan dan kelulusan peserta.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card card-surface p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-2xl font-semibold text-slate-900">{{ $pelatihan->nama }}</div>
                        <div class="text-sm text-slate-600">{{ $pelatihan->lembagaPelatihan?->nama }}</div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="badge {{ $pelatihan->status === \App\Models\Pelatihan::STATUS_DIBUKA ? 'badge-success' : 'badge-warning' }}">{{ $pelatihan->status }}</span>
                            <span class="chip">{{ $pelatihan->jumlah_jam ?? '-' }} JP</span>
                            <span class="chip">{{ $pelatihan->lokasi ?? '-' }}</span>
                        </div>
                        <div class="mt-3 text-sm text-slate-600">
                            Tanggal:
                            @if ($pelatihan->tanggal_mulai)
                                {{ $pelatihan->tanggal_mulai->format('d M Y') }}
                                @if ($pelatihan->tanggal_selesai)
                                    - {{ $pelatihan->tanggal_selesai->format('d M Y') }}
                                @endif
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    @if ($pelatihan->status !== \App\Models\Pelatihan::STATUS_DITUTUP)
                        <form method="POST" action="{{ route('lembaga.pelatihan.close', $pelatihan) }}">
                            @csrf
                            <x-primary-button>{{ __('Tutup Pelatihan') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="module-card card-surface p-6">
                <div class="panel-title mb-4">Peserta</div>
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[720px]">
                        <thead>
                            <tr>
                                <th>Perawat</th>
                                <th>NIK</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pelatihan->peserta as $peserta)
                                <tr class="align-top">
                                    <td class="font-semibold text-slate-900">{{ $peserta->perawat?->nama ?? '-' }}</td>
                                    <td>{{ $peserta->perawat?->nik ?? '-' }}</td>
                                    <td>{{ $peserta->status }}</td>
                                    <td>{{ $peserta->nilai_kompetensi ?? '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('lembaga.pelatihan.peserta.lulus', [$pelatihan, $peserta]) }}" class="mb-3">
                                            @csrf
                                            <input type="number" step="0.01" min="0" max="100" name="nilai_kompetensi" placeholder="Nilai" class="input-field text-sm mb-2" />
                                            <input type="text" name="catatan" placeholder="Catatan (opsional)" class="input-field text-sm mb-2" />
                                            <x-primary-button>{{ __('Lulus & Buat Sertifikat') }}</x-primary-button>
                                        </form>
                                        <form method="POST" action="{{ route('lembaga.pelatihan.peserta.tidak_lulus', [$pelatihan, $peserta]) }}">
                                            @csrf
                                            <input type="text" name="catatan" placeholder="Catatan (opsional)" class="input-field text-sm mb-2" />
                                            <button class="btn-secondary" type="submit">Tidak Lulus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada peserta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
