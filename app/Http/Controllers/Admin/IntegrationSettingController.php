<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateIntegrationSettingRequest;
use App\Models\IntegrationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IntegrationSettingController extends Controller
{
    public function index(): View
    {
        $settings = collect(IntegrationSetting::PROVIDERS)
            ->map(function (string $provider) {
                return IntegrationSetting::firstOrCreate(
                    ['provider' => $provider],
                    ['is_active' => false]
                );
            })
            ->keyBy('provider');

        return view('admin.integrations.index', [
            'settings' => $settings,
        ]);
    }

    public function update(UpdateIntegrationSettingRequest $request, string $provider): RedirectResponse
    {
        $setting = IntegrationSetting::firstOrCreate(
            ['provider' => $provider],
            ['is_active' => false]
        );

        $setting->update([
            'base_url' => $request->validated('base_url'),
            'api_key' => $request->validated('api_key'),
            'client_id' => $request->validated('client_id'),
            'client_secret' => $request->validated('client_secret'),
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
            'is_active' => (bool) $request->validated('is_active', false),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()
            ->route('admin.integrations.index')
            ->with('status', 'Pengaturan integrasi berhasil disimpan.');
    }
}
