<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPendidikan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MitraKandidatVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_hired_candidate_by_other_mitra_is_hidden_from_talent_search(): void
    {
        $viewer = User::factory()->create([
            'role' => User::ROLE_MITRA,
        ]);

        $mitraViewer = MitraPengguna::create(['nama' => 'RS Viewer']);
        $mitraOther = MitraPengguna::create(['nama' => 'RS Other']);

        $this->attachUserToMitra($viewer, $mitraViewer);

        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus Uji']);
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga Uji']);

        $nurseHidden = Perawat::create([
            'nik' => '3501000000000001',
            'nama' => 'Nurse Hidden',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);

        $nurseVisible = Perawat::create([
            'nik' => '3501000000000002',
            'nama' => 'Nurse Visible',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);

        $this->seedReadyRequirements($nurseHidden, $institusi, $lembaga);
        $this->seedReadyRequirements($nurseVisible, $institusi, $lembaga);

        Rekrutmen::create([
            'mitra_pengguna_id' => $mitraOther->id,
            'perawat_id' => $nurseHidden->id,
            'perawat_nik' => $nurseHidden->nik,
            'status' => Rekrutmen::STATUS_HIRED,
            'hired_at' => now(),
        ]);

        Rekrutmen::create([
            'mitra_pengguna_id' => $mitraViewer->id,
            'perawat_id' => $nurseVisible->id,
            'perawat_nik' => $nurseVisible->nik,
            'status' => Rekrutmen::STATUS_HIRED,
            'hired_at' => now(),
        ]);

        $response = $this->actingAs($viewer)->get(route('mitra.kandidat.index'));

        $response->assertOk();
        $response->assertDontSee('Nurse Hidden');
        $response->assertSee('Nurse Visible');
    }

    public function test_hired_candidate_is_hidden_when_viewer_has_no_mitra_mapping(): void
    {
        $viewer = User::factory()->create([
            'role' => User::ROLE_MITRA,
        ]);

        $mitra = MitraPengguna::create(['nama' => 'RS Mitra']);
        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus Uji']);
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga Uji']);

        $nurse = Perawat::create([
            'nik' => '3501000000000003',
            'nama' => 'Nurse No Mapping',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);

        $this->seedReadyRequirements($nurse, $institusi, $lembaga);

        Rekrutmen::create([
            'mitra_pengguna_id' => $mitra->id,
            'perawat_id' => $nurse->id,
            'perawat_nik' => $nurse->nik,
            'status' => Rekrutmen::STATUS_HIRED,
            'hired_at' => now(),
        ]);

        $response = $this->actingAs($viewer)->get(route('mitra.kandidat.index'));

        $response->assertOk();
        $response->assertDontSee('Nurse No Mapping');
    }

    public function test_hired_candidate_by_other_mitra_cannot_open_detail_and_cv_pdf(): void
    {
        $viewer = User::factory()->create([
            'role' => User::ROLE_MITRA,
        ]);

        $mitraViewer = MitraPengguna::create(['nama' => 'RS Viewer']);
        $mitraOther = MitraPengguna::create(['nama' => 'RS Other']);
        $this->attachUserToMitra($viewer, $mitraViewer);

        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus Uji']);
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga Uji']);

        $nurse = Perawat::create([
            'nik' => '3501000000000004',
            'nama' => 'Nurse Protected',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
        ]);
        $this->seedReadyRequirements($nurse, $institusi, $lembaga);

        Rekrutmen::create([
            'mitra_pengguna_id' => $mitraOther->id,
            'perawat_id' => $nurse->id,
            'perawat_nik' => $nurse->nik,
            'status' => Rekrutmen::STATUS_HIRED,
            'hired_at' => now(),
        ]);

        $this->actingAs($viewer)
            ->get(route('mitra.kandidat.show', $nurse))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('mitra.kandidat.cv_pdf', $nurse))
            ->assertForbidden();
    }

    private function seedReadyRequirements(
        Perawat $perawat,
        InstitusiPendidikan $institusi,
        LembagaPelatihan $lembaga
    ): void {
        RiwayatPendidikan::create([
            'perawat_nik' => $perawat->nik,
            'institusi_pendidikan_id' => $institusi->id,
            'jenjang' => 'S1',
            'program_studi' => 'Keperawatan',
            'tahun_lulus' => 2020,
            'ipk' => 3.5,
        ]);

        SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'BTCLS',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);

        DataLegalitas::create([
            'perawat_nik' => $perawat->nik,
            'nomor_str' => 'STR-'.$perawat->nik,
            'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            'str_berlaku_sampai' => now()->addYear()->toDateString(),
            'data_status' => DataLegalitas::DATA_STATUS_VERIFIED,
        ]);
    }

    private function attachUserToMitra(User $user, MitraPengguna $mitra): void
    {
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $mitra->id,
            'organisasiable_type' => MitraPengguna::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
