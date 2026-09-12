<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Models\User;
use App\Models\WebhookSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifiedWebhookDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_is_sent_when_legalitas_data_becomes_verified(): void
    {
        Http::fake([
            'https://partner.example.com/*' => Http::response('ok', 200),
        ]);

        $subscription = WebhookSubscription::create([
            'name' => 'Partner Legalitas',
            'target_url' => 'https://partner.example.com/webhooks/legalitas',
            'events' => [WebhookSubscription::EVENT_LEGALITAS_VERIFIED],
            'is_active' => true,
        ]);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-123',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'approve',
            ])
            ->assertRedirect();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://partner.example.com/webhooks/legalitas'
                && $request['event'] === WebhookSubscription::EVENT_LEGALITAS_VERIFIED
                && ($request['data']['perawat_nik'] ?? null) === '1234567890123456';
        });

        $this->assertDatabaseHas('webhook_deliveries', [
            'webhook_subscription_id' => $subscription->id,
            'event' => WebhookSubscription::EVENT_LEGALITAS_VERIFIED,
            'is_success' => true,
            'status_code' => 200,
        ]);
    }
}
