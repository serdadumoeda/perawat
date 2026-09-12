<?php

namespace Tests\Feature;

use App\Jobs\DeliverWebhookJob;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookDeadLetterRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_retry_dead_letter_delivery(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $subscription = WebhookSubscription::create([
            'name' => 'Partner Legalitas',
            'target_url' => 'https://partner.example.com/webhooks/legalitas',
            'events' => [WebhookSubscription::EVENT_LEGALITAS_VERIFIED],
            'is_active' => true,
        ]);

        $delivery = WebhookDelivery::create([
            'webhook_subscription_id' => $subscription->id,
            'event' => WebhookSubscription::EVENT_LEGALITAS_VERIFIED,
            'attempt_number' => 3,
            'max_attempts' => 3,
            'payload_hash' => hash('sha256', 'payload'),
            'request_payload' => [
                'event' => WebhookSubscription::EVENT_LEGALITAS_VERIFIED,
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'entity' => 'data_legalitas',
                    'id' => 'sample-id',
                    'perawat_nik' => '1234567890123456',
                    'status' => 'verified',
                ],
            ],
            'status_code' => 500,
            'response_time_ms' => 100,
            'is_success' => false,
            'is_dead_letter' => true,
            'error_message' => 'Server error',
            'delivered_at' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.integration_playground.index'))
            ->post(route('admin.integration_playground.deliveries.retry', $delivery))
            ->assertRedirect(route('admin.integration_playground.index'))
            ->assertSessionHas('status', 'Dead-letter berhasil dijadwalkan ulang.');

        Queue::assertPushed(DeliverWebhookJob::class, function (DeliverWebhookJob $job) use ($subscription) {
            return $job->subscriptionId === $subscription->id
                && $job->event === WebhookSubscription::EVENT_LEGALITAS_VERIFIED
                && ($job->payload['perawat_nik'] ?? null) === '1234567890123456'
                && $job->attemptNumber === 1;
        });
    }

    public function test_retry_dead_letter_rejects_non_dead_letter_row(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $subscription = WebhookSubscription::create([
            'name' => 'Partner Legalitas',
            'target_url' => 'https://partner.example.com/webhooks/legalitas',
            'events' => [WebhookSubscription::EVENT_LEGALITAS_VERIFIED],
            'is_active' => true,
        ]);

        $delivery = WebhookDelivery::create([
            'webhook_subscription_id' => $subscription->id,
            'event' => WebhookSubscription::EVENT_LEGALITAS_VERIFIED,
            'attempt_number' => 1,
            'max_attempts' => 3,
            'payload_hash' => hash('sha256', 'payload'),
            'request_payload' => [
                'event' => WebhookSubscription::EVENT_LEGALITAS_VERIFIED,
                'timestamp' => now()->toIso8601String(),
                'data' => ['entity' => 'data_legalitas'],
            ],
            'status_code' => 200,
            'response_time_ms' => 60,
            'is_success' => true,
            'is_dead_letter' => false,
            'error_message' => null,
            'delivered_at' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.integration_playground.index'))
            ->post(route('admin.integration_playground.deliveries.retry', $delivery))
            ->assertRedirect(route('admin.integration_playground.index'))
            ->assertSessionHasErrors('webhook');

        Queue::assertNothingPushed();
    }
}
