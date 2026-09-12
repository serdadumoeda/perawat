<?php

namespace Tests\Feature;

use App\Models\ApiIdempotencyKey;
use App\Models\ApiKey;
use App\Models\InstitusiPendidikan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiIdempotencyPurgeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_purges_expired_and_stale_in_progress_idempotency_records(): void
    {
        config([
            'api.idempotency_stale_in_progress_minutes' => 120,
            'api.idempotency_purge_batch_size' => 1000,
        ]);

        $apiKey = $this->createApiKey();

        $expired = $this->createEntry(
            apiKeyId: $apiKey->id,
            key: 'expired-key',
            expiresAt: now()->subMinute(),
            completedAt: now()->subMinutes(2),
            createdAt: now()->subHour()
        );

        $staleInProgress = $this->createEntry(
            apiKeyId: $apiKey->id,
            key: 'stale-key',
            expiresAt: now()->addDay(),
            completedAt: null,
            createdAt: now()->subMinutes(180)
        );

        $activeCompleted = $this->createEntry(
            apiKeyId: $apiKey->id,
            key: 'active-completed',
            expiresAt: now()->addDay(),
            completedAt: now()->subMinute(),
            createdAt: now()->subMinutes(10)
        );

        $activeInProgress = $this->createEntry(
            apiKeyId: $apiKey->id,
            key: 'active-pending',
            expiresAt: now()->addDay(),
            completedAt: null,
            createdAt: now()->subMinutes(20)
        );

        $this->artisan('api:idempotency:purge')
            ->expectsOutputToContain('Purge idempotency selesai')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('api_idempotency_keys', ['id' => $expired->id]);
        $this->assertDatabaseMissing('api_idempotency_keys', ['id' => $staleInProgress->id]);
        $this->assertDatabaseHas('api_idempotency_keys', ['id' => $activeCompleted->id]);
        $this->assertDatabaseHas('api_idempotency_keys', ['id' => $activeInProgress->id]);
    }

    private function createApiKey(): ApiKey
    {
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus Purge']);

        return ApiKey::create([
            'name' => 'Purge Key',
            'token_hash' => hash('sha256', Str::random(32)),
            'owner_type' => InstitusiPendidikan::class,
            'owner_id' => $institusi->id,
            'scopes' => ['lulusan:write'],
        ]);
    }

    private function createEntry(
        string $apiKeyId,
        string $key,
        \Illuminate\Support\Carbon $expiresAt,
        ?\Illuminate\Support\Carbon $completedAt,
        \Illuminate\Support\Carbon $createdAt
    ): ApiIdempotencyKey {
        return ApiIdempotencyKey::create([
            'api_key_id' => $apiKeyId,
            'method' => 'POST',
            'endpoint' => '/api/v1/lulusan',
            'idempotency_key' => $key,
            'request_hash' => hash('sha256', $key),
            'response_status_code' => $completedAt ? 200 : null,
            'response_body' => $completedAt ? '{"processed":1}' : null,
            'completed_at' => $completedAt,
            'expires_at' => $expiresAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
