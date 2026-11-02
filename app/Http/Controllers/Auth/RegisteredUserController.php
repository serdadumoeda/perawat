<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Perawat;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', Rule::in(User::REGISTRABLE_ROLES)],
            'nik' => ['nullable', 'string', 'digits:16', 'required_if:role,perawat'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $perawatNik = null;
        $perawatId = null;
        if ($request->role === User::ROLE_PERAWAT) {
            $nikInput = (string) $request->nik;

            // Cari data perawat berdasarkan NIK
            $perawat = Perawat::where('nik', $nikInput)->first();

            // Jika NIK ditemukan dengan prefix TMP- (import kampus tanpa NIK asli), update NIK-nya
            if ($perawat && str_starts_with($perawat->nik, 'TMP-')) {
                $oldNik = $perawat->nik;
                $perawat->nik = $nikInput;
                $perawat->save();

                \App\Models\RiwayatPendidikan::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\DataLegalitas::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\SertifikasiKompetensi::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\RiwayatPekerjaan::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\Lamaran::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\Rekrutmen::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\TracingAlumni::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\PelatihanPeserta::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\CareerEvent::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                \App\Models\StrSyncRequest::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
                User::where('perawat_nik', $oldNik)->update(['perawat_nik' => $nikInput]);
            }

            // Jika NIK sudah terhubung ke akun lain, tolak
            if ($perawat && User::where('perawat_nik', $perawat->nik)->exists()) {
                return back()->withErrors(['nik' => 'NIK ini sudah terhubung dengan akun lain. Silakan login atau hubungi administrator.'])->onlyInput('nik');
            }

            // Jika belum ada data perawat, buat profil baru secara otomatis (self-registration)
            if (!$perawat) {
                $perawat = Perawat::create([
                    'nik'                    => $nikInput,
                    'nama'                   => $request->name,
                    'email'                  => $request->email,
                    'status_kesiapan'        => Perawat::KESIAPAN_DRAFT,
                    'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_FRESH,
                    'status_ketersediaan'    => Perawat::KETERSEDIAAN_AVAILABLE,
                ]);
            }

            $perawatNik = $perawat->nik;
            $perawatId = $perawat->id;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'status' => User::STATUS_PENDING,
            'perawat_nik' => $perawatNik,
            'perawat_id' => $perawatId,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
