<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_openapi_json_is_available_for_api_v1(): void
    {
        $response = $this->getJson('/api/v1/openapi.json')
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.3')
            ->assertJsonPath('info.title', 'Platform Keperawatan API v1')
            ->assertJsonStructure([
                'paths' => [
                    '/lulusan',
                    '/sertifikasi',
                    '/legalitas',
                ],
            ]);

        $parameters = collect(data_get($response->json(), 'paths./lulusan.post.parameters', []));
        $this->assertTrue($parameters->contains(fn ($item) => ($item['name'] ?? null) === 'Idempotency-Key'));
    }

    public function test_admin_can_download_postman_collection(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.api_keys.postman_collection'));

        $response->assertOk();
        $this->assertStringContainsString(
            'postman_collection.json',
            (string) $response->headers->get('content-disposition')
        );

        $json = json_decode($response->streamedContent(), true);

        $this->assertIsArray($json);
        $this->assertSame('Platform Keperawatan API v1', data_get($json, 'info.name'));
        $this->assertCount(3, data_get($json, 'item', []));
        $variables = collect(data_get($json, 'variable', []));
        $this->assertTrue($variables->contains(fn ($item) => ($item['key'] ?? null) === 'idempotency_key'));
    }
}
