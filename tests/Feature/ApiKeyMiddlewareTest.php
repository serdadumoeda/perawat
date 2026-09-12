<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\LembagaPelatihan;
use App\Models\Perawat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_key(): void
    {
        $payload = [
            'data' => [
                ['nik' => '1234567890123456', 'nama' => 'Siti Aminah'],
            ],
        ];

        $this->postJson('/api/v1/lulusan', $payload)
            ->assertStatus(401);
    }

    public function test_api_rejects_invalid_scope(): void
    {
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $token = 'test-token-scope';

        ApiKey::create([
            'name' => 'Test Key',
            'token_hash' => hash('sha256', $token),
            'owner_type' => LembagaPelatihan::class,
            'owner_id' => $lembaga->id,
            'scopes' => ['lulusan:write'],
        ]);

        $payload = [
            'data' => [
                [
                    'nik' => '1234567890123456',
                    'nama_sertifikat' => 'Pelatihan ICU',
                ],
            ],
        ];

        $this->postJson('/api/v1/sertifikasi', $payload, ['X-API-KEY' => $token])
            ->assertStatus(403);
    }

    public function test_api_accepts_valid_scope(): void
    {
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $token = 'test-token-valid';

        ApiKey::create([
            'name' => 'Test Key',
            'token_hash' => hash('sha256', $token),
            'owner_type' => LembagaPelatihan::class,
            'owner_id' => $lembaga->id,
            'scopes' => ['sertifikasi:write'],
        ]);

        Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $payload = [
            'data' => [
                [
                    'nik' => '1234567890123456',
                    'nama_sertifikat' => 'Pelatihan ICU',
                ],
            ],
        ];

        $this->postJson('/api/v1/sertifikasi', $payload, ['X-API-KEY' => $token])
            ->assertStatus(200)
            ->assertJson([
                'processed' => 1,
            ]);
    }
}
