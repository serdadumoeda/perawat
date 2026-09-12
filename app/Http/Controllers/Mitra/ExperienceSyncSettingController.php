<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\UpdateExperienceSyncSettingRequest;
use App\Models\ExperienceSyncSetting;
use App\Models\MitraPengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperienceSyncSettingController extends Controller
{
    public function index(Request $request): View
    {
        $mitraList = $request->user()->mitraPengguna()->orderBy('nama')->get();
        $selectedId = $request->query('mitra_id') ?: $mitraList->first()?->id;

        $setting = null;
        if ($selectedId) {
            $this->ensureMitraAccess($request, $selectedId);
            $setting = ExperienceSyncSetting::firstOrCreate(
                ['mitra_pengguna_id' => $selectedId],
                ['is_active' => false, 'sync_mode' => ExperienceSyncSetting::MODE_PULL]
            );
        }

        return view('mitra.experience_sync.index', [
            'mitraList' => $mitraList,
            'selectedId' => $selectedId,
            'setting' => $setting,
        ]);
    }

    public function update(UpdateExperienceSyncSettingRequest $request): RedirectResponse
    {
        $mitraId = $request->validated('mitra_pengguna_id');
        $this->ensureMitraAccess($request, $mitraId);

        $setting = ExperienceSyncSetting::firstOrCreate(
            ['mitra_pengguna_id' => $mitraId],
            ['is_active' => false, 'sync_mode' => ExperienceSyncSetting::MODE_PULL]
        );

        $setting->update([
            'base_url' => $request->validated('base_url'),
            'api_key' => $request->validated('api_key'),
            'client_id' => $request->validated('client_id'),
            'client_secret' => $request->validated('client_secret'),
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
            'sync_mode' => $request->validated('sync_mode'),
            'is_active' => (bool) $request->validated('is_active', false),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()
            ->route('mitra.experience_sync.index', ['mitra_id' => $mitraId])
            ->with('status', 'Pengaturan sync pengalaman kerja berhasil disimpan.');
    }

    private function ensureMitraAccess(Request $request, string $mitraId): void
    {
        $exists = $request->user()
            ->mitraPengguna()
            ->where('mitra_pengguna.id', $mitraId)
            ->exists();

        if (!$exists) {
            abort(403, 'Mitra tidak sesuai dengan akun ini.');
        }
    }
}
