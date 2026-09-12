<?php

namespace Tests\Feature;

use App\Models\InstitusiPendidikan;
use App\Models\LembagaPelatihan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerawatNikForeignKeyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function test_riwayat_pendidikan_requires_existing_perawat_nik(): void
    {
        $perawat = $this->makePerawat();
        $institusi = InstitusiPendidikan::create(['nama' => 'Test Kampus']);

        $this->expectException(QueryException::class);

        DB::table('riwayat_pendidikan')->insert([
            'id' => (string) Str::uuid(),
            'perawat_id' => $perawat->id,
            'perawat_nik' => '0000000000000000',
            'institusi_pendidikan_id' => $institusi->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_sertifikasi_requires_existing_perawat_nik(): void
    {
        $perawat = $this->makePerawat();
        $lembaga = LembagaPelatihan::create(['nama' => 'Test Lembaga']);

        $this->expectException(QueryException::class);

        DB::table('sertifikasi_kompetensi')->insert([
            'id' => (string) Str::uuid(),
            'perawat_id' => $perawat->id,
            'perawat_nik' => '0000000000000000',
            'lembaga_pelatihan_id' => $lembaga->id,
            'nama_sertifikat' => 'BTCLS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_data_legalitas_requires_existing_perawat_nik(): void
    {
        $perawat = $this->makePerawat();

        $this->expectException(QueryException::class);

        DB::table('data_legalitas')->insert([
            'id' => (string) Str::uuid(),
            'perawat_id' => $perawat->id,
            'perawat_nik' => '0000000000000000',
            'nomor_str' => 'STR-TEST',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_riwayat_pekerjaan_requires_existing_perawat_nik(): void
    {
        $perawat = $this->makePerawat();
        $mitra = MitraPengguna::create(['nama' => 'Test Mitra']);

        $this->expectException(QueryException::class);

        DB::table('riwayat_pekerjaan')->insert([
            'id' => (string) Str::uuid(),
            'perawat_id' => $perawat->id,
            'perawat_nik' => '0000000000000000',
            'mitra_pengguna_id' => $mitra->id,
            'status_pekerjaan' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_data_legalitas_rejects_mismatched_perawat_id_and_nik_pair(): void
    {
        $perawatA = $this->makePerawat();
        $perawatB = Perawat::create([
            'nik' => '9999888877776666',
            'nama' => 'Perawat Kedua',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
            'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_FRESH,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kombinasi perawat_id dan perawat_nik tidak konsisten.');

        \App\Models\DataLegalitas::create([
            'perawat_id' => $perawatA->id,
            'perawat_nik' => $perawatB->nik,
            'nomor_str' => 'STR-MISMATCH-001',
        ]);
    }

    public function test_user_rejects_mismatched_perawat_id_and_nik_pair(): void
    {
        $perawatA = $this->makePerawat();
        $perawatB = Perawat::create([
            'nik' => '5555444433332222',
            'nama' => 'Perawat Ketiga',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
            'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_FRESH,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kombinasi perawat_id dan perawat_nik tidak konsisten.');

        User::create([
            'name' => 'Mismatched User',
            'email' => 'mismatch-user@example.test',
            'password' => 'password',
            'role' => User::ROLE_PERAWAT,
            'perawat_id' => $perawatA->id,
            'perawat_nik' => $perawatB->nik,
        ]);
    }

    private function makePerawat(): Perawat
    {
        return Perawat::create([
            'nik' => '1111222233334444',
            'nama' => 'Perawat Test',
            'status_kesiapan' => Perawat::KESIAPAN_READY,
            'status_ketenagakerjaan' => Perawat::KETENAGAKERJAAN_FRESH,
        ]);
    }
}
