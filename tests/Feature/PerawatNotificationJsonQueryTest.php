<?php

namespace Tests\Feature;

use App\Models\Perawat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerawatNotificationJsonQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_perawat_dashboard_and_notification_filters_work_with_notification_data_json_query(): void
    {
        $perawat = Perawat::create([
            'nik' => '3271010101010001',
            'nama' => 'Perawat Uji',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_PERAWAT,
            'perawat_nik' => $perawat->nik,
        ]);

        DB::table('notifications')->insert([
            [
                'id' => (string) Str::uuid(),
                'type' => 'test.notification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'tipe' => 'legalitas_data_status',
                    'status' => 'verified',
                    'message' => 'Legalitas data diverifikasi',
                ], JSON_THROW_ON_ERROR),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'test.notification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'tipe' => 'other_status',
                    'status' => 'info',
                    'message' => 'Notifikasi lain',
                ], JSON_THROW_ON_ERROR),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($user)
            ->get(route('perawat.dashboard'))
            ->assertOk()
            ->assertViewHas('legalitasDataUnread', 1);

        $this->actingAs($user)
            ->get(route('perawat.notifikasi', [
                'tipe' => 'legalitas',
                'legalitas_status' => 'verified',
            ]))
            ->assertOk()
            ->assertViewHas('notifications', function ($notifications) {
                return $notifications->total() === 1;
            });
    }
}
