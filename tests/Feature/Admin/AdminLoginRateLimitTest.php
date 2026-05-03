<?php

use App\Models\User;
use function Pest\Laravel\post;

it('returns rate limit message after too many failed admin login attempts', function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.admin_password', 'asdasdasd');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
    config()->set('app.super_admin_password', 'tgbujmghj');

    User::factory()->create(['email' => 'admin@example.com']);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        post('/admin-panel/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email']);
    }

    $response = post('/admin-panel/login', [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email']);
    expect(session('errors')->first('email'))->toContain('Zbyt dużo prób logowania');
});
