<?php

namespace Tests\Feature;

use App\Models\DataLegalitas;
use App\Models\CareerEvent;
use App\Models\InstitusiPendidikan;
use App\Models\Lamaran;
use App\Models\LembagaPelatihan;
use App\Models\Lowongan;
use App\Models\MitraPengguna;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use App\Models\SertifikasiKompetensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class EndToEndFlowTest extends TestCase
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

    public function test_end_to_end_flow_from_lulusan_to_kontrak_selesai(): void
    {
        Notification::fake();
        Queue::fake();

        $institusi = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $lembaga = LembagaPelatihan::create(['nama' => 'Lembaga A']);
        $mitra = MitraPengguna::create(['nama' => 'RS A']);

        $kampusUser = User::factory()->create(['role' => User::ROLE_KAMPUS]);
        $this->attachUserToOrganisasi($kampusUser, $institusi->id, InstitusiPendidikan::class);

        $lembagaUser = User::factory()->create(['role' => User::ROLE_LEMBAGA]);
        $this->attachUserToOrganisasi($lembagaUser, $lembaga->id, LembagaPelatihan::class);

        $mitraUser = User::factory()->create(['role' => User::ROLE_MITRA]);
        $this->attachUserToOrganisasi($mitraUser, $mitra->id, MitraPengguna::class);

        $regulatorUser = User::factory()->create(['role' => User::ROLE_REGULATOR]);

        $nik = '1234567890123456';

        $this->actingAs($kampusUser)
            ->post(route('kampus.lulusan.store'), [
                'nik' => $nik,
                'nama' => 'Siti Aminah',
                'institusi_pendidikan_id' => $institusi->id,
            ])
            ->assertStatus(302);

        $perawat = Perawat::where('nik', $nik)->firstOrFail();

        $this->actingAs($lembagaUser)
            ->post(route('lembaga.sertifikasi.store'), [
                'perawat_nik' => $perawat->nik,
                'lembaga_pelatihan_id' => $lembaga->id,
                'nama_sertifikat' => 'Pelatihan ICU',
            ])
            ->assertStatus(302);

        $sertifikasi = SertifikasiKompetensi::where('perawat_nik', $perawat->nik)
            ->where('lembaga_pelatihan_id', $lembaga->id)
            ->firstOrFail();

        $this->actingAs($lembagaUser)
            ->post(route('lembaga.sertifikasi.verify', $sertifikasi))
            ->assertStatus(302);

        $this->actingAs($regulatorUser)
            ->post(route('regulator.legalitas.upsert'), [
                'perawat_nik' => $perawat->nik,
                'nomor_str' => 'STR-001',
                'status_str' => DataLegalitas::STATUS_STR_AKTIF,
            ])
            ->assertStatus(302);

        $perawat->refresh();
        $this->assertSame(Perawat::KESIAPAN_READY, $perawat->status_kesiapan);

        $this->actingAs($mitraUser)
            ->post(route('mitra.lowongan.store'), [
                'mitra_pengguna_id' => $mitra->id,
                'judul' => 'Perawat ICU',
                'deskripsi' => 'Shift malam',
                'tipe_penempatan' => \App\Models\Lowongan::PENEMPATAN_DOMESTIK,
            ])
            ->assertStatus(302);

        $lowongan = Lowongan::where('mitra_pengguna_id', $mitra->id)->firstOrFail();

        $perawatUser = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        $this->actingAs($perawatUser)
            ->post(route('perawat.lowongan.apply'), [
                'lowongan_id' => $lowongan->id,
            ])
            ->assertStatus(302);

        $lamaran = Lamaran::where('perawat_nik', $perawat->nik)
            ->where('lowongan_id', $lowongan->id)
            ->firstOrFail();

        $this->actingAs($mitraUser)
            ->post(route('mitra.lamaran.hire'), [
                'lamaran_id' => $lamaran->id,
                'mitra_pengguna_id' => $mitra->id,
            ])
            ->assertStatus(302);

        $perawat->refresh();
        $this->assertSame(Perawat::KETENAGAKERJAAN_BEKERJA, $perawat->status_ketenagakerjaan);

        $riwayat = RiwayatPekerjaan::where('perawat_nik', $perawat->nik)
            ->where('mitra_pengguna_id', $mitra->id)
            ->where('status_pekerjaan', RiwayatPekerjaan::STATUS_AKTIF)
            ->firstOrFail();

        $this->actingAs($mitraUser)
            ->post(route('mitra.riwayat_pekerjaan.complete', $riwayat), [
                'status_pekerjaan' => RiwayatPekerjaan::STATUS_SELESAI,
                'tanggal_selesai' => now()->toDateString(),
                'penilaian_kinerja' => 'Baik',
            ])
            ->assertStatus(302);

        $perawat->refresh();
        $this->assertSame(Perawat::KETENAGAKERJAAN_BERPENGALAMAN, $perawat->status_ketenagakerjaan);

        $this->assertDatabaseHas('riwayat_pekerjaan', [
            'id' => $riwayat->id,
            'status_pekerjaan' => RiwayatPekerjaan::STATUS_SELESAI,
        ]);

        $this->assertDatabaseHas('rekrutmen', [
            'perawat_nik' => $perawat->nik,
            'mitra_pengguna_id' => $mitra->id,
            'status' => Rekrutmen::STATUS_RELEASED,
        ]);

        $eventKeys = CareerEvent::query()
            ->where('perawat_nik', $perawat->nik)
            ->pluck('event_key')
            ->all();

        $this->assertContains('lulusan.recorded', $eventKeys);
        $this->assertContains('sertifikasi.verified', $eventKeys);
        $this->assertContains('legalitas.verified', $eventKeys);
        $this->assertContains('pengalaman.completed', $eventKeys);

        $this->assertDatabaseHas('career_events', [
            'perawat_nik' => $perawat->nik,
            'event_key' => 'legalitas.verified',
            'actor_role' => User::ROLE_REGULATOR,
            'source_actor' => 'regulator',
        ]);
    }
}
