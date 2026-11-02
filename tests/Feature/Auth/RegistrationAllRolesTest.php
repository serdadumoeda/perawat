<?php

use App\Models\User;

/**
 * End-to-end registration flow for every self-registrable role:
 * register -> dashboard -> pending gate -> admin approval -> role pages.
 */
$roles = [
    'perawat' => ['perawat.dashboard', 'perawat.profil.edit', 'perawat.lowongan.index', 'perawat.lamaran.index', 'perawat.riwayat_pekerjaan.index', 'perawat.pelatihan.index', 'perawat.notifikasi', 'perawat.str_sync.create'],
    'kampus' => ['kampus.lulusan.index', 'kampus.lulusan.create', 'kampus.lulusan.import', 'kampus.tracing.index', 'kampus.tracing.analytics', 'kampus.forecasting', 'kampus.gateway'],
    'lembaga' => ['lembaga.dashboard', 'lembaga.pelatihan.index', 'lembaga.pelatihan.create', 'lembaga.sertifikasi.create', 'lembaga.sertifikasi.pending', 'lembaga.sertifikasi.import'],
    'mitra' => ['mitra.kandidat.index', 'mitra.lowongan.index', 'mitra.lowongan.create', 'mitra.notifikasi', 'mitra.riwayat_kinerja.index', 'mitra.riwayat_pekerjaan.pending', 'mitra.experience_sync.index'],
    'headhunter' => ['mitra.kandidat.index', 'mitra.lowongan.index', 'mitra.lowongan.create', 'mitra.notifikasi', 'mitra.riwayat_kinerja.index'],
    'regulator' => ['regulator.legalitas.create', 'regulator.legalitas.documents', 'regulator.legalitas.data.index', 'regulator.analytics.index', 'regulator.str_sync.index'],
];

foreach ($roles as $role => $pages) {
    test("role {$role}: register, pending gate, approve, open pages", function () use ($role, $pages) {
        $email = "{$role}.test@example.com";
        $payload = [
            'name' => 'Uji '.ucfirst($role),
            'email' => $email,
            'role' => $role,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        if ($role === 'perawat') {
            $payload['nik'] = '1871130203890099';
        }

        // 1. Register
        $this->post('/register', $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', $email)->firstOrFail();
        expect($user->status)->toBe(User::STATUS_PENDING);

        // 2. Dashboard & pending page render
        $this->get('/dashboard')->assertOk();
        $this->get(route('pending_approval'))->assertOk();

        // 3. Role pages gated while pending
        $this->get(route($pages[0]))->assertRedirect(route('pending_approval'));

        // 4. Admin approves
        $user->update(['status' => User::STATUS_ACTIVE]);
        $this->actingAs($user->fresh());

        // 5. Every main page of the role must load (no 500 / 403)
        $problems = [];
        foreach ($pages as $page) {
            $res = $this->get(route($page));
            if ($res->status() !== 200) {
                $problems[] = "{$page} => {$res->status()} ".($res->headers->get('Location') ?? '').' '.json_encode(session('errors')?->all() ?? session('error') ?? session('status'));
            }
        }
        expect($problems)->toBe([], implode("\n", $problems));
    });
}

test('missing NIK for perawat is rejected with a validation error', function () {
    $this->post('/register', [
        'name' => 'Tanpa NIK', 'email' => 'nonik@example.com', 'role' => 'perawat',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasErrors('nik');
});

test('admin role cannot be self-registered', function () {
    $this->post('/register', [
        'name' => 'Hacker', 'email' => 'admin.x@example.com', 'role' => 'admin',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasErrors('role');
});

test('duplicate email is rejected', function () {
    User::factory()->create(['email' => 'dup@example.com']);
    $this->post('/register', [
        'name' => 'Dup', 'email' => 'dup@example.com', 'role' => 'kampus',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});
