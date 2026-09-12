<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Riwayat Pekerjaan Aktif</h2>
                <p class="section-subtitle">Akhiri kontrak dan catat evaluasi kinerja.</p>
            </div>
            <a href="{{ route('mitra.riwayat_kinerja.index') }}" class="btn-secondary">Riwayat Kinerja</a>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 module-container">
            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="text-slate-900">
                    <div class="table-wrap overflow-x-auto">
                        <table class="data-table min-w-[940px]">
                            <thead>
                                <tr>
                                    <th>Perawat</th>
                                    <th>Mitra</th>
                                    <th>Posisi</th>
                                    <th>Mulai</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($riwayat as $item)
                                    <tr class="align-top">
                                        <td>{{ $item->perawat?->nama ?? '-' }}</td>
                                        <td>{{ $item->mitraPengguna?->nama ?? '-' }}</td>
                                        <td>{{ $item->posisi ?? '-' }}</td>
                                        <td>{{ optional($item->tanggal_mulai)->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('mitra.riwayat_pekerjaan.complete', $item) }}" class="space-y-2">
                                                @csrf
                                                <select name="status_pekerjaan" class="select-field text-sm">
                                                    <option value="selesai">Selesai Kontrak</option>
                                                    <option value="resign">Resign</option>
                                                </select>
                                                <input type="date" name="tanggal_selesai" class="input-field text-sm" required />
                                                <input type="text" name="penilaian_kinerja" placeholder="Penilaian (opsional)" class="input-field text-sm" />
                                                <div class="grid gap-2 sm:grid-cols-2">
                                                    <div>
                                                        <label class="text-xs font-semibold text-slate-600">Rating bintang</label>
                                                        <div class="mt-2 star-rating" role="radiogroup" aria-label="Rating bintang">
                                                            @for ($i = 5; $i >= 1; $i--)
                                                                <input type="radio" id="rating-active-{{ $item->id }}-{{ $i }}" name="kinerja_rating" value="{{ $i }}">
                                                                <label for="rating-active-{{ $item->id }}-{{ $i }}" title="{{ $i }} bintang">&#9733;</label>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-semibold text-slate-600">Skor (0-100)</label>
                                                        <input type="number" step="0.01" min="0" max="100" name="kinerja_score" placeholder="Skor 0-100" class="input-field text-sm" />
                                                    </div>
                                                </div>
                                                <input type="text" name="keterangan" placeholder="Catatan (opsional)" class="input-field text-sm" />
                                                <button type="submit" class="table-action table-action-approve">{{ __('Simpan') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="empty-state-row" colspan="5">Tidak ada riwayat pekerjaan aktif.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $riwayat->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
