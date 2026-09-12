<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Log Audit</h2>
            <p class="section-subtitle">Jejak aktivitas dan perubahan data.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            <div class="module-card card-surface p-4 sm:p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="action" :value="__('Action')" />
                        <x-text-input id="action" class="mt-1 w-full" type="text" name="action" :value="request('action')" placeholder="lamaran.apply" />
                    </div>
                    <div>
                        <x-input-label for="user_id" :value="__('User ID')" />
                        <x-text-input id="user_id" class="mt-1 w-full" type="text" name="user_id" :value="request('user_id')" />
                    </div>
                    <div>
                        <x-input-label for="subject_type" :value="__('Subject Type')" />
                        <x-text-input id="subject_type" class="mt-1 w-full" type="text" name="subject_type" :value="request('subject_type')" placeholder="App\\Models\\Lamaran" />
                    </div>
                    <div>
                        <x-input-label for="subject_id" :value="__('Subject ID')" />
                        <x-text-input id="subject_id" class="mt-1 w-full" type="text" name="subject_id" :value="request('subject_id')" />
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Aktor</th>
                                <th>Aksi</th>
                                <th>Subjek</th>
                                <th>Metadata</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr class="align-top">
                                    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $log->user?->name ?? 'Sistem' }}</div>
                                        <div class="text-xs text-slate-500">{{ $log->user?->email }}</div>
                                        <div class="text-xs text-slate-500">{{ $log->user?->role }}</div>
                                    </td>
                                    <td>{{ $log->action }}</td>
                                    <td>
                                        <div class="text-xs text-slate-500">{{ class_basename($log->subject_type) }}</div>
                                        <div class="text-xs">{{ $log->subject_id }}</div>
                                    </td>
                                    <td class="text-xs text-slate-600">
                                        {{ \Illuminate\Support\Str::limit(json_encode($log->meta, JSON_UNESCAPED_SLASHES), 160) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada audit log.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
