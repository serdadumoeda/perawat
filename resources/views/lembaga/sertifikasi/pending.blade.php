<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Verifikasi Sertifikat</h2>
            <p class="section-subtitle">Tinjau sertifikat yang menunggu verifikasi.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 module-container">
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

            <div class="module-card card-surface p-6">
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[1180px]">
                        <thead>
                            <tr>
                                <th>Perawat</th>
                                <th>Sertifikat</th>
                                <th>Nomor</th>
                                <th>Tanggal</th>
                                <th>Jam/Nilai</th>
                                <th>File</th>
                                <th>Metadata Review</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sertifikasi as $item)
                                <tr class="align-top">
                                    <td class="font-semibold text-slate-900">{{ $item->perawat?->nama ?? '-' }}</td>
                                    <td>{{ $item->nama_sertifikat }}</td>
                                    <td>{{ $item->nomor_sertifikat ?? '-' }}</td>
                                    <td>
                                        {{ optional($item->tanggal_terbit)->format('d M Y') ?? '-' }}
                                        @if ($item->tanggal_kadaluarsa)
                                            <div class="text-xs text-slate-500">Kadaluarsa: {{ $item->tanggal_kadaluarsa->format('d M Y') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->jumlah_jam)
                                            Jam: {{ $item->jumlah_jam }}
                                        @endif
                                        @if ($item->nilai_kompetensi)
                                            <div class="text-xs text-slate-500">Nilai: {{ $item->nilai_kompetensi }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->file_sertifikat)
                                            <a class="btn-ghost" href="{{ Storage::url($item->file_sertifikat) }}" target="_blank">Lihat</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="min-w-[280px]">
                                        <x-review-metadata
                                            :status="$item->status_verifikasi"
                                            :rejected-reason="$item->rejected_reason"
                                            :verified-by="$item->verifiedBy?->name"
                                            :verified-at="$item->verified_at"
                                            :source-system="$item->source_system"
                                            :source-actor="$item->source_actor"
                                        />
                                    </td>
                                    <td class="min-w-[220px]">
                                        <form method="POST" action="{{ route('lembaga.sertifikasi.verify', $item) }}" class="mb-3">
                                            @csrf
                                            <input type="text" name="catatan" placeholder="Catatan (opsional)" class="input-field text-sm mb-2" />
                                            <button class="table-action table-action-approve" type="submit">{{ __('Verifikasi') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('lembaga.sertifikasi.reject', $item) }}">
                                            @csrf
                                            <input type="text" name="catatan" placeholder="Alasan penolakan (opsional)" class="input-field text-sm mb-2" />
                                            <button class="table-action table-action-reject" type="submit">Tolak</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="8">Tidak ada sertifikat yang menunggu verifikasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $sertifikasi->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
