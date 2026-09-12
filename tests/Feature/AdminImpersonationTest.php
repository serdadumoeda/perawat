<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_start_impersonation_for_other_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $target = User::factory()->create(['role' => User::ROLE_PERAWAT]);

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $target))
            ->assertRedirect(route('perawat.dashboard'))
            ->assertSessionHas('impersonator_id', $admin->id)
            ->assertSessionHas('impersonator_name', $admin->name);

        $this->assertAuthenticatedAs($target);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'user.impersonate.start',
            'subject_type' => User::class,
            'subject_id' => $target->id,
        ]);
    }

    public function test_admin_can_stop_impersonation_and_return_to_admin_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $target = User::factory()->create(['role' => User::ROLE_PERAWAT]);

        $this->actingAs($admin)->post(route('admin.users.impersonate', $target));

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Kembali ke akun admin.');

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'user.impersonate.stop',
            'subject_type' => User::class,
            'subject_id' => $target->id,
        ]);
    }

    public function test_admin_cannot_impersonate_self(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $admin))
            ->assertRedirect()
            ->assertSessionHas('status', 'Tidak bisa impersonate akun sendiri.');

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseMissing('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'user.impersonate.start',
            'subject_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_start_impersonation(): void
    {
        $actor = User::factory()->create(['role' => User::ROLE_PERAWAT]);
        $target = User::factory()->create(['role' => User::ROLE_KAMPUS]);

        $this->actingAs($actor)
            ->post(route('admin.users.impersonate', $target))
            ->assertForbidden();
    }
}
