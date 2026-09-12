<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
            <div>
                <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.375rem;">Institusi Pendidikan</div>
                <h2 style="font-family:'Playfair Display',serif; font-size:1.625rem; font-weight:700; color:#1a1a1a; margin:0;">Kelola Lulusan &amp; Angkatan</h2>
                <p style="font-size:0.875rem; color:#9a9a9a; margin:0.25rem 0 0;">Input, kelola, dan lakukan penelusuran data alumni lulusan keperawatan.</p>
            </div>
            <div style="display:flex; gap:0.625rem;">
                <a class="btn-secondary" href="{{ route('kampus.gateway') }}" style="font-size:0.75rem;">🔍 Gateway NIK Lookup</a>
                <a class="btn-primary" href="{{ route('kampus.lulusan.create') }}" style="font-size:0.75rem;">+ Input Lulusan Baru</a>
            </div>
        </div>
    </x-slot>

    <div class="module-shell module-shell-compact">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 module-container" style="display:flex; flex-direction:column; gap:1.5rem;">
            <div class="module-card card-surface p-6">

                <!-- Search & Filters -->
                <form method="GET" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:1.5rem;" class="sm:grid-cols-3 grid-cols-1">
                    <div>
                        <x-input-label for="institusi_id" :value="__('Institusi')" />
                        <select id="institusi_id" name="institusi_id" class="select-field mt-1.5">
                            @foreach ($institusiList as $institusi)
                                <option value="{{ $institusi->id }}" @selected($selectedInstitusi && $selectedInstitusi->id === $institusi->id)>{{ $institusi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="q" :value="__('Pencarian NIK / Nama')" />
                        <x-text-input id="q" class="mt-1.5" type="text" name="q" :value="request('q')" placeholder="Ketik Nama / NIK / Email" />
                    </div>
                    <div style="display:flex; align-items:flex-end; gap:0.75rem;">
                        <x-primary-button style="padding:0.75rem 1.5rem;">{{ __('Cari') }}</x-primary-button>
                        <a class="btn-ghost" href="{{ route('kampus.lulusan.import') }}">Impor Bulk CSV</a>
                    </div>
                </form>

                <!-- Cohort Quick Filter Chips -->
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5rem; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid #f0e5cf;">
                    <span style="font-size:0.7rem; font-weight:700; color:#9a9a9a; text-transform:uppercase; letter-spacing:0.08em; margin-right:0.25rem;">Filter Cepat:</span>
                    <a href="{{ route('kampus.lulusan.index', ['institusi_id' => $selectedInstitusi?->id]) }}" class="{{ !request('q') ? 'badge badge-teal' : 'badge badge-muted' }}" style="text-decoration:none;">Semua Alumni</a>
                    <a href="{{ route('kampus.lulusan.index', ['institusi_id' => $selectedInstitusi?->id, 'q' => '3.5']) }}" class="badge badge-muted" style="text-decoration:none;">⭐ Cumlaude (IPK ≥ 3.5)</a>
                    <a href="{{ route('kampus.lulusan.index', ['institusi_id' => $selectedInstitusi?->id, 'q' => 'Ners']) }}" class="badge badge-muted" style="text-decoration:none;">Profesi Ners</a>
                    <a href="{{ route('kampus.lulusan.index', ['institusi_id' => $selectedInstitusi?->id, 'q' => 'D3']) }}" class="badge badge-muted" style="text-decoration:none;">D3 Keperawatan</a>
                </div>

                <!-- Data Table -->
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Perawat &amp; Identitas</th>
                                <th>NIK (16 Digit)</th>
                                <th>Jenjang &amp; Prodi</th>
                                <th>IPK Lulus</th>
                                <th>Dokumen Ijazah</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perawat as $item)
                                @php($riwayat = $item->riwayatPendidikan->first())
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:#1a1a1a;">{{ $item->nama }}</div>
                                        <div style="font-size:0.75rem; color:#9a9a9a;">{{ $item->email }}</div>
                                    </td>
                                    <td>
                                        <code style="font-size:0.8rem; color:#0f5050; font-weight:700;">{{ $item->nik }}</code>
                                    </td>
                                    <td>
                                        <span class="badge badge-teal" style="font-size:0.65rem;">{{ $riwayat?->jenjang ?? '-' }}</span>
                                        @if ($riwayat?->program_studi)
                                            <div style="font-size:0.75rem; color:#5a5a5a; margin-top:0.125rem;">{{ $riwayat->program_studi }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color:{{ ($riwayat?->ipk ?? 0) >= 3.5 ? '#e05252' : '#1a1a1a' }};">
                                            {{ number_format($riwayat?->ipk ?? 0, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if ($riwayat?->transkrip_path)
                                            <a class="link-muted" href="{{ Storage::url($riwayat->transkrip_path) }}" target="_blank">📄 Transkrip</a>
                                        @endif
                                        @if ($riwayat?->ijazah_path)
                                            <span style="color:#d0c0a0; margin:0 0.25rem;">|</span>
                                            <a class="link-muted" href="{{ Storage::url($riwayat->ijazah_path) }}" target="_blank">🎓 Ijazah</a>
                                        @endif
                                        @if (!$riwayat?->transkrip_path && !$riwayat?->ijazah_path)
                                            <span style="font-size:0.75rem; color:#9a9a9a;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        <a class="btn-ghost text-xs" href="{{ route('kampus.lulusan.edit', [$item, 'institusi_id' => $selectedInstitusi?->id]) }}">Edit Data &rarr;</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="6">Belum ada data lulusan terdaftar. Klik "+ Input Lulusan Baru" untuk menambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $perawat->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
