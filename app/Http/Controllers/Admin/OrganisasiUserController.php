<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrganisasiUserRequest;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\MitraPengguna;
use App\Models\RegulatorOrganisasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganisasiUserController extends Controller
{
    public function create(): View
    {
        return view('admin.mapping.create', [
            'users' => User::whereIn('role', [
                User::ROLE_KAMPUS,
                User::ROLE_LEMBAGA,
                User::ROLE_MITRA,
                User::ROLE_FASKES,
                User::ROLE_HEADHUNTER,
                User::ROLE_REGULATOR,
            ])
                ->orderBy('name')
                ->get(),
            'institusi' => InstitusiPendidikan::orderBy('nama')->get(),
            'lembaga' => LembagaPelatihan::orderBy('nama')->get(),
            'mitra' => MitraPengguna::orderBy('nama')->get(),
            'regulatorOrganisasi' => RegulatorOrganisasi::orderBy('nama')->get(),
        ]);
    }

    public function store(StoreOrganisasiUserRequest $request): RedirectResponse
    {
        $user = User::findOrFail($request->validated('user_id'));
        $type = $request->validated('organisasi_type');
        $orgId = $request->validated('organisasi_id');

        if ($type === 'institusi') {
            if (!InstitusiPendidikan::whereKey($orgId)->exists()) {
                return back()->withErrors(['organisasi_id' => 'Institusi tidak ditemukan.']);
            }
            if ($user->role !== User::ROLE_KAMPUS) {
                return back()->withErrors(['user_id' => 'User bukan role kampus.']);
            }
            $this->attachUserToOrganisasi($user->id, $orgId, InstitusiPendidikan::class);
        }

        if ($type === 'lembaga') {
            if (!LembagaPelatihan::whereKey($orgId)->exists()) {
                return back()->withErrors(['organisasi_id' => 'Lembaga tidak ditemukan.']);
            }
            if ($user->role !== User::ROLE_LEMBAGA) {
                return back()->withErrors(['user_id' => 'User bukan role lembaga.']);
            }
            $this->attachUserToOrganisasi($user->id, $orgId, LembagaPelatihan::class);
        }

        if ($type === 'mitra') {
            if (!MitraPengguna::whereKey($orgId)->exists()) {
                return back()->withErrors(['organisasi_id' => 'Mitra tidak ditemukan.']);
            }
            if (!in_array($user->role, [User::ROLE_MITRA, User::ROLE_FASKES, User::ROLE_HEADHUNTER], true)) {
                return back()->withErrors(['user_id' => 'User bukan role mitra/faskes/headhunter.']);
            }
            $this->attachUserToOrganisasi($user->id, $orgId, MitraPengguna::class);
        }

        if ($type === 'regulator') {
            if (!RegulatorOrganisasi::whereKey($orgId)->exists()) {
                return back()->withErrors(['organisasi_id' => 'Organisasi regulator tidak ditemukan.']);
            }
            if ($user->role !== User::ROLE_REGULATOR) {
                return back()->withErrors(['user_id' => 'User bukan role regulator.']);
            }
            $this->attachUserToOrganisasi($user->id, $orgId, RegulatorOrganisasi::class);
        }

        return redirect()->route('admin.mapping.create')->with('status', 'Mapping berhasil disimpan.');
    }

    private function attachUserToOrganisasi(string $userId, string $organisasiId, string $organisasiType): void
    {
        $exists = DB::table('organisasi_user')
            ->where('user_id', $userId)
            ->where('organisasiable_id', $organisasiId)
            ->where('organisasiable_type', $organisasiType)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();

        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'organisasiable_id' => $organisasiId,
            'organisasiable_type' => $organisasiType,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
