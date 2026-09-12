<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\InstitusiPendidikan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyHmacSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_hmac_signature_rejects_unsigned_request(): void
    {
        [$token] = $this->createSignedApiKey();

        $payload = [
            'data' => [
                ['nik' => '1234567890123456', 'nama' => 'Siti Aminah'],
            ],
        ];

        $this->postJson('/api/v1/lulusan', $payload, ['X-API-KEY' => $token])
            ->assertStatus(401);
    }

    public function test_required_hmac_signature_accepts_valid_signature(): void
    {
        [$token, $secret] = $this->createSignedApiKey();

        $payload = ['data' => [['nik' => '1234567890123456', 'nama' => 'Siti Aminah']]];
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$jsonPayload, $secret);

        $response = $this->call(
            'POST',
            '/api/v1/lulusan',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_API_KEY' => $token,
                'HTTP_X_API_TIMESTAMP' => $timestamp,
                'HTTP_X_API_SIGNATURE' => $signature,
            ],
            $jsonPayload
        );

        $response->assertStatus(200);
    }

    public function test_required_hmac_signature_rejects_invalid_signature(): void
    {
        [$token] = $this->createSignedApiKey();

        $payload = ['data' => [['nik' => '1234567890123456', 'nama' => 'Siti Aminah']]];
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) now()->timestamp;

        $response = $this->call(
            'POST',
            '/api/v1/lulusan',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_API_KEY' => $token,
                'HTTP_X_API_TIMESTAMP' => $timestamp,
                'HTTP_X_API_SIGNATURE' => 'invalid-signature',
            ],
            $jsonPayload
        );

        $response->assertStatus(401);
    }

    private function createSignedApiKey(): array
    {
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $token = 'signed-key-token-1';
        $secret = 'my-signing-secret-123';

        ApiKey::create([
            'name' => 'Signed Key',
            'token_hash' => hash('sha256', $token),
            'owner_type' => InstitusiPendidikan::class,
            'owner_id' => $institusi->id,
            'scopes' => ['lulusan:write'],
            'require_signature' => true,
            'signature_secret' => $secret,
            'signature_last_rotated_at' => now(),
        ]);

        return [$token, $secret];
    }
}
