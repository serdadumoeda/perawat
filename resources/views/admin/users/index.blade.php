<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-title">Manajemen User</h2>
            <p class="section-subtitle">Kelola akun, role, dan status akses pengguna.</p>
        </div>
    </x-slot>

    <div class="module-shell">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 module-container">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="module-card card-surface p-4 sm:p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="q" :value="__('Cari')" />
                        <x-text-input id="q" class="mt-2" type="text" name="q" :value="request('q')" placeholder="Nama / Email" />
                    </div>
                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" class="select-field mt-2">
                            <option value="">Semua</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="select-field mt-2">
                            <option value="">Semua</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="module-card card-surface p-4 sm:p-6">
                <div class="table-wrap overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php($statusBadge = match($user->status) {
                                    'active' => 'badge badge-success',
                                    'pending' => 'badge badge-warning',
                                    default => 'badge badge-danger',
                                })
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role }}</td>
                                    <td>
                                        <span class="{{ $statusBadge }}">{{ $user->status }}</span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            @if ($user->status !== 'active')
                                                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="table-action table-action-approve">Setujui</button>
                                                </form>
                                            @endif
                                            @if ($user->status !== 'suspended')
                                                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="table-action table-action-reject">Nonaktifkan</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="table-action table-action-reopen">Aktifkan</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.users.reset_password', $user) }}">
                                                @csrf
                                                <button type="submit" class="table-action table-action-secondary">Reset Sandi</button>
                                            </form>
                                            @if (auth()->id() !== $user->id)
                                                <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="table-action table-action-secondary">Impersonasi</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty-state-row" colspan="5">Belum ada user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
