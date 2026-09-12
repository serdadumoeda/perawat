<?php

namespace Tests\Feature;

use App\Models\Perawat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerawatProfilUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_perawat_can_update_profile_fields(): void
    {
        $perawat = Perawat::create([
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $this->actingAs($user)
            ->patch(route('perawat.profil.update'), [
                'nama' => 'Siti A.',
                'email' => 'siti@update.test',
                'no_hp' => '081234000111',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2001-06-12',
                'alamat' => 'Jl. Mawar 1',
                'kota' => 'Bandung',
                'provinsi' => 'Jawa Barat',
                'status_ketersediaan' => 'open_to_offer',
                'nik' => '9999999999999999',
            ])
            ->assertStatus(302);

        $perawat->refresh();
        $this->assertSame('Siti A.', $perawat->nama);
        $this->assertSame('siti@update.test', $perawat->email);
        $this->assertSame('081234000111', $perawat->no_hp);
        $this->assertSame('P', $perawat->jenis_kelamin);
        $this->assertSame('Bandung', $perawat->tempat_lahir);
        $this->assertSame('2001-06-12', $perawat->tanggal_lahir->format('Y-m-d'));
        $this->assertSame('Jl. Mawar 1', $perawat->alamat);
        $this->assertSame('Bandung', $perawat->kota);
        $this->assertSame('Jawa Barat', $perawat->provinsi);
        $this->assertSame('open_to_offer', $perawat->status_ketersediaan);
        $this->assertSame('1234567890123456', $perawat->nik);
    }
}
