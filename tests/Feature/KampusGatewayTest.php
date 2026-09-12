<?php

namespace Tests\Feature;

use App\Models\InstitusiPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KampusGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_gateway_lookup_requires_api_key(): void
    {
        $this->getJson('/api/v1/kampus/gateway/3578000000000001')
            ->assertStatus(401);
    }

    public function test_api_gateway_lookup_by_nik_returns_json(): void
    {
        $institusi = InstitusiPendidikan::create([
            'kode' => '001001',
            'nama' => 'Universitas Keperawatan Indonesia',
        ]);
        $token = 'test-token-lulusan-read';

        \App\Models\ApiKey::create([
            'name' => 'Test Key Read',
            'token_hash' => hash('sha256', $token),
            'owner_type' => InstitusiPendidikan::class,
            'owner_id' => $institusi->id,
            'scopes' => ['lulusan:read'],
        ]);

        $response = $this->getJson('/api/v1/kampus/gateway/3578000000000001', ['X-API-KEY' => $token]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'found' => true,
                'nik' => '3578000000000001',
            ]);
    }

    public function test_web_gateway_screen_accessible_for_kampus_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'kampus',
            'status' => User::STATUS_ACTIVE,
        ]);


        $institusi = InstitusiPendidikan::create([
            'kode' => '001099',
            'nama' => 'Universitas Keperawatan Test',
        ]);


        $user->institusiPendidikan()->attach($institusi->id, ['id' => (string) \Illuminate\Support\Str::uuid()]);



        $response = $this->actingAs($user)->get('/kampus/gateway?nik=3578000000000001');

        $response->assertStatus(200)
            ->assertSee('Gateway Awal Data Perawat')
            ->assertSee('3578000000000001');
    }
}
