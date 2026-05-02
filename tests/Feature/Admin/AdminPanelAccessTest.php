<?php

use App\Models\User;

it('redirects guest to admin login', function (): void {
    $this->get('/admin-panel')->assertRedirect('/login');
});

it('allows admin login with env credentials', function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.admin_password', 'asdasdasd');

    $user = User::factory()->create(['email' => 'admin@example.com']);

    $this->post('/admin-panel/login', [
        'email' => 'admin@example.com',
        'password' => 'asdasdasd',
    ])->assertRedirect('/admin-panel');

    $this->assertAuthenticatedAs($user);
});
