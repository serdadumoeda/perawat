<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Verifikasi Riwayat Pekerjaan</h2>
            <p class="section-subtitle">Validasi pengalaman kerja yang diajukan perawat.</p>
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
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[1100px]">
                        <thead>
                            <tr>
                                <th>Perawat</th>
                                <th>Mitra</th>
                                <th>Posisi</th>
                                <th>Periode</th>
                                <th>Metadata Verifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayat as $item)
                                <tr class="align-top">
                                    <td>{{ $item->perawat?->nama ?? '-' }}</td>
                                    <td>{{ $item->mitraPengguna?->nama ?? '-' }}</td>
                                    <td>{{ $item->posisi }}</td>
                                    <td>
                                        {{ optional($item->tanggal_mulai)->format('d M Y') }}
                                        -
                                        {{ optional($item->tanggal_selesai)->format('d M Y') ?? 'Sekarang' }}
                                    </td>
                                    <td class="min-w-[280px]">
                                        <x-review-metadata
                                            :status="$item->verifikasi_status"
                                            :rejected-reason="$item->rejected_reason"
                                            :verified-by="$item->verifiedBy?->name"
                                            :verified-at="$item->verified_at"
                                            :source-system="$item->source_system"
                                            :source-actor="$item->source_actor"
                                        />
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('mitra.riwayat_pekerjaan.verify', $item) }}" class="mb-3 space-y-2">
                                            @csrf
                                            <input type="text" name="penilaian_kinerja" placeholder="Penilaian (opsional)" class="input-field" />
                                            <input type="text" name="keterangan" placeholder="Catatan (opsional)" class="input-field" />
                                            <button class="table-action table-action-approve" type="submit">{{ __('Setujui') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('mitra.riwayat_pekerjaan.reject', $item) }}" class="space-y-2">
                                            @csrf
                                            <input type="text" name="keterangan" placeholder="Alasan penolakan (opsional)" class="input-field" />
                                            <button type="submit" class="table-action table-action-reject">Tolak</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="6">Tidak ada riwayat pekerjaan yang menunggu verifikasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $riwayat->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
