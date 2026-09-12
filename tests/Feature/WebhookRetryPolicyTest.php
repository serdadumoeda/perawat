<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookRetryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_retries_with_backoff_until_success(): void
    {
        config([
            'api.webhook_retry_attempts' => 3,
            'api.webhook_retry_backoff_seconds' => [10, 30, 60],
        ]);

        Http::fake([
            'https://partner.example.com/*' => Http::sequence()
                ->push('server error', 500)
                ->push('bad gateway', 502)
                ->push('ok', 200),
        ]);

        $subscription = WebhookSubscription::create([
            'name' => 'Partner Legalitas',
            'target_url' => 'https://partner.example.com/webhooks/legalitas',
            'events' => [WebhookSubscription::EVENT_LEGALITAS_VERIFIED],
            'is_active' => true,
        ]);

        $legalitas = $this->createSubmittedLegalitas();
        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        $deliveries = WebhookDelivery::query()
            ->where('webhook_subscription_id', $subscription->id)
            ->orderBy('attempt_number')
            ->get();

        $this->assertCount(3, $deliveries);
        $this->assertFalse((bool) $deliveries[0]->is_success);
        $this->assertSame(1, (int) $deliveries[0]->attempt_number);
        $this->assertNotNull($deliveries[0]->next_retry_at);
        $this->assertFalse((bool) $deliveries[0]->is_dead_letter);

        $this->assertFalse((bool) $deliveries[1]->is_success);
        $this->assertSame(2, (int) $deliveries[1]->attempt_number);
        $this->assertNotNull($deliveries[1]->next_retry_at);
        $this->assertFalse((bool) $deliveries[1]->is_dead_letter);

        $this->assertTrue((bool) $deliveries[2]->is_success);
        $this->assertSame(3, (int) $deliveries[2]->attempt_number);
        $this->assertNull($deliveries[2]->next_retry_at);
        $this->assertFalse((bool) $deliveries[2]->is_dead_letter);

        $subscription->refresh();
        $this->assertSame(0, (int) $subscription->consecutive_failures);
        $this->assertNotNull($subscription->last_sent_at);
    }

    public function test_webhook_marks_dead_letter_when_all_retries_fail(): void
    {
        config([
            'api.webhook_retry_attempts' => 3,
            'api.webhook_retry_backoff_seconds' => [10, 30, 60],
        ]);

        Http::fake([
            'https://partner.example.com/*' => Http::sequence()
                ->push('server error', 500)
                ->push('server error', 500)
                ->push('server error', 500),
        ]);

        $subscription = WebhookSubscription::create([
            'name' => 'Partner Legalitas',
            'target_url' => 'https://partner.example.com/webhooks/legalitas',
            'events' => [WebhookSubscription::EVENT_LEGALITAS_VERIFIED],
            'is_active' => true,
        ]);

        $legalitas = $this->createSubmittedLegalitas();
        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('webhook_deliveries', [
            'webhook_subscription_id' => $subscription->id,
            'attempt_number' => 3,
            'max_attempts' => 3,
            'is_success' => false,
            'is_dead_letter' => true,
            'status_code' => 500,
        ]);

        $subscription->refresh();
        $this->assertSame(3, (int) $subscription->consecutive_failures);
    }

    private function createSubmittedLegalitas(): DataLegalitas
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        return DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-123',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);
    }
}
