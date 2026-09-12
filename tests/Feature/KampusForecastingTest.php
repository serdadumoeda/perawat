<?php

namespace Tests\Feature;

use App\Models\InstitusiPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KampusForecastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_kampus_forecasting_dashboard_accessible_and_renders_projections(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'kampus',
            'status' => User::STATUS_ACTIVE,
        ]);

        $institusi = InstitusiPendidikan::create([
            'kode' => '001088',
            'nama' => 'Universitas Airlangga - Keperawatan',
        ]);

        $user->institusiPendidikan()->attach($institusi->id, ['id' => (string) \Illuminate\Support\Str::uuid()]);

        $response = $this->actingAs($user)->get('/kampus/forecasting');

        $response->assertStatus(200)
            ->assertSee('Dashboard Forecasting')
            ->assertSee('Total Supply Lulusan')
            ->assertSee('Demand Pasar')
            ->assertSee('Proyeksi 3 Tahun Ke Depan');
    }
}
