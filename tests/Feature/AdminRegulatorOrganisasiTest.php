<?php

namespace Tests\Feature;

use App\Models\RegulatorOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRegulatorOrganisasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_regulator_organisasi(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.regulator_org.store'), [
                'kode' => 'DINKES-DKI',
                'nama' => 'Dinas Kesehatan DKI Jakarta',
                'tingkat' => 'provinsi',
                'wilayah' => 'DKI Jakarta',
                'email' => 'dinkes-dki@example.test',
                'telepon' => '021555000',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.regulator_org.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('regulator_organisasi', [
            'kode' => 'DINKES-DKI',
            'nama' => 'Dinas Kesehatan DKI Jakarta',
            'tingkat' => 'provinsi',
            'wilayah' => 'DKI Jakarta',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_map_regulator_user_to_regulator_organisasi(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);
        $organisasi = RegulatorOrganisasi::create([
            'kode' => 'REG-001',
            'nama' => 'Regulator 1',
            'tingkat' => 'provinsi',
            'wilayah' => 'Jawa Barat',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.mapping.store'), [
                'user_id' => $regulator->id,
                'organisasi_type' => 'regulator',
                'organisasi_id' => $organisasi->id,
            ])
            ->assertRedirect(route('admin.mapping.create'))
            ->assertSessionHas('status');

        $this->assertTrue(
            DB::table('organisasi_user')
                ->where('user_id', $regulator->id)
                ->where('organisasiable_id', $organisasi->id)
                ->where('organisasiable_type', RegulatorOrganisasi::class)
                ->exists()
        );
    }
}
