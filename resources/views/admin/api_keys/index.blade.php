<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Kunci API</h2>
                <p class="section-subtitle">Kelola akses API untuk integrasi data.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.integration_playground.index') }}" class="btn-secondary">Playground Integrasi</a>
                <a href="{{ route('api.v1.swagger') }}" target="_blank" rel="noopener" class="btn-secondary">Swagger API v1</a>
                <a href="{{ route('admin.api_keys.postman_collection') }}" class="btn-secondary">Unduh Postman</a>
                <a href="{{ route('admin.api_keys.create') }}" class="btn-primary">Buat Kunci API</a>
            </div>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if (session('token'))
                <div class="alert alert-warning">
                    <div class="font-semibold mb-1">Token Baru (tampilkan sekali saja)</div>
                    <div class="font-mono break-all">{{ session('token') }}</div>
                </div>
            @endif

            @if (session('signature_secret'))
                <div class="alert alert-warning">
                    <div class="font-semibold mb-1">HMAC Signing Secret (tampilkan sekali saja)</div>
                    <div class="font-mono break-all">{{ session('signature_secret') }}</div>
                </div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Pemilik</th>
                                <th>Cakupan</th>
                                <th>Keamanan</th>
                                <th>Terakhir Digunakan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($apiKeys as $key)
                                @php($owner = $key->owner)
                                <tr class="{{ session('created_id') === $key->id ? 'bg-slate-50/70' : '' }}">
                                    <td class="font-semibold text-slate-900">{{ $key->name }}</td>
                                    <td>
                                        @if ($owner instanceof \App\Models\InstitusiPendidikan)
                                            Institusi: {{ $owner->nama }}
                                        @elseif ($owner instanceof \App\Models\LembagaPelatihan)
                                            Lembaga: {{ $owner->nama }}
                                        @elseif ($owner instanceof \App\Models\RegulatorOrganisasi)
                                            Regulator Org: {{ $owner->nama }}
                                        @elseif ($owner instanceof \App\Models\User)
                                            Pengguna: {{ $owner->name }} ({{ $owner->role }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-sm">{{ implode(', ', $key->scopes ?? []) }}</td>
                                    <td>
                                        @if ($key->require_signature)
                                            <span class="badge badge-success">Wajib HMAC</span>
                                        @else
                                            <span class="badge badge-warning">Token Saja</span>
                                        @endif
                                    </td>
                                    <td class="text-sm">{{ optional($key->last_used_at)->format('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        @if ($key->revoked_at)
                                            <span class="badge badge-danger">Dicabut</span>
                                        @else
                                            <span class="badge badge-success">Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!$key->revoked_at)
                                            <div class="flex flex-wrap gap-2">
                                                <form method="POST" action="{{ route('admin.api_keys.rotate_signature', $key) }}">
                                                    @csrf
                                                    <button class="btn-secondary" type="submit">Putar Ulang HMAC</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.api_keys.revoke', $key) }}">
                                                    @csrf
                                                    <button class="btn-secondary" type="submit">Cabut</button>
                                                </form>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="7">Belum ada kunci API.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $apiKeys->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
