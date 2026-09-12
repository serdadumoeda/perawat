<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h1 class="form-title">Verifikasi email</h1>
            <p class="form-subtitle">Kami sudah mengirimkan tautan verifikasi ke email Anda.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ __('Tautan verifikasi baru telah dikirim ke email Anda.') }}
            </div>
        @endif

        <div class="rounded-2xl border border-white/60 bg-white/70 p-4 text-sm text-slate-600">
            Cek folder spam jika tidak menerima email dalam 5 menit.
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button>
                    {{ __('Kirim ulang email verifikasi') }}
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="link-muted">
                    {{ __('Keluar') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
