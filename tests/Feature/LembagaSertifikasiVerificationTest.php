<?php

namespace Tests\Feature;

use App\Models\LembagaPelatihan;
use App\Models\Perawat;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LembagaSertifikasiVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function attachUserToLembaga(User $user, LembagaPelatihan $lembaga): void
    {
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $lembaga->id,
            'organisasiable_type' => LembagaPelatihan::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_lembaga_can_verify_own_sertifikasi(): void
    {
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $user = User::factory()->create(['role' => User::ROLE_LEMBAGA]);
        $this->attachUserToLembaga($user, $lembaga);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $sertifikasi = SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan ICU',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_PENDING,
        ]);

        $this->actingAs($user)
            ->post(route('lembaga.sertifikasi.verify', $sertifikasi))
            ->assertStatus(302);

        $sertifikasi->refresh();
        $this->assertSame(SertifikasiKompetensi::VERIF_VERIFIED, $sertifikasi->status_verifikasi);
        $this->assertSame($user->id, $sertifikasi->verified_by);
        $this->assertNotNull($sertifikasi->verified_at);
        $this->assertNull($sertifikasi->rejected_reason);
    }

    public function test_lembaga_cannot_verify_other_sertifikasi(): void
    {
        $lembagaA = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $lembagaB = LembagaPelatihan::create(['nama' => 'Lembaga B']);

        $user = User::factory()->create(['role' => User::ROLE_LEMBAGA]);
        $this->attachUserToLembaga($user, $lembagaA);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $sertifikasi = SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembagaB->id,
            'nama_sertifikat' => 'Pelatihan ICU',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_PENDING,
        ]);

        $this->actingAs($user)
            ->post(route('lembaga.sertifikasi.verify', $sertifikasi))
            ->assertStatus(403);
    }

    public function test_lembaga_can_reject_own_sertifikasi_and_store_reason(): void
    {
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $user = User::factory()->create(['role' => User::ROLE_LEMBAGA]);
        $this->attachUserToLembaga($user, $lembaga);

        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $sertifikasi = SertifikasiKompetensi::create([
            'perawat_nik' => $perawat->nik,
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'Pelatihan ICU',
            'status_verifikasi' => SertifikasiKompetensi::VERIF_PENDING,
        ]);

        $this->actingAs($user)
            ->post(route('lembaga.sertifikasi.reject', $sertifikasi), [
                'catatan' => 'Nomor sertifikat tidak valid.',
            ])
            ->assertStatus(302);

        $sertifikasi->refresh();
        $this->assertSame(SertifikasiKompetensi::VERIF_REJECTED, $sertifikasi->status_verifikasi);
        $this->assertSame($user->id, $sertifikasi->verified_by);
        $this->assertNotNull($sertifikasi->verified_at);
        $this->assertSame('Nomor sertifikat tidak valid.', $sertifikasi->rejected_reason);
    }
}
