<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Detail Lowongan</h2>
                <p class="section-subtitle">Pantau detail lowongan dan lamaran masuk.</p>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <a href="{{ route('mitra.lowongan.edit', $lowongan) }}" class="btn-secondary">Edit</a>
                @if($lowongan->status === 'dibuka')
                    <form method="POST" action="{{ route('mitra.lowongan.close', $lowongan) }}" onsubmit="return confirm('Apakah Anda yakin ingin menutup lowongan ini?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-600 hover:bg-amber-700 px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 shadow-sm">Tutup Lowongan</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('mitra.lowongan.reopen', $lowongan) }}">
                        @csrf
                        <button type="submit" class="btn-primary">Buka Kembali</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('mitra.lowongan.destroy', $lowongan) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 shadow-sm">Hapus</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
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

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="text-2xl font-semibold text-slate-900">{{ $lowongan->judul }}</div>
                <div class="text-sm text-slate-600">Mitra: {{ $lowongan->mitraPengguna?->nama ?? '-' }}</div>
                <div class="text-sm text-slate-600">
                    Penempatan:
                    {{ $lowongan->tipe_penempatan === \App\Models\Lowongan::PENEMPATAN_LUAR_NEGERI ? 'Luar Negeri' : 'Domestik' }}
                    @if ($lowongan->negara_penempatan)
                        • {{ $lowongan->negara_penempatan }}
                    @endif
                    @php($lokasi = trim(($lowongan->kota_penempatan ?? '').' '.($lowongan->provinsi_penempatan ?? '')))
                    @if ($lokasi !== '')
                        • {{ $lokasi }}
                    @endif
                </div>
                <div class="mt-4 whitespace-pre-line text-sm text-slate-700">{{ $lowongan->deskripsi }}</div>
                <div class="mt-4">
                    <div class="panel-title">Kriteria</div>
                    @php($kriteria = $lowongan->kriteria ?? [])
                    <div class="text-sm text-slate-600">Status STR: {{ $kriteria['status_str'] ?? '-' }}</div>
                    <div class="text-sm text-slate-600">Sertifikat: {{ $kriteria['sertifikat'] ?? '-' }}</div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="panel-title mb-4">Lamaran Masuk</div>

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[640px]">
                        <thead>
                            <tr>
                                <th>Perawat</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($mitraId = $lowongan->mitra_pengguna_id)
                            @forelse ($lamaran as $item)
                                <tr>
                                    <td class="font-semibold text-slate-900">{{ $item->perawat?->nama ?? '-' }}</td>
                                    <td>{{ $item->status }}</td>
                                    <td>{{ $item->catatan ?? '-' }}</td>
                                    <td>
                                        <div class="table-action-group">
                                            <a class="btn-ghost" href="{{ route('mitra.kandidat.show', $item->perawat?->id) }}">Lihat Profil</a>
                                            @if ($mitraId && !in_array($item->status, [\App\Models\Lamaran::STATUS_DITERIMA, \App\Models\Lamaran::STATUS_DITOLAK, \App\Models\Lamaran::STATUS_DIBATALKAN], true))
                                                <form method="POST" action="{{ route('mitra.lamaran.process') }}">
                                                    @csrf
                                                    <input type="hidden" name="lamaran_id" value="{{ $item->id }}">
                                                    <input type="hidden" name="mitra_pengguna_id" value="{{ $mitraId }}">
                                                    <button class="table-action table-action-secondary" type="submit">{{ __('Kunci') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('mitra.lamaran.reject') }}">
                                                    @csrf
                                                    <input type="hidden" name="lamaran_id" value="{{ $item->id }}">
                                                    <input type="hidden" name="mitra_pengguna_id" value="{{ $mitraId }}">
                                                    <button class="table-action table-action-reject" type="submit">Tolak</button>
                                                </form>
                                                <form method="POST" action="{{ route('mitra.lamaran.hire') }}">
                                                    @csrf
                                                    <input type="hidden" name="lamaran_id" value="{{ $item->id }}">
                                                    <input type="hidden" name="mitra_pengguna_id" value="{{ $mitraId }}">
                                                    <button class="table-action table-action-approve" type="submit">{{ __('Setujui') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="4">Belum ada lamaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $lamaran->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
