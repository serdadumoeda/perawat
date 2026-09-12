<?php

namespace Tests\Feature;

use App\Models\InstitusiPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class KampusAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function attachUserToInstitusi(User $user, InstitusiPendidikan $institusi): void
    {
        DB::table('organisasi_user')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'organisasiable_id' => $institusi->id,
            'organisasiable_type' => InstitusiPendidikan::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_kampus_cannot_store_lulusan_for_other_institusi(): void
    {
        $institusiA = InstitusiPendidikan::create(['nama' => 'Kampus A']);
        $institusiB = InstitusiPendidikan::create(['nama' => 'Kampus B']);

        $user = User::factory()->create(['role' => User::ROLE_KAMPUS]);
        $this->attachUserToInstitusi($user, $institusiA);

        $payload = [
            'nik' => '1234567890123456',
            'nama' => 'Siti Aminah',
            'institusi_pendidikan_id' => $institusiB->id,
        ];

        $this->actingAs($user)
            ->post(route('kampus.lulusan.store'), $payload)
            ->assertStatus(403);
    }
}
