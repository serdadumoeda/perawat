<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Impor Lulusan (CSV)</h2>
            <p class="section-subtitle">Pemetaan header fleksibel, pratinjau 20 baris, validasi per baris, dan log batch dapat diunduh.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if (session('import_errors'))
                <div class="alert alert-danger">
                    <div class="font-semibold mb-2">Kesalahan Impor:</div>
                    <ul class="list-disc list-inside">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('last_import_batch_id'))
                <div class="alert alert-success">
                    Batch tersimpan.
                    <a class="link-muted font-semibold" href="{{ route('kampus.lulusan.import.logs.download', session('last_import_batch_id')) }}">
                        Unduh laporan batch
                    </a>
                </div>
            @endif

            <div class="module-card card-surface p-6 space-y-4">
                <div class="text-sm text-slate-600">
                    Unduh template CSV: <a class="link-muted" href="{{ route('kampus.lulusan.template') }}">template-lulusan.csv</a>
                </div>

                <form method="POST" action="{{ route('kampus.lulusan.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="preview">

                    <div>
                        <x-input-label for="institusi_pendidikan_id" :value="__('Institusi Pendidikan')" />
                        <select id="institusi_pendidikan_id" name="institusi_pendidikan_id" class="select-field mt-2" required>
                            <option value="" disabled @selected(!(old('institusi_pendidikan_id') ?? ($selectedInstitusiId ?? null)))>Pilih institusi</option>
                            @foreach ($institusi as $item)
                                <option value="{{ $item->id }}" @selected((old('institusi_pendidikan_id') ?? ($selectedInstitusiId ?? null)) === $item->id)>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('institusi_pendidikan_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="file" :value="__('File CSV')" />
                        <input id="file" type="file" name="file" class="input-field mt-2" required />
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>{{ __('Pratinjau 20 Baris') }}</x-primary-button>
                    </div>
                </form>
            </div>

            @if (!empty($preview))
                <div class="module-card card-surface p-6 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="panel-title">Pratinjau Data</div>
                            <div class="panel-desc">Total baris data: {{ $preview['total_rows'] }} • Ditampilkan: {{ count($preview['preview_rows']) }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('kampus.lulusan.import.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action" value="import">
                        <input type="hidden" name="institusi_pendidikan_id" value="{{ old('institusi_pendidikan_id', $selectedInstitusiId ?? '') }}">
                        <input type="hidden" name="temp_file" value="{{ $preview['temp_file'] }}">
                        <input type="hidden" name="original_filename" value="{{ $preview['original_filename'] }}">

                        <div class="table-wrap overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-100 text-slate-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Header CSV</th>
                                        <th class="px-3 py-2 text-left">Pemetaan Kolom</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($preview['headers'] as $header)
                                        <tr>
                                            <td class="px-3 py-2">{{ $header['raw'] }}</td>
                                            <td class="px-3 py-2">
                                                <select name="mapping[{{ $header['key'] }}]" class="select-field">
                                                    <option value="">-- Abaikan Kolom --</option>
                                                    @foreach ($preview['field_options'] as $fieldKey => $label)
                                                        <option value="{{ $fieldKey }}" @selected((old('mapping.'.$header['key']) ?? $header['mapped_field']) === $fieldKey)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-wrap overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-100 text-slate-700">
                                    <tr>
                                        @foreach ($preview['headers'] as $header)
                                            <th class="px-3 py-2 text-left">{{ $header['raw'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($preview['preview_rows'] as $row)
                                        <tr>
                                            @foreach ($preview['headers'] as $header)
                                                <td class="px-3 py-2 text-slate-700">{{ $row[$header['index']] ?? '' }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ max(count($preview['headers']), 1) }}" class="empty-state-row">
                                                Tidak ada data untuk dipratinjau.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>{{ __('Proses Impor') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="module-card card-surface p-6 space-y-4">
                <div class="panel-title">Riwayat Batch Impor</div>
                <div class="panel-desc">10 batch terakhir milik akun Anda.</div>

                <div class="table-wrap overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 text-slate-700">
                            <tr>
                                <th class="px-3 py-2 text-left">Waktu</th>
                                <th class="px-3 py-2 text-left">File</th>
                                <th class="px-3 py-2 text-left">Ringkasan</th>
                                <th class="px-3 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($importLogs as $log)
                                <tr>
                                    <td class="px-3 py-2 text-slate-700">{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-3 py-2 text-slate-700">{{ $log->original_filename }}</td>
                                    <td class="px-3 py-2 text-slate-700">
                                        OK {{ $log->success_rows }} / Gagal {{ $log->failed_rows }} / Baru {{ $log->created_rows }} / Update {{ $log->updated_rows }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <a class="btn-secondary text-xs" href="{{ route('kampus.lulusan.import.logs.download', $log) }}">
                                            Unduh CSV
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state-row">Belum ada batch impor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
