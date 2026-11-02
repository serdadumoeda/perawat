<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'kampus',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('perawat with new NIK can self-register', function () {
    $response = $this->post('/register', [
        'name' => 'Perawat Baru',
        'email' => 'perawat.baru@example.com',
        'role' => 'perawat',
        'nik' => '1871130203890006',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $this->assertDatabaseHas('perawat', ['nik' => '1871130203890006', 'nama' => 'Perawat Baru']);
    $this->assertDatabaseHas('users', ['email' => 'perawat.baru@example.com', 'perawat_nik' => '1871130203890006']);
});

test('perawat NIK already linked to another account is rejected', function () {
    $payload = [
        'name' => 'Perawat A',
        'role' => 'perawat',
        'nik' => '1871130203890007',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $this->post('/register', $payload + ['email' => 'a@example.com']);
    auth()->logout();

    $response = $this->post('/register', $payload + ['email' => 'b@example.com']);
    $response->assertSessionHasErrors('nik');
});
