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

class LegalitasDocumentReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_regulator_can_approve_str_document(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'str_document_path' => 'legalitas/str.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_PENDING,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.documents.review', $legalitas), [
                'document_type' => 'str',
                'action' => 'approve',
            ])
            ->assertStatus(302);

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DOC_STATUS_APPROVED, $legalitas->str_document_status);
        $this->assertSame($regulator->id, $legalitas->str_document_reviewed_by);
        $this->assertNotNull($legalitas->str_document_reviewed_at);

        $this->assertTrue(AuditLog::where('action', 'legalitas.document.review')
            ->where('subject_id', $legalitas->id)
            ->exists());
    }

    public function test_regulator_can_reject_izin_luar_negeri_document(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'izin_luar_negeri_document_path' => 'legalitas/izin.pdf',
            'izin_luar_negeri_document_status' => DataLegalitas::DOC_STATUS_PENDING,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.documents.review', $legalitas), [
                'document_type' => 'izin_luar_negeri',
                'action' => 'reject',
                'note' => 'Dokumen tidak lengkap.',
            ])
            ->assertStatus(302);

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DOC_STATUS_REJECTED, $legalitas->izin_luar_negeri_document_status);
        $this->assertSame('Dokumen tidak lengkap.', $legalitas->izin_luar_negeri_document_note);
        $this->assertSame($regulator->id, $legalitas->izin_luar_negeri_document_reviewed_by);
        $this->assertNotNull($legalitas->izin_luar_negeri_document_reviewed_at);
    }

    public function test_non_regulator_cannot_review_documents(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'str_document_path' => 'legalitas/str.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_PENDING,
        ]);

        $perawatUser = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $this->actingAs($perawatUser)
            ->patch(route('regulator.legalitas.documents.review', $legalitas), [
                'document_type' => 'str',
                'action' => 'approve',
            ])
            ->assertStatus(403);
    }

    public function test_regulator_cannot_re_review_final_document_without_reopen(): void
    {
        $perawat = Perawat::create([
            'nik' => '2234567890123456',
            'nama' => 'Final Dokumen',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'str_document_path' => 'legalitas/str.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_APPROVED,
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->from(route('regulator.legalitas.documents'))
            ->patch(route('regulator.legalitas.documents.review', $legalitas), [
                'document_type' => 'str',
                'action' => 'reject',
                'note' => 'Tidak boleh langsung re-review.',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors('status');

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DOC_STATUS_APPROVED, $legalitas->str_document_status);
    }

    public function test_regulator_can_reopen_final_document_to_pending(): void
    {
        $perawat = Perawat::create([
            'nik' => '3234567890123456',
            'nama' => 'Reopen Dokumen',
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'str_document_path' => 'legalitas/str.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_REJECTED,
            'str_document_note' => 'Dokumen buram.',
            'str_document_reviewed_by' => User::factory()->create(['role' => User::ROLE_REGULATOR])->id,
            'str_document_reviewed_at' => now(),
        ]);

        $regulator = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $this->actingAs($regulator)
            ->patch(route('regulator.legalitas.documents.reopen', $legalitas), [
                'document_type' => 'str',
                'note' => 'Upload ulang dokumen terbaru.',
            ])
            ->assertStatus(302)
            ->assertSessionHas('status');

        $legalitas->refresh();
        $this->assertSame(DataLegalitas::DOC_STATUS_PENDING, $legalitas->str_document_status);
        $this->assertNull($legalitas->str_document_note);
        $this->assertNull($legalitas->str_document_reviewed_by);
        $this->assertNull($legalitas->str_document_reviewed_at);

        $this->assertTrue(AuditLog::where('action', 'legalitas.document.reopen')
            ->where('subject_id', $legalitas->id)
            ->exists());
    }

    public function test_regulator_cannot_review_documents_from_other_regulator_organisasi(): void
    {
        $perawat = Perawat::create([
            'nik' => '4234567890123456',
            'nama' => 'Scoped Dokumen',
        ]);

        $orgA = RegulatorOrganisasi::create([
            'kode' => 'ORG-A',
            'nama' => 'Regulator A',
            'tingkat' => 'provinsi',
            'wilayah' => 'A',
            'is_active' => true,
        ]);
        $orgB = RegulatorOrganisasi::create([
            'kode' => 'ORG-B',
            'nama' => 'Regulator B',
            'tingkat' => 'provinsi',
            'wilayah' => 'B',
            'is_active' => true,
        ]);

        $legalitas = DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'str_document_path' => 'legalitas/str.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_PENDING,
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
            ->patch(route('regulator.legalitas.documents.review', $legalitas), [
                'document_type' => 'str',
                'action' => 'approve',
            ])
            ->assertStatus(403);
    }

    public function test_regulator_documents_index_respects_organisasi_scope(): void
    {
        $orgA = RegulatorOrganisasi::create([
            'kode' => 'DOC-A',
            'nama' => 'Regulator Doc A',
            'tingkat' => 'provinsi',
            'wilayah' => 'A',
            'is_active' => true,
        ]);
        $orgB = RegulatorOrganisasi::create([
            'kode' => 'DOC-B',
            'nama' => 'Regulator Doc B',
            'tingkat' => 'provinsi',
            'wilayah' => 'B',
            'is_active' => true,
        ]);

        $perawatA = Perawat::create(['nik' => '5234567890123456', 'nama' => 'Doc Scope A']);
        $perawatB = Perawat::create(['nik' => '6234567890123456', 'nama' => 'Doc Scope B']);
        $perawatNull = Perawat::create(['nik' => '7234567890123456', 'nama' => 'Doc Scope Null']);

        DataLegalitas::create([
            'perawat_nik' => $perawatA->nik,
            'str_document_path' => 'legalitas/a.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_PENDING,
            'regulator_organisasi_id' => $orgA->id,
        ]);
        DataLegalitas::create([
            'perawat_nik' => $perawatB->nik,
            'str_document_path' => 'legalitas/b.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_PENDING,
            'regulator_organisasi_id' => $orgB->id,
        ]);
        DataLegalitas::create([
            'perawat_nik' => $perawatNull->nik,
            'str_document_path' => 'legalitas/null.pdf',
            'str_document_status' => DataLegalitas::DOC_STATUS_PENDING,
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
            ->get(route('regulator.legalitas.documents', ['status' => 'all']))
            ->assertOk()
            ->assertSee('Doc Scope A')
            ->assertSee('Doc Scope Null')
            ->assertDontSee('Doc Scope B');
    }
}
