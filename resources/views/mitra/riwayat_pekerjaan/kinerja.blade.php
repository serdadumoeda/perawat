<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl text-slate-900">Riwayat Kinerja</h2>
                <p class="text-sm text-slate-500">Akhiri kontrak dan catat evaluasi kinerja setelah selesai / resign.</p>
            </div>
            <a href="{{ route('mitra.riwayat_pekerjaan.active') }}" class="btn-secondary">Riwayat Aktif</a>
        </div>
    </x-slot>

    <div class="module-shell module-shell-compact">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 module-container">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('mitra.riwayat_kinerja.export') }}" class="btn-secondary">Ekspor CSV</a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="module-card card-surface p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Kontrak Aktif</h3>
                        <span class="chip">Akhiri & evaluasi</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">Gunakan formulir ini saat kontrak selesai atau perawat resign.</p>

                    <div class="mt-5 space-y-4">
                        @forelse ($aktif as $item)
                            <div class="rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $item->perawat?->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->mitraPengguna?->nama ?? '-' }} - {{ $item->posisi ?? '-' }}</div>
                                    </div>
                                    <div class="text-xs text-slate-500">Mulai: {{ optional($item->tanggal_mulai)->format('d M Y') ?? '-' }}</div>
                                </div>

                                <form method="POST" action="{{ route('mitra.riwayat_pekerjaan.complete', $item) }}" class="mt-4 grid gap-3">
                                    @csrf
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600">Status</label>
                                            <select name="status_pekerjaan" class="select-field mt-1" required>
                                                <option value="selesai">Selesai Kontrak</option>
                                                <option value="resign">Resign</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600">Tanggal selesai</label>
                                            <input type="date" name="tanggal_selesai" class="input-field mt-1" required />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">Penilaian kinerja</label>
                                        <input type="text" name="penilaian_kinerja" placeholder="Contoh: Sangat Baik / Baik / Cukup" class="input-field mt-1" />
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600">Rating bintang</label>
                                            <div class="mt-2 star-rating" role="radiogroup" aria-label="Rating bintang">
                                                @for ($i = 5; $i >= 1; $i--)
                                                    <input type="radio" id="rating-{{ $item->id }}-{{ $i }}" name="kinerja_rating" value="{{ $i }}">
                                                    <label for="rating-{{ $item->id }}-{{ $i }}" title="{{ $i }} bintang">&#9733;</label>
                                                @endfor
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-slate-600">Skor (0-100)</label>
                                            <input type="number" step="0.01" min="0" max="100" name="kinerja_score" class="input-field mt-1" placeholder="0 - 100" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">Catatan singkat</label>
                                        <input type="text" name="keterangan" placeholder="Catatan tambahan (opsional)" class="input-field mt-1" />
                                    </div>
                                    <div class="flex justify-end">
                                        <x-primary-button>Simpan evaluasi</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <div class="empty-state">
                                Tidak ada kontrak aktif yang siap ditutup.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">{{ $aktif->links() }}</div>
                </section>

                <section class="module-card card-surface p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Riwayat Kinerja</h3>
                        <span class="chip">Selesai / Resign</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">Rekap evaluasi setelah kontrak berakhir.</p>

                    <div class="mt-5 space-y-4">
                        @forelse ($riwayat as $item)
                            <div class="rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $item->perawat?->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->mitraPengguna?->nama ?? '-' }} - {{ $item->posisi ?? '-' }}</div>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->status_pekerjaan === 'resign' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ ucfirst($item->status_pekerjaan) }}
                                    </span>
                                </div>
                                <div class="mt-3 text-xs text-slate-500">
                                    Periode: {{ optional($item->tanggal_mulai)->format('d M Y') ?? '-' }} - {{ optional($item->tanggal_selesai)->format('d M Y') ?? '-' }}
                                </div>
                                <div class="mt-3 grid gap-2 text-sm text-slate-600">
                                    <div><span class="font-semibold text-slate-700">Penilaian:</span> {{ $item->penilaian_kinerja ?? 'Belum ada penilaian' }}</div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-700">Rating:</span>
                                        @if ($item->kinerja_rating)
                                            <span class="text-amber-500">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    {!! $i <= $item->kinerja_rating ? '&#9733;' : '&#9734;' !!}
                                                @endfor
                                            </span>
                                            <span class="text-xs text-slate-500">({{ $item->kinerja_rating }}/5)</span>
                                        @else
                                            <span class="text-xs text-slate-500">Belum ada rating</span>
                                        @endif
                                    </div>
                                    <div><span class="font-semibold text-slate-700">Skor:</span> {{ $item->kinerja_score ?? 'Belum ada skor' }}</div>
                                    <div><span class="font-semibold text-slate-700">Catatan:</span> {{ $item->keterangan ?? 'Belum ada catatan' }}</div>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <a href="{{ route('mitra.riwayat_kinerja.edit', $item) }}" class="btn-secondary">Edit Evaluasi</a>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                Belum ada riwayat kinerja tersimpan.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">{{ $riwayat->links() }}</div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
