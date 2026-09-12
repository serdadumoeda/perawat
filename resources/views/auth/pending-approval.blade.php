<x-guest-layout>
    <div class="text-center">
        <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-500 mb-4 border border-amber-200">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h2 class="form-title text-xl font-bold text-slate-900 mb-2">Akun Menunggu Aktivasi</h2>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            Terima kasih telah mendaftar di <strong>Platform Keperawatan</strong>. 
            Saat ini akun Anda sedang dalam proses peninjauan oleh administrator sistem.
            Kami biasanya mengaktifkan akun baru dalam waktu kurang dari 24 jam.
        </p>
        
        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 mb-6 text-xs text-start text-slate-500 space-y-2">
            <div class="font-semibold text-slate-700">Detail Akun:</div>
            <div>Nama: {{ auth()->user()->name }}</div>
            <div>Email: {{ auth()->user()->email }}</div>
            <div>Status: <span class="badge badge-warning uppercase font-semibold text-[10px]">Menunggu Approval</span></div>
        </div>

        <div class="flex flex-col gap-2">
            <a href="/" class="btn-primary w-full">{{ __('Refresh Halaman') }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-ghost w-full">
                    {{ __('Keluar dari Akun') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
