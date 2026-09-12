<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Notifikasi</h2>
            <p class="section-subtitle">Info status lamaran, sertifikasi, legalitas, dan lowongan terbaru.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <div class="panel-title">Daftar Notifikasi</div>
                        <div class="panel-desc">Terbaru di bagian atas.</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="chip chip-accent">{{ $notifications->total() }} notifikasi</span>
                        <span class="chip">{{ $unreadCount }} belum dibaca</span>
                    </div>
                </div>

                <div class="mb-5 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <form method="GET" class="contents">
                        <div>
                            <x-input-label for="tipe" :value="__('Tipe')" />
                            <select id="tipe" name="tipe" class="select-field mt-1">
                                <option value="all" @selected($tipe === 'all')>Semua</option>
                                <option value="legalitas" @selected($tipe === 'legalitas')>Legalitas</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="legalitas_status" :value="__('Status Legalitas')" />
                            <select id="legalitas_status" name="legalitas_status" class="select-field mt-1">
                                <option value="all" @selected($legalitasStatus === 'all')>Semua Status</option>
                                @foreach ($legalitasStatusOptions as $status)
                                    <option value="{{ $status }}" @selected($legalitasStatus === $status)>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <x-primary-button class="w-full">{{ __('Filter') }}</x-primary-button>
                            <a class="btn-secondary w-full text-center" href="{{ route('perawat.notifikasi') }}">Atur Ulang</a>
                        </div>
                    </form>
                    <div class="flex items-end gap-2">
                        <form method="POST" action="{{ route('perawat.notifikasi.read') }}" class="w-full">
                            @csrf
                            <input type="hidden" name="scope" value="all">
                            <button class="btn-secondary w-full" type="submit">Tandai Semua Dibaca</button>
                        </form>
                        <form method="POST" action="{{ route('perawat.notifikasi.read') }}" class="w-full">
                            @csrf
                            <input type="hidden" name="scope" value="legalitas">
                            <button class="btn-ghost w-full" type="submit">Tandai Legalitas Dibaca</button>
                        </form>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse ($notifications as $notification)
                        @php
                            $data = $notification->data ?? [];
                            $type = $data['tipe'] ?? 'lowongan';
                            $badgeClass = match ($type) {
                                'lamaran_status' => 'badge badge-success',
                                'sertifikasi_status' => 'badge badge-warning',
                                'riwayat_pekerjaan_status' => 'badge badge-success',
                                'legalitas_document_status' => 'badge badge-warning',
                                'legalitas_data_status' => 'badge badge-warning',
                                default => 'badge badge-success',
                            };
                            $badgeLabel = match ($type) {
                                'lamaran_status' => 'Lamaran',
                                'sertifikasi_status' => 'Sertifikasi',
                                'riwayat_pekerjaan_status' => 'Kinerja',
                                'legalitas_document_status' => 'Legalitas',
                                'legalitas_data_status' => 'Legalitas Data',
                                default => 'Lowongan',
                            };
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="font-semibold text-slate-900">
                                    @if ($type === 'lamaran_status')
                                        Status Lamaran: {{ $data['status'] ?? '-' }}
                                    @elseif ($type === 'sertifikasi_status')
                                        Status Sertifikasi: {{ $data['status'] ?? '-' }}
                                    @elseif ($type === 'riwayat_pekerjaan_status')
                                        Status Riwayat Pekerjaan: {{ $data['status'] ?? '-' }}
                                    @elseif ($type === 'legalitas_document_status')
                                        @php($docLabel = ($data['document_type'] ?? '') === 'izin_luar_negeri' ? 'Izin Luar Negeri' : 'STR')
                                        Status Dokumen {{ $docLabel }}: {{ $data['status'] ?? '-' }}
                                    @elseif ($type === 'legalitas_data_status')
                                        Status Data STR: {{ $data['status'] ?? '-' }}
                                    @else
                                        Lowongan Baru: {{ $data['judul'] ?? '-' }}
                                    @endif
                                </div>
                                <span class="{{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </div>

                            <div class="mt-2 text-sm text-slate-600 space-y-1">
                                @if ($type === 'lamaran_status')
                                    <div>{{ $data['message'] ?? '' }}</div>
                                    <div>Lowongan: {{ $data['judul_lowongan'] ?? '-' }}</div>
                                @elseif ($type === 'sertifikasi_status')
                                    <div>{{ $data['message'] ?? '' }}</div>
                                    <div>Sertifikat: {{ $data['nama_sertifikat'] ?? '-' }}</div>
                                    <div>Lembaga: {{ $data['lembaga_nama'] ?? '-' }}</div>
                                @elseif ($type === 'riwayat_pekerjaan_status')
                                    <div>{{ $data['message'] ?? '' }}</div>
                                    <div>Mitra: {{ $data['mitra_nama'] ?? '-' }}</div>
                                @elseif ($type === 'legalitas_document_status')
                                    <div>{{ $data['message'] ?? '' }}</div>
                                    @if (!empty($data['note']))
                                        <div>Catatan: {{ $data['note'] }}</div>
                                    @endif
                                @elseif ($type === 'legalitas_data_status')
                                    <div>{{ $data['message'] ?? '' }}</div>
                                    @if (!empty($data['note']))
                                        <div>Catatan: {{ $data['note'] }}</div>
                                    @endif
                                @else
                                    @if (!empty($data['tipe_penempatan']))
                                        <div>
                                            Penempatan:
                                            {{ ($data['tipe_penempatan'] ?? '') === 'luar_negeri' ? 'Luar Negeri' : 'Domestik' }}
                                            @if (!empty($data['negara_penempatan']))
                                                • {{ $data['negara_penempatan'] }}
                                            @endif
                                            @php($lokasi = trim(($data['kota_penempatan'] ?? '') . ' ' . ($data['provinsi_penempatan'] ?? '')))
                                            @if ($lokasi !== '')
                                                • {{ $lokasi }}
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="mt-3 text-xs text-slate-500">{{ $notification->created_at->format('d M Y H:i') }}</div>
                            @if (!empty($data['lowongan_id']))
                                <div class="mt-1 text-xs text-slate-500">ID Lowongan: {{ $data['lowongan_id'] }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">Tidak ada notifikasi.</div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
