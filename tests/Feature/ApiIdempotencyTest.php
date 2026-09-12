<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\InstitusiPendidikan;
use App\Models\Perawat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_idempotency_key_and_payload_replays_previous_response(): void
    {
        [$token] = $this->createLulusanApiKey();

        $payload = [
            'data' => [
                ['nik' => '1234567890123456', 'nama' => 'Siti Aminah'],
            ],
        ];

        $headers = [
            'X-API-KEY' => $token,
            'Idempotency-Key' => 'lulusan-1234567890123456-1',
        ];

        $first = $this->postJson('/api/v1/lulusan', $payload, $headers)->assertOk();
        $second = $this->postJson('/api/v1/lulusan', $payload, $headers)->assertOk();

        $second->assertHeader('X-Idempotency-Replayed', 'true');
        $second->assertHeader('X-Idempotency-Key', 'lulusan-1234567890123456-1');
        $this->assertSame($first->getContent(), $second->getContent());

        $this->assertDatabaseCount('api_idempotency_keys', 1);
    }

    public function test_same_idempotency_key_with_different_payload_returns_conflict(): void
    {
        [$token] = $this->createLulusanApiKey();

        $headers = [
            'X-API-KEY' => $token,
            'Idempotency-Key' => 'lulusan-duplicate',
        ];

        $this->postJson('/api/v1/lulusan', [
            'data' => [
                ['nik' => '1234567890123456', 'nama' => 'Siti Aminah'],
            ],
        ], $headers)->assertOk();

        $this->postJson('/api/v1/lulusan', [
            'data' => [
                ['nik' => '1234567890123456', 'nama' => 'Nama Berbeda'],
            ],
        ], $headers)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Idempotency-Key conflict: payload tidak sama.');

        $this->assertDatabaseCount('api_idempotency_keys', 1);
    }

    public function test_legalitas_endpoint_replays_response_for_same_idempotency_key(): void
    {
        [$token] = $this->createLegalitasApiKey();

        Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $payload = [
            'data' => [
                [
                    'nik' => '1234567890123456',
                    'nomor_str' => 'STR-LEGAL-001',
                    'status_str' => 'aktif',
                    'str_berlaku_sampai' => now()->addYear()->toDateString(),
                ],
            ],
        ];

        $headers = [
            'X-API-KEY' => $token,
            'Idempotency-Key' => 'legalitas-1234567890123456-1',
        ];

        $first = $this->postJson('/api/v1/legalitas', $payload, $headers)->assertOk();
        $second = $this->postJson('/api/v1/legalitas', $payload, $headers)->assertOk();

        $second->assertHeader('X-Idempotency-Replayed', 'true');
        $second->assertHeader('X-Idempotency-Key', 'legalitas-1234567890123456-1');
        $this->assertSame($first->getContent(), $second->getContent());

        $this->assertDatabaseCount('api_idempotency_keys', 1);
        $this->assertDatabaseHas('data_legalitas', [
            'perawat_nik' => '1234567890123456',
            'nomor_str' => 'STR-LEGAL-001',
            'source_system' => 'regulator_api',
        ]);
    }

    public function test_legalitas_endpoint_rejects_conflicting_payload_for_same_idempotency_key(): void
    {
        [$token] = $this->createLegalitasApiKey();

        Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $headers = [
            'X-API-KEY' => $token,
            'Idempotency-Key' => 'legalitas-duplicate',
        ];

        $this->postJson('/api/v1/legalitas', [
            'data' => [
                [
                    'nik' => '1234567890123456',
                    'nomor_str' => 'STR-OLD',
                    'status_str' => 'aktif',
                ],
            ],
        ], $headers)->assertOk();

        $this->postJson('/api/v1/legalitas', [
            'data' => [
                [
                    'nik' => '1234567890123456',
                    'nomor_str' => 'STR-NEW',
                    'status_str' => 'expired',
                ],
            ],
        ], $headers)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Idempotency-Key conflict: payload tidak sama.');

        $this->assertDatabaseCount('api_idempotency_keys', 1);
        $this->assertDatabaseHas('data_legalitas', [
            'perawat_nik' => '1234567890123456',
            'nomor_str' => 'STR-OLD',
        ]);
    }

    private function createLulusanApiKey(): array
    {
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus API']);
        $token = 'idempotency-token-1';

        ApiKey::create([
            'name' => 'Idempotency Key',
            'token_hash' => hash('sha256', $token),
            'owner_type' => InstitusiPendidikan::class,
            'owner_id' => $institusi->id,
            'scopes' => ['lulusan:write'],
        ]);

        return [$token];
    }

    private function createLegalitasApiKey(): array
    {
        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);
        $token = 'legalitas-idempotency-token-1';

        ApiKey::create([
            'name' => 'Legalitas Key',
            'token_hash' => hash('sha256', $token),
            'owner_type' => User::class,
            'owner_id' => $regulator->id,
            'scopes' => ['legalitas:write'],
        ]);

        return [$token];
    }
}
