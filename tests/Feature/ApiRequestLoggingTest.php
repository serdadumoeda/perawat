<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use App\Models\InstitusiPendidikan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRequestLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_successful_api_request_with_key_context(): void
    {
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $token = 'valid-logger-token';

        $apiKey = ApiKey::create([
            'name' => 'Logger Key',
            'token_hash' => hash('sha256', $token),
            'owner_type' => InstitusiPendidikan::class,
            'owner_id' => $institusi->id,
            'scopes' => ['lulusan:write'],
        ]);

        $payload = [
            'data' => [
                [
                    'nik' => '1234567890123456',
                    'nama' => 'Siti Aminah',
                ],
            ],
        ];

        $this->postJson('/api/v1/lulusan', $payload, ['X-API-KEY' => $token])
            ->assertOk();

        $expectedHash = hash(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION)
        );

        $this->assertDatabaseHas('api_request_logs', [
            'api_key_id' => $apiKey->id,
            'endpoint' => '/api/v1/lulusan',
            'status_code' => 200,
            'payload_hash' => $expectedHash,
        ]);

        $log = ApiRequestLog::firstOrFail();
        $this->assertSame('POST', $log->method);
        $this->assertNotNull($log->response_time_ms);
        $this->assertNull($log->error_message);
    }

    public function test_it_logs_unauthorized_api_request(): void
    {
        $payload = [
            'data' => [
                [
                    'nik' => '1234567890123456',
                    'nama' => 'Siti Aminah',
                ],
            ],
        ];

        $this->postJson('/api/v1/lulusan', $payload)
            ->assertStatus(401);

        $this->assertDatabaseHas('api_request_logs', [
            'api_key_id' => null,
            'endpoint' => '/api/v1/lulusan',
            'status_code' => 401,
            'error_message' => 'Unauthorized',
        ]);
    }
}
