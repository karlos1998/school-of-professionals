<?php

use App\Models\User;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('redirects guest to admin login', function (): void {
    get('/admin-panel')->assertRedirect('/login');
});

it('allows admin login with env credentials', function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.admin_password', 'asdasdasd');

    $user = User::factory()->create(['email' => 'admin@example.com']);

    post('/admin-panel/login', [
        'email' => 'admin@example.com',
        'password' => 'asdasdasd',
    ])->assertRedirect('/admin-panel');

    expect(auth()->id())->toBe($user->id);
});
