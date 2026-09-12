<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Playground Integrasi</h2>
                <p class="section-subtitle">Simulasi integrasi API, pemantauan request, dan webhook outgoing.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.api_keys.create') }}" class="btn-primary">Buat Kunci API</a>
                <a href="{{ route('api.v1.swagger') }}" target="_blank" rel="noopener" class="btn-secondary">Swagger API v1</a>
                <a href="{{ route('admin.api_keys.postman_collection') }}" class="btn-secondary">Unduh Postman</a>
            </div>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('signature_secret'))
                <div class="alert alert-warning">
                    <div class="font-semibold mb-1">HMAC Signing Secret (tampilkan sekali)</div>
                    <div class="font-mono break-all">{{ session('signature_secret') }}</div>
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <h3 class="font-semibold text-slate-900 mb-3">Contoh cURL</h3>
                <div class="space-y-4 text-xs">
                    <pre class="bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto">curl -X POST "{{ $baseUrl }}/api/v1/lulusan" \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: &lt;api_key&gt;" \
  -H "Idempotency-Key: lulusan-1234567890123456-20260214" \
  -d '{"data":[{"nik":"1234567890123456","nama":"Siti Aminah"}]}'</pre>

                    <pre class="bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto">TIMESTAMP=$(date +%s)
PAYLOAD='{"data":[{"nik":"1234567890123456","nama":"Siti Aminah"}]}'
SIGNATURE=$(printf "%s.%s" "$TIMESTAMP" "$PAYLOAD" | openssl dgst -sha256 -hmac "&lt;signing_secret&gt;" -binary | xxd -p -c 256)
curl -X POST "{{ $baseUrl }}/api/v1/lulusan" \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: &lt;api_key&gt;" \
  -H "Idempotency-Key: lulusan-1234567890123456-20260214" \
  -H "X-API-TIMESTAMP: $TIMESTAMP" \
  -H "X-API-SIGNATURE: $SIGNATURE" \
  -d "$PAYLOAD"</pre>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Log Request API Terakhir</h3>
                    <div class="text-xs text-slate-500">Menyimpan endpoint, payload hash, status, response time, error.</div>
                </div>
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[1100px]">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Endpoint</th>
                                <th>Metode</th>
                                <th>Kunci API</th>
                                <th>Status</th>
                                <th>Waktu Respons</th>
                                <th>Hash Payload</th>
                                <th>Galat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($apiRequestLogs as $log)
                                <tr>
                                    <td class="text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                                    <td class="font-semibold text-slate-800">{{ $log->endpoint }}</td>
                                    <td>{{ $log->method }}</td>
                                    <td class="text-xs">
                                        @if ($log->apiKey)
                                            {{ $log->apiKey->name }}<br>
                                            <span class="text-slate-500">{{ class_basename($log->apiKey->owner_type) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $log->status_code >= 400 ? 'badge-danger' : 'badge-success' }}">{{ $log->status_code }}</span>
                                    </td>
                                    <td>{{ $log->response_time_ms }} ms</td>
                                    <td class="text-[11px] font-mono text-slate-600">{{ $log->payload_hash ? substr($log->payload_hash, 0, 18).'...' : '-' }}</td>
                                    <td class="text-xs text-rose-700">{{ $log->error_message ? \Illuminate\Support\Str::limit($log->error_message, 80) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty-state-row">Belum ada log request API.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="module-card card-surface p-4 sm:p-6">
                    <h3 class="font-semibold text-slate-900 mb-3">Webhook Outgoing</h3>
                    <p class="text-sm text-slate-600 mb-4">Kirim event saat status menjadi <span class="font-semibold">terverifikasi</span> ke partner.</p>
                    <p class="text-xs text-slate-500 mb-4">
                        Kebijakan coba ulang: maks {{ config('api.webhook_retry_attempts', 3) }} percobaan,
                        jeda {{ implode(', ', config('api.webhook_retry_backoff_seconds', [30, 120, 300])) }} detik.
                        Percobaan terakhir gagal akan ditandai <span class="font-semibold">dead-letter</span>.
                    </p>

                    <form method="POST" action="{{ route('admin.webhook_subscriptions.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Nama Langganan')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 w-full" required />
                        </div>
                        <div>
                            <x-input-label for="target_url" :value="__('Target URL')" />
                            <x-text-input id="target_url" name="target_url" type="url" class="mt-1 w-full" required />
                        </div>
                        <div>
                            <x-input-label :value="__('Peristiwa')" />
                            <div class="mt-2 space-y-2 text-sm">
                                @foreach ($webhookEvents as $event)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" class="checkbox-field" name="events[]" value="{{ $event }}" checked>
                                        <span>{{ $event }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <x-input-label for="signing_secret" :value="__('Rahasia Penandatanganan (Opsional)')" />
                            <x-text-input id="signing_secret" name="signing_secret" type="text" class="mt-1 w-full" />
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" class="checkbox-field" name="is_active" value="1" checked>
                            Aktif
                        </label>
                        <div class="pt-1">
                            <x-primary-button>Simpan Langganan</x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="module-card card-surface p-4 sm:p-6">
                    <h3 class="font-semibold text-slate-900 mb-3">Langganan Aktif</h3>
                    <div class="space-y-3">
                        @forelse ($subscriptions as $subscription)
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $subscription->name }}</div>
                                        <div class="text-xs text-slate-500 break-all">{{ $subscription->target_url }}</div>
                                        <div class="text-xs mt-1">{{ implode(', ', $subscription->events ?? []) }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="badge {{ $subscription->is_active ? 'badge-success' : 'badge-warning' }}">
                                            {{ $subscription->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('admin.webhook_subscriptions.update', $subscription) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="name" value="{{ $subscription->name }}">
                                        <input type="hidden" name="target_url" value="{{ $subscription->target_url }}">
                                        @foreach (($subscription->events ?? []) as $event)
                                            <input type="hidden" name="events[]" value="{{ $event }}">
                                        @endforeach
                                        <input type="hidden" name="is_active" value="{{ $subscription->is_active ? 0 : 1 }}">
                                        <button class="btn-secondary" type="submit">{{ $subscription->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.webhook_subscriptions.destroy', $subscription) }}" onsubmit="return confirm('Hapus webhook ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-secondary text-rose-700 border-rose-200 hover:bg-rose-50" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">Belum ada langganan webhook.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <h3 class="font-semibold text-slate-900 mb-3">Riwayat Delivery Webhook</h3>
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[1080px]">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Langganan</th>
                                <th>Peristiwa</th>
                                <th>Percobaan</th>
                                <th>Status</th>
                                <th>Coba Ulang Selanjutnya</th>
                                <th>Waktu Respons</th>
                                <th>Hash Payload</th>
                                <th>Galat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentDeliveries as $delivery)
                                <tr>
                                    <td class="text-xs">{{ $delivery->delivered_at?->format('d M Y H:i:s') }}</td>
                                    <td class="text-sm">{{ $delivery->webhookSubscription?->name ?? '-' }}</td>
                                    <td class="text-xs">{{ $delivery->event }}</td>
                                    <td class="text-xs">{{ $delivery->attempt_number ?? 1 }}/{{ $delivery->max_attempts ?? 1 }}</td>
                                    <td>
                                        <span class="badge {{ $delivery->is_success ? 'badge-success' : ($delivery->is_dead_letter ? 'badge-warning' : 'badge-danger') }}">
                                            {{ $delivery->is_dead_letter ? 'DEAD-LETTER' : ($delivery->status_code ?? 'ERR') }}
                                        </span>
                                    </td>
                                    <td class="text-xs">
                                        {{ $delivery->next_retry_at?->format('d M Y H:i:s') ?? '-' }}
                                    </td>
                                    <td>{{ $delivery->response_time_ms ? $delivery->response_time_ms.' ms' : '-' }}</td>
                                    <td class="text-[11px] font-mono text-slate-600">{{ substr($delivery->payload_hash, 0, 18) }}...</td>
                                    <td class="text-xs text-rose-700">{{ $delivery->error_message ? \Illuminate\Support\Str::limit($delivery->error_message, 90) : '-' }}</td>
                                    <td>
                                        @if ($delivery->is_dead_letter)
                                            <form method="POST" action="{{ route('admin.integration_playground.deliveries.retry', $delivery) }}">
                                                @csrf
                                                <button type="submit" class="btn-secondary">Coba Ulang</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="empty-state-row">Belum ada delivery webhook.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
