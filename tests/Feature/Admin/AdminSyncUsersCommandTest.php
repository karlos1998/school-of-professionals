<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('synchronizes admin users from env and deletes others', function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.admin_password', 'asdasdasd');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
    config()->set('app.super_admin_password', 'tgbujmghj');

    User::factory()->create(['email' => 'ghost@example.com']);

    $exitCode = Artisan::call('admin:sync');

    expect($exitCode)->toBe(0);

    expect(User::query()->pluck('email')->all())
        ->toEqualCanonicalizing(['admin@example.com', 'kontakt@letscode.it']);
});
