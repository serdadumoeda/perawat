<?php

namespace Tests\Feature;

use App\Models\ApiRequestLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationPlaygroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_integration_playground_and_see_logs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        ApiRequestLog::create([
            'api_key_id' => null,
            'method' => 'POST',
            'endpoint' => '/api/v1/lulusan',
            'payload_hash' => hash('sha256', '{"sample":1}'),
            'status_code' => 401,
            'response_time_ms' => 45,
            'error_message' => 'Unauthorized',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.integration_playground.index'))
            ->assertOk()
            ->assertSee('Playground Integrasi')
            ->assertSee('/api/v1/lulusan')
            ->assertSee('Unauthorized');
    }
}
