<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Bank Kandidat</h2>
                <p class="section-subtitle">Cari kandidat dengan filter terverifikasi.</p>
            </div>
            <a href="{{ route('mitra.lowongan.index') }}" class="btn-secondary">Kelola Lowongan</a>
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
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="panel-title">Filter Kandidat</div>
                        <div class="panel-desc">Gunakan kombinasi filter untuk hasil yang lebih presisi.</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('mitra.kandidat.index') }}" class="btn-ghost">Atur Ulang</a>
                        <button type="submit" form="filter-form" class="btn-primary">Terapkan Filter</button>
                    </div>
                </div>

                <form id="filter-form" method="GET" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="nama" :value="__('Nama')" />
                        <x-text-input id="nama" class="mt-1 w-full" type="text" name="nama" :value="request('nama')" placeholder="Nama perawat" />
                    </div>
                    <div>
                        <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin')" />
                        <select id="jenis_kelamin" name="jenis_kelamin" class="select-field mt-1">
                            <option value="">Semua</option>
                            <option value="L" @selected(request('jenis_kelamin') === 'L')>Laki-laki</option>
                            <option value="P" @selected(request('jenis_kelamin') === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status_str" :value="__('Status STR')" />
                        <select id="status_str" name="status_str" class="select-field mt-1">
                            <option value="">Semua</option>
                            <option value="aktif" @selected(request('status_str') === 'aktif')>Aktif</option>
                            <option value="hold" @selected(request('status_str') === 'hold')>Ditahan</option>
                            <option value="expired" @selected(request('status_str') === 'expired')>Kedaluwarsa</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status_ketenagakerjaan" :value="__('Status Ketenagakerjaan')" />
                        <select id="status_ketenagakerjaan" name="status_ketenagakerjaan" class="select-field mt-1">
                            <option value="">Semua</option>
                            <option value="fresh_graduate" @selected(request('status_ketenagakerjaan') === 'fresh_graduate')>Lulusan Baru</option>
                            <option value="berpengalaman" @selected(request('status_ketenagakerjaan') === 'berpengalaman')>Berpengalaman</option>
                            <option value="bekerja" @selected(request('status_ketenagakerjaan') === 'bekerja')>Bekerja</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status_ketersediaan" :value="__('Ketersediaan')" />
                        <select id="status_ketersediaan" name="status_ketersediaan" class="select-field mt-1">
                            <option value="">Semua</option>
                            <option value="available" @selected(request('status_ketersediaan') === 'available')>Tersedia</option>
                            <option value="open_to_offer" @selected(request('status_ketersediaan') === 'open_to_offer')>Terbuka untuk tawaran</option>
                            <option value="not_available" @selected(request('status_ketersediaan') === 'not_available')>Tidak tersedia</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="kota" :value="__('Kota')" />
                        <select id="kota" name="kota" class="select-field mt-1">
                            <option value="">Semua</option>
                            @foreach ($kotaOptions as $kota)
                                <option value="{{ $kota }}" @selected(request('kota') === $kota)>{{ $kota }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="provinsi" :value="__('Provinsi')" />
                        <select id="provinsi" name="provinsi" class="select-field mt-1">
                            <option value="">Semua</option>
                            @foreach ($provinsiOptions as $provinsi)
                                <option value="{{ $provinsi }}" @selected(request('provinsi') === $provinsi)>{{ $provinsi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <details class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <summary class="flex cursor-pointer items-center justify-between text-sm font-semibold text-slate-700">
                                Filter Lanjutan
                                <span class="text-xs text-slate-500">Klik untuk buka/tutup</span>
                            </summary>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="jenjang" :value="__('Jenjang')" />
                                    <x-text-input id="jenjang" class="mt-1 w-full" type="text" name="jenjang" :value="request('jenjang')" placeholder="S1/D3" />
                                </div>
                                <div>
                                    <x-input-label for="program_studi" :value="__('Program Studi')" />
                                    <x-text-input id="program_studi" class="mt-1 w-full" type="text" name="program_studi" :value="request('program_studi')" />
                                </div>
                                <div>
                                    <x-input-label for="min_ipk" :value="__('IPK Min')" />
                                    <x-text-input id="min_ipk" class="mt-1 w-full" type="number" step="0.01" name="min_ipk" :value="request('min_ipk')" />
                                </div>
                                <div>
                                    <x-input-label for="min_pengalaman" :value="__('Pengalaman Min (tahun)')" />
                                    <x-text-input id="min_pengalaman" class="mt-1 w-full" type="number" min="0" name="min_pengalaman" :value="request('min_pengalaman')" />
                                </div>
                                <div>
                                    <x-input-label for="min_pengalaman_total" :value="__('Total Pengalaman Min (tahun)')" />
                                    <x-text-input id="min_pengalaman_total" class="mt-1 w-full" type="number" min="0" step="0.5" name="min_pengalaman_total" :value="request('min_pengalaman_total')" />
                                </div>
                                <div>
                                    <x-input-label for="sertifikat_verifikasi" :value="__('Sertifikat Terverifikasi')" />
                                    <select id="sertifikat_verifikasi" name="sertifikat_verifikasi" class="select-field mt-1">
                                        <option value="">Semua</option>
                                        @foreach ($sertifikatOptions as $nama)
                                            <option value="{{ $nama }}" @selected(request('sertifikat_verifikasi') === $nama)>{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="sertifikat" :value="__('Sertifikat')" />
                                    <x-text-input id="sertifikat" class="mt-1 w-full" type="text" name="sertifikat" :value="request('sertifikat')" />
                                </div>
                                <div>
                                    <x-input-label for="lokasi" :value="__('Lokasi (alamat/tempat lahir)')" />
                                    <x-text-input id="lokasi" class="mt-1 w-full" type="text" name="lokasi" :value="request('lokasi')" />
                                </div>
                                <div>
                                    <x-input-label for="sort" :value="__('Urutkan')" />
                                    <select id="sort" name="sort" class="select-field mt-1">
                                        <option value="">Terbaru</option>
                                        <option value="nama_asc" @selected(request('sort') === 'nama_asc')>Nama A-Z</option>
                                        <option value="ipk_desc" @selected(request('sort') === 'ipk_desc')>IPK Tertinggi</option>
                                        <option value="ipk_asc" @selected(request('sort') === 'ipk_asc')>IPK Terendah</option>
                                        <option value="pengalaman_desc" @selected(request('sort') === 'pengalaman_desc')>Pengalaman Terbanyak</option>
                                        <option value="pengalaman_asc" @selected(request('sort') === 'pengalaman_asc')>Pengalaman Tersedikit</option>
                                        <option value="str_active" @selected(request('sort') === 'str_active')>STR Aktif Terlebih Dahulu</option>
                                        <option value="str_expired" @selected(request('sort') === 'str_expired')>STR Kedaluwarsa Terlebih Dahulu</option>
                                    </select>
                                </div>
                            </div>
                        </details>
                    </div>
                </form>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="panel-title">Daftar Kandidat</div>
                        <div class="panel-desc">Total kandidat: {{ $perawat->total() }}</div>
                    </div>
                    <span class="chip">Data terverifikasi</span>
                </div>

                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[720px]">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status STR</th>
                                <th>Pendidikan</th>
                                <th>IPK</th>
                                <th>Ketenagakerjaan</th>
                                <th>Ketersediaan</th>
                                <th>Status Rekrutmen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perawat as $item)
                                <tr>
                                    @php($pendidikan = $item->riwayatPendidikan->sortByDesc('tahun_lulus')->first())
                                    @php($activeRecruitment = $activeRecruitmentByNik[$item->nik] ?? null)
                                    @php($isViewerOwner = $activeRecruitment && in_array($activeRecruitment->mitra_pengguna_id, $viewerMitraIds, true))
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-900">{{ $item->nama }}</span>
                                            @if ($item->isVerifiedExperience())
                                                <span class="badge badge-success">Terverifikasi</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ optional($item->dataLegalitas)->status_str ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ optional($item->dataLegalitas?->str_berlaku_sampai)->format('d M Y') ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($pendidikan)
                                            {{ $pendidikan->jenjang }} - {{ $pendidikan->program_studi }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $pendidikan?->ipk ?? '-' }}</td>
                                    <td>{{ $item->ketenagakerjaan_label }}</td>
                                    <td>{{ $item->ketersediaan_label }}</td>
                                    <td>
                                        @if ($activeRecruitment)
                                            @if ($activeRecruitment->status === \App\Models\Rekrutmen::STATUS_HIRED)
                                                <span class="badge {{ $isViewerOwner ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $isViewerOwner ? 'Direkrut oleh Anda' : 'Direkrut oleh mitra lain' }}
                                                </span>
                                            @else
                                                <span class="badge {{ $isViewerOwner ? 'badge-warning' : 'badge-info' }}">
                                                    {{ $isViewerOwner ? 'Dikunci oleh Anda' : 'Dikunci oleh mitra lain' }}
                                                </span>
                                            @endif
                                            <div class="text-xs text-slate-500 mt-1">{{ $activeRecruitment->mitraPengguna?->nama ?? '-' }}</div>
                                        @else
                                            <span class="badge badge-muted">Belum di-lock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn-ghost" href="{{ route('mitra.kandidat.show', $item) }}">Lihat Profil</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="8">Belum ada kandidat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $perawat->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
