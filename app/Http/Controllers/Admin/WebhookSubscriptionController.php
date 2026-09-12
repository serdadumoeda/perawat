<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WebhookSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        WebhookSubscription::create($validated);

        return back()->with('status', 'Webhook subscription berhasil dibuat.');
    }

    public function update(Request $request, WebhookSubscription $webhookSubscription): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        if (!$request->filled('signing_secret')) {
            unset($validated['signing_secret']);
        }
        $webhookSubscription->update($validated);

        return back()->with('status', 'Webhook subscription berhasil diperbarui.');
    }

    public function destroy(WebhookSubscription $webhookSubscription): RedirectResponse
    {
        $webhookSubscription->delete();

        return back()->with('status', 'Webhook subscription berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_url' => ['required', 'url', 'max:255'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', Rule::in(array_merge(WebhookSubscription::EVENTS, ['*']))],
            'signing_secret' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
