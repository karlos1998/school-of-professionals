<?php

use App\Models\User;

it('synchronizes admin users from env and deletes others', function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.admin_password', 'asdasdasd');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
    config()->set('app.super_admin_password', 'tgbujmghj');

    User::factory()->create(['email' => 'ghost@example.com']);

    $this->artisan('admin:sync')
        ->expectsTable(['Email', 'Action', 'Password'], [
            ['admin@example.com', 'created', 'set'],
            ['kontakt@letscode.it', 'created', 'set'],
            ['other users', 'deleted', '1'],
        ])
        ->assertExitCode(0);

    expect(User::query()->pluck('email')->all())
        ->toEqualCanonicalizing(['admin@example.com', 'kontakt@letscode.it']);
});
