<?php

namespace Tests\Feature;

use App\Models\LembagaPelatihan;
use App\Models\Pelatihan;
use App\Models\PelatihanPeserta;
use App\Models\Perawat;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PelatihanFlowTest extends TestCase
{
    use RefreshDatabase;

    private function attachUserToOrganisasi(User $user, string $organisasiId, string $organisasiType): void
    {
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $organisasiId,
            'organisasiable_type' => $organisasiType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_lulus_pelatihan_membuat_sertifikat(): void
    {
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Nursi',
        ]);

        $lembagaUser = User::factory()->create(['role' => User::ROLE_LEMBAGA]);
        $this->attachUserToOrganisasi($lembagaUser, $lembaga->id, LembagaPelatihan::class);

        $pelatihan = Pelatihan::create([
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama' => 'Pelatihan BTCLS',
            'jumlah_jam' => 40,
            'status' => Pelatihan::STATUS_DIBUKA,
        ]);

        $peserta = PelatihanPeserta::create([
            'pelatihan_id' => $pelatihan->id,
            'perawat_nik' => $perawat->nik,
            'status' => PelatihanPeserta::STATUS_TERDAFTAR,
            'tanggal_daftar' => now()->toDateString(),
        ]);

        $this->actingAs($lembagaUser)
            ->post(route('lembaga.pelatihan.peserta.lulus', [$pelatihan, $peserta]), [
                'nilai_kompetensi' => 85,
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('pelatihan_peserta', [
            'id' => $peserta->id,
            'status' => PelatihanPeserta::STATUS_LULUS,
        ]);

        $this->assertDatabaseHas('sertifikasi_kompetensi', [
            'perawat_nik' => $perawat->nik,
            'pelatihan_id' => $pelatihan->id,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan BTCLS',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_VERIFIED,
        ]);
    }
}
