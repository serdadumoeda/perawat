<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Skenario Demo</h2>
                <p class="section-subtitle">Jalankan alur PDF untuk 1 perawat: lulusan → sertifikat → STR aktif → hire & update pengalaman.</p>
            </div>
            <code class="text-xs rounded-lg bg-slate-900/95 px-3 py-2 text-slate-100">php artisan demo:scenario --nik=9000000000000001</code>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <form method="POST" action="{{ route('admin.demo_scenario.run') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4 md:items-end">
                    @csrf
                    <div class="md:col-span-2">
                        <x-input-label for="nik" :value="__('NIK Perawat (opsional)')" />
                        <x-text-input
                            id="nik"
                            name="nik"
                            type="text"
                            maxlength="16"
                            class="mt-1 block w-full"
                            :value="old('nik', $defaultNik)"
                            placeholder="16 digit NIK"
                        />
                        <p class="mt-1 text-xs text-slate-500">Kosongkan untuk menggunakan NIK default demo.</p>
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="btn-primary">Jalankan Skenario</button>
                        <a href="{{ route('admin.demo_scenario.index') }}" class="btn-ghost">Atur Ulang Tampilan</a>
                    </div>
                </form>
            </div>

            @if ($selectedRun)
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div class="module-card card-surface p-4 sm:p-6 xl:col-span-2">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="panel-title">Linimasa Peristiwa</div>
                                <div class="text-xs text-slate-500">Kunci Skenario: {{ $selectedRun->scenario_key }}</div>
                            </div>
                            <span class="badge {{ $selectedRun->status === 'completed' ? 'badge-success' : ($selectedRun->status === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                {{ strtoupper($selectedRun->status) }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            @forelse ($selectedRun->events as $event)
                                <div class="rounded-2xl border border-slate-200 bg-white/80 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="text-sm font-semibold text-slate-900">Step {{ $event->step_order }} · {{ str_replace('_', ' ', $event->step_key) }}</div>
                                        <div class="text-xs text-slate-500">{{ $event->happened_at?->format('d M Y H:i:s') }}</div>
                                    </div>
                                    <div class="mt-1 text-sm text-slate-700">{{ $event->action }}</div>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="badge badge-info">{{ strtoupper($event->actor_role) }}</span>
                                        <span class="text-slate-600">{{ $event->actor_label }}</span>
                                    </div>

                                    @if (!empty($event->payload))
                                        <dl class="mt-3 grid grid-cols-1 gap-x-4 gap-y-1 text-xs text-slate-600 sm:grid-cols-2">
                                            @foreach ($event->payload as $key => $value)
                                                <div>
                                                    <dt class="font-semibold text-slate-500">{{ str_replace('_', ' ', $key) }}</dt>
                                                    <dd class="text-slate-700">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">Belum ada peristiwa pada linimasa.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="module-card card-surface p-4 sm:p-6">
                            <div class="panel-title mb-2">Ringkasan Eksekusi</div>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Perawat NIK</dt>
                                    <dd class="font-semibold text-slate-900">{{ $selectedRun->perawat_nik }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Mulai</dt>
                                    <dd class="text-slate-700">{{ $selectedRun->started_at?->format('d M Y H:i:s') ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Selesai</dt>
                                    <dd class="text-slate-700">{{ $selectedRun->finished_at?->format('d M Y H:i:s') ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Total Peristiwa</dt>
                                    <dd class="font-semibold text-slate-900">{{ $selectedRun->events->count() }}</dd>
                                </div>
                            </dl>

                            @if (!empty($selectedRun->summary))
                                <div class="mt-4 rounded-xl bg-slate-50 p-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ringkasan</div>
                                    <pre class="mt-2 whitespace-pre-wrap break-words text-xs text-slate-700">{{ json_encode($selectedRun->summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            @endif

                            @if ($selectedRun->error_message)
                                <div class="mt-3 alert alert-danger">
                                    {{ $selectedRun->error_message }}
                                </div>
                            @endif
                        </div>

                        <div class="module-card card-surface p-4 sm:p-6">
                            <div class="panel-title mb-3">Riwayat Eksekusi</div>
                            <div class="space-y-2">
                                @forelse ($runs as $run)
                                    <a href="{{ route('admin.demo_scenario.index', ['run' => $run->id]) }}" class="block rounded-xl border px-3 py-2 text-sm transition hover:bg-slate-50 {{ $selectedRun->id === $run->id ? 'border-teal-300 bg-teal-50' : 'border-slate-200' }}">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-semibold text-slate-800">{{ $run->perawat_nik }}</span>
                                            <span class="badge {{ $run->status === 'completed' ? 'badge-success' : ($run->status === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ $run->status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $run->started_at?->format('d M Y H:i:s') }} · {{ $run->events_count }} peristiwa
                                        </div>
                                    </a>
                                @empty
                                    <div class="empty-state">Belum ada data eksekusi.</div>
                                @endforelse
                            </div>
                            <div class="mt-4">
                                {{ $runs->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="module-card card-surface p-8">
                    <div class="empty-state">Belum ada eksekusi skenario demo. Jalankan skenario untuk membuat linimasa.</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
