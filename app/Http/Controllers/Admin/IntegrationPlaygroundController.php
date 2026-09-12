<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DeliverWebhookJob;
use App\Models\ApiRequestLog;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IntegrationPlaygroundController extends Controller
{
    public function index(): View
    {
        $apiRequestLogs = ApiRequestLog::query()
            ->with('apiKey.owner')
            ->latest()
            ->limit(50)
            ->get();

        $subscriptions = WebhookSubscription::query()
            ->with(['deliveries' => function ($query) {
                $query->latest()->limit(3);
            }])
            ->latest()
            ->get();

        $recentDeliveries = WebhookDelivery::query()
            ->with('webhookSubscription')
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.integrations.playground', [
            'apiRequestLogs' => $apiRequestLogs,
            'subscriptions' => $subscriptions,
            'recentDeliveries' => $recentDeliveries,
            'baseUrl' => rtrim((string) config('app.url'), '/'),
            'webhookEvents' => WebhookSubscription::EVENTS,
        ]);
    }

    public function retryDeadLetter(WebhookDelivery $webhookDelivery): RedirectResponse
    {
        if (!$webhookDelivery->is_dead_letter) {
            return back()->withErrors(['webhook' => 'Hanya data dead-letter yang bisa di-retry.']);
        }

        $subscription = $webhookDelivery->webhookSubscription;
        if (!$subscription || !$subscription->is_active) {
            return back()->withErrors(['webhook' => 'Subscription tidak aktif atau tidak ditemukan.']);
        }

        $requestPayload = $webhookDelivery->request_payload;
        $event = data_get($requestPayload, 'event');
        $timestamp = data_get($requestPayload, 'timestamp');
        $data = data_get($requestPayload, 'data');

        if (!is_string($event) || $event === '' || !is_string($timestamp) || !is_array($data)) {
            return back()->withErrors(['webhook' => 'Request payload webhook tidak lengkap untuk retry.']);
        }

        DeliverWebhookJob::dispatch(
            subscriptionId: $subscription->id,
            event: $event,
            payload: $data,
            eventTimestamp: $timestamp,
            attemptNumber: 1
        );

        return back()->with('status', 'Dead-letter berhasil dijadwalkan ulang.');
    }
}
