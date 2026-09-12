<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $subscriptionId,
        public string $event,
        public array $payload,
        public string $eventTimestamp,
        public int $attemptNumber = 1
    ) {
    }

    public function handle(): void
    {
        $subscription = WebhookSubscription::query()->find($this->subscriptionId);
        if (!$subscription || !$subscription->is_active || !$subscription->listensTo($this->event)) {
            return;
        }

        $requestBody = [
            'event' => $this->event,
            'timestamp' => $this->eventTimestamp,
            'data' => $this->payload,
        ];

        $jsonPayload = json_encode($requestBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        $payloadHash = hash('sha256', $jsonPayload);
        $startedAt = microtime(true);
        $maxAttempts = max(1, (int) config('api.webhook_retry_attempts', 3));
        $attempt = max(1, $this->attemptNumber);

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $this->event,
            'X-Webhook-Payload-Hash' => $payloadHash,
        ];

        if ($subscription->signing_secret) {
            $headers['X-Webhook-Signature'] = 'sha256='.hash_hmac(
                'sha256',
                $jsonPayload,
                (string) $subscription->signing_secret
            );
        }

        try {
            $response = Http::timeout((int) config('api.webhook_timeout_seconds', 8))
                ->withHeaders($headers)
                ->withBody($jsonPayload, 'application/json')
                ->post($subscription->target_url);

            if ($response->successful()) {
                $this->storeDelivery(
                    subscription: $subscription,
                    payloadHash: $payloadHash,
                    requestPayload: $requestBody,
                    statusCode: $response->status(),
                    responseTimeMs: (int) round((microtime(true) - $startedAt) * 1000),
                    errorMessage: null,
                    attempt: $attempt,
                    maxAttempts: $maxAttempts,
                    nextRetryAt: null,
                    isDeadLetter: false
                );

                $subscription->last_sent_at = now();
                $subscription->consecutive_failures = 0;
                $subscription->save();

                return;
            }

            $this->handleFailure(
                subscription: $subscription,
                payloadHash: $payloadHash,
                requestPayload: $requestBody,
                statusCode: $response->status(),
                responseTimeMs: (int) round((microtime(true) - $startedAt) * 1000),
                errorMessage: mb_substr((string) $response->body(), 0, 2000),
                attempt: $attempt,
                maxAttempts: $maxAttempts
            );
        } catch (\Throwable $error) {
            $this->handleFailure(
                subscription: $subscription,
                payloadHash: $payloadHash,
                requestPayload: $requestBody,
                statusCode: null,
                responseTimeMs: (int) round((microtime(true) - $startedAt) * 1000),
                errorMessage: mb_substr($error->getMessage(), 0, 2000),
                attempt: $attempt,
                maxAttempts: $maxAttempts
            );
        }
    }

    private function handleFailure(
        WebhookSubscription $subscription,
        string $payloadHash,
        array $requestPayload,
        ?int $statusCode,
        int $responseTimeMs,
        ?string $errorMessage,
        int $attempt,
        int $maxAttempts
    ): void {
        $shouldRetry = $attempt < $maxAttempts;
        $delay = $shouldRetry ? $this->resolveBackoffSeconds($attempt) : 0;
        $nextRetryAt = $shouldRetry ? now()->addSeconds($delay) : null;

        $this->storeDelivery(
            subscription: $subscription,
            payloadHash: $payloadHash,
            requestPayload: $requestPayload,
            statusCode: $statusCode,
            responseTimeMs: $responseTimeMs,
            errorMessage: $errorMessage,
            attempt: $attempt,
            maxAttempts: $maxAttempts,
            nextRetryAt: $nextRetryAt,
            isDeadLetter: !$shouldRetry
        );

        $subscription->consecutive_failures = $subscription->consecutive_failures + 1;
        $subscription->save();

        if ($shouldRetry) {
            self::dispatch(
                subscriptionId: $this->subscriptionId,
                event: $this->event,
                payload: $this->payload,
                eventTimestamp: $this->eventTimestamp,
                attemptNumber: $attempt + 1
            )->delay($nextRetryAt);
        }
    }

    private function resolveBackoffSeconds(int $attempt): int
    {
        $backoff = config('api.webhook_retry_backoff_seconds', [30, 120, 300]);
        if (!is_array($backoff) || $backoff === []) {
            return 0;
        }

        $index = max(0, $attempt - 1);
        $resolved = $backoff[$index] ?? end($backoff);

        return max(0, (int) $resolved);
    }

    private function storeDelivery(
        WebhookSubscription $subscription,
        string $payloadHash,
        array $requestPayload,
        ?int $statusCode,
        int $responseTimeMs,
        ?string $errorMessage,
        int $attempt,
        int $maxAttempts,
        ?\Illuminate\Support\Carbon $nextRetryAt,
        bool $isDeadLetter
    ): void {
        WebhookDelivery::create([
            'webhook_subscription_id' => $subscription->id,
            'event' => $this->event,
            'attempt_number' => $attempt,
            'max_attempts' => $maxAttempts,
            'payload_hash' => $payloadHash,
            'request_payload' => $requestPayload,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'is_success' => $statusCode !== null && $statusCode >= 200 && $statusCode < 300,
            'is_dead_letter' => $isDeadLetter,
            'error_message' => $errorMessage,
            'delivered_at' => now(),
            'next_retry_at' => $nextRetryAt,
        ]);
    }
}
