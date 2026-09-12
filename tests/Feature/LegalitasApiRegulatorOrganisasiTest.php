<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Models\RegulatorOrganisasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalitasApiRegulatorOrganisasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_legalitas_api_accepts_regulator_organisasi_api_key_owner(): void
    {
        $perawat = Perawat::create([
            'nik' => '8111111111111111',
            'nama' => 'Perawat API',
        ]);

        $organisasi = RegulatorOrganisasi::create([
            'kode' => 'ORG-API-01',
            'nama' => 'Regulator API',
            'tingkat' => 'provinsi',
            'wilayah' => 'Jawa Barat',
            'is_active' => true,
        ]);

        $plainToken = 'demo-regulator-org-token';
        ApiKey::create([
            'name' => 'Legalitas API Org',
            'token_hash' => hash('sha256', $plainToken),
            'owner_type' => RegulatorOrganisasi::class,
            'owner_id' => $organisasi->id,
            'scopes' => ['legalitas:write'],
            'require_signature' => false,
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => $plainToken,
            'Idempotency-Key' => 'org-legalitas-001',
        ])->postJson('/api/v1/legalitas', [
            'data' => [[
                'nik' => $perawat->nik,
                'nomor_str' => 'STR-API-ORG-001',
                'status_str' => 'aktif',
                'str_berlaku_mulai' => now()->toDateString(),
                'str_berlaku_sampai' => now()->addYears(5)->toDateString(),
                'sumber_validasi' => 'api',
            ]],
        ]);

        $response->assertOk();

        $legalitas = DataLegalitas::where('perawat_nik', $perawat->nik)->first();
        $this->assertNotNull($legalitas);
        $this->assertSame($organisasi->id, $legalitas?->regulator_organisasi_id);
        $this->assertSame(DataLegalitas::DATA_STATUS_VERIFIED, $legalitas?->data_status);
    }
}
