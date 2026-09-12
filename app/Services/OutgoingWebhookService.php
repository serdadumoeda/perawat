<?php

namespace App\Services;

use App\Jobs\DeliverWebhookJob;
use App\Models\WebhookSubscription;

class OutgoingWebhookService
{
    public function dispatch(string $event, array $payload): void
    {
        $subscriptions = WebhookSubscription::query()
            ->where('is_active', true)
            ->orderBy('created_at')
            ->get()
            ->filter(fn (WebhookSubscription $subscription) => $subscription->listensTo($event));

        $eventTimestamp = now()->toIso8601String();

        foreach ($subscriptions as $subscription) {
            DeliverWebhookJob::dispatch(
                subscriptionId: $subscription->id,
                event: $event,
                payload: $payload,
                eventTimestamp: $eventTimestamp,
            );
        }
    }
}
