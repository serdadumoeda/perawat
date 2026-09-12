<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Riwayat Pekerjaan Saya</h2>
            <p class="section-subtitle">Pantau status verifikasi pengalaman kerja.</p>
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

            <div class="flex justify-end">
                <a href="{{ route('perawat.riwayat_pekerjaan.create') }}" class="btn-primary">Tambah Riwayat</a>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>Mitra</th>
                                <th>Posisi</th>
                                <th>Status Pekerjaan</th>
                                <th>Metadata Verifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayat as $item)
                                <tr class="align-top">
                                    <td>{{ $item->mitraPengguna?->nama ?? '-' }}</td>
                                    <td>{{ $item->posisi }}</td>
                                    <td>{{ $item->status_pekerjaan }}</td>
                                    <td class="min-w-[320px]">
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
                                        <div class="table-action-group">
                                            @if ($item->verifikasi_status !== \App\Models\RiwayatPekerjaan::VERIF_VERIFIED)
                                                <a href="{{ route('perawat.riwayat_pekerjaan.edit', $item) }}" class="btn-ghost text-xs">Edit</a>
                                                <form method="POST" action="{{ route('perawat.riwayat_pekerjaan.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat pekerjaan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="table-action table-action-reject text-xs">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-400 font-semibold">Terkunci</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada riwayat pekerjaan.</td>
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
