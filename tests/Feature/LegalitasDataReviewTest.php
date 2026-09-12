<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DataLegalitas;
use App\Models\Perawat;
use App\Models\RegulatorOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LegalitasDataReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_regulator_can_approve_legalitas_data(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-001',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'approve',
            ])
            ->assertStatus(302);

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DATA_STATUS_VERIFIED, $legalitas->data_status);
        $this->assertSame($regulator->id, $legalitas->data_reviewed_by);
        $this->assertNotNull($legalitas->data_reviewed_at);
        $this->assertNull($legalitas->regulator_organisasi_id);

        $this->assertTrue(AuditLog::where('action', 'legalitas.data.review')
            ->where('subject_id', $legalitas->id)
            ->exists());
    }

    public function test_regulator_review_stores_regulator_organisasi_when_mapped(): void
    {
        $perawat = Perawat::create([
            'nik' => '9234567890123456',
            'nama' => 'Regulator Mapping',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-ORG-001',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);
        $regulatorOrg = RegulatorOrganisasi::create([
            'kode' => 'DINKES-TEST',
            'nama' => 'Dinas Kesehatan Test',
            'tingkat' => 'provinsi',
            'wilayah' => 'Jawa Barat',
            'is_active' => true,
        ]);

        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $regulator->id,
            'organisasiable_id' => $regulatorOrg->id,
            'organisasiable_type' => RegulatorOrganisasi::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'approve',
            ])
            ->assertStatus(302);

        $legalitas->refresh();
        $this->assertSame($regulatorOrg->id, $legalitas->regulator_organisasi_id);

        $audit = AuditLog::query()
            ->where('action', 'legalitas.data.review')
            ->where('subject_id', $legalitas->id)
            ->latest()
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($regulatorOrg->id, data_get($audit?->meta, 'regulator_organisasi_id'));
        $this->assertSame($regulatorOrg->nama, data_get($audit?->meta, 'regulator_organisasi_nama'));
    }

    public function test_regulator_can_reject_legalitas_data_with_note(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-002',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'reject',
                'note' => 'Nomor STR tidak valid.',
            ])
            ->assertStatus(302);

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DATA_STATUS_REJECTED, $legalitas->data_status);
        $this->assertSame('Nomor STR tidak valid.', $legalitas->data_rejection_note);
        $this->assertSame($regulator->id, $legalitas->data_reviewed_by);
        $this->assertNotNull($legalitas->data_reviewed_at);

        $this->assertTrue(AuditLog::where('action', 'legalitas.data.review')
            ->where('subject_id', $legalitas->id)
            ->exists());
    }

    public function test_non_regulator_cannot_review_legalitas_data(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-003',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
        ]);

        $perawatUser = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $this->actingAs($perawatUser)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'approve',
            ])
            ->assertStatus(403);
    }

    public function test_regulator_cannot_review_legalitas_data_that_is_already_final(): void
    {
        $perawat = Perawat::create([
            'nik' => '7234567890123456',
            'nama' => 'Final State',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-FINAL-001',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->from(route('regulator.legalitas.data.index'))
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'reject',
                'note' => 'Should not pass.',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors('status');

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DATA_STATUS_VERIFIED, $legalitas->data_status);
        $this->assertNull($legalitas->data_rejection_note);

        $this->assertFalse(AuditLog::where('action', 'legalitas.data.review')
            ->where('subject_id', $legalitas->id)
            ->exists());
    }

    public function test_regulator_cannot_review_legalitas_of_other_regulator_organisasi(): void
    {
        $perawat = Perawat::create([
            'nik' => '8234567890123456',
            'nama' => 'Scoped Legalitas',
        ]);

        $orgA = RegulatorOrganisasi::create([
            'kode' => 'DINKES-A',
            'nama' => 'Dinas A',
            'tingkat' => 'provinsi',
            'wilayah' => 'A',
            'is_active' => true,
        ]);

        $orgB = RegulatorOrganisasi::create([
            'kode' => 'DINKES-B',
            'nama' => 'Dinas B',
            'tingkat' => 'provinsi',
            'wilayah' => 'B',
            'is_active' => true,
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-SCOPE-001',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'regulator_organisasi_id' => $orgB->id,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $regulator->id,
            'organisasiable_id' => $orgA->id,
            'organisasiable_type' => RegulatorOrganisasi::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.review', $legalitas), [
                'action' => 'approve',
            ])
            ->assertStatus(403);
    }

    public function test_regulator_can_reopen_final_legalitas_status(): void
    {
        $perawat = Perawat::create([
            'nik' => '9234567890123456',
            'nama' => 'Reopen Legalitas',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-REOPEN-001',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
            'data_reviewed_by' => User::factory()->create(['role' => User::ROLE_REGULATOR])->id,
            'data_reviewed_at' => now(),
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.data.reopen', $legalitas), [
                'note' => 'Perlu verifikasi ulang data STR.',
            ])
            ->assertStatus(302)
            ->assertSessionHas('status');

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DATA_STATUS_SUBMITTED, $legalitas->data_status);
        $this->assertNull($legalitas->data_reviewed_by);
        $this->assertNull($legalitas->data_reviewed_at);

        $this->assertTrue(AuditLog::where('action', 'legalitas.data.reopen')
            ->where('subject_id', $legalitas->id)
            ->exists());
    }

    public function test_regulator_index_shows_only_accessible_organisasi_scope(): void
    {
        $orgA = RegulatorOrganisasi::create([
            'kode' => 'IDX-A',
            'nama' => 'Index A',
            'tingkat' => 'provinsi',
            'wilayah' => 'A',
            'is_active' => true,
        ]);
        $orgB = RegulatorOrganisasi::create([
            'kode' => 'IDX-B',
            'nama' => 'Index B',
            'tingkat' => 'provinsi',
            'wilayah' => 'B',
            'is_active' => true,
        ]);

        $perawatA = Perawat::create(['nik' => '1334567890123456', 'nama' => 'Nurse Scope A']);
        $perawatB = Perawat::create(['nik' => '1434567890123456', 'nama' => 'Nurse Scope B']);
        $perawatNull = Perawat::create(['nik' => '1534567890123456', 'nama' => 'Nurse Scope Null']);

        DataLegalitas::create([
            'perawat_nik' => $perawatA->nik,
            'nomor_str' => 'STR-A',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'regulator_organisasi_id' => $orgA->id,
        ]);
        DataLegalitas::create([
            'perawat_nik' => $perawatB->nik,
            'nomor_str' => 'STR-B',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'regulator_organisasi_id' => $orgB->id,
        ]);
        DataLegalitas::create([
            'perawat_nik' => $perawatNull->nik,
            'nomor_str' => 'STR-NULL',
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'regulator_organisasi_id' => null,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $regulator->id,
            'organisasiable_id' => $orgA->id,
            'organisasiable_type' => RegulatorOrganisasi::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($regulator)
            ->get(route('regulator.legalitas.data.index', ['status' => 'all']))
            ->assertOk()
            ->assertSee('Nurse Scope A')
            ->assertSee('Nurse Scope Null')
            ->assertDontSee('Nurse Scope B');
    }
}
