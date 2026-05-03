<?php

use App\Models\User;
use App\Services\Admin\AdminAuthService;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.admin_password', 'sekret123');
    config()->set('app.super_admin_login', 'super-admin@example.com');
    config()->set('app.super_admin_password', 'super-sekret');
});

it('returns success for valid admin credentials and existing user', function (): void {
    $user = User::factory()->create(['email' => 'admin@example.com']);
    $service = app(AdminAuthService::class);

    $result = $service->attempt([
        'email' => 'admin@example.com',
        'password' => 'sekret123',
    ], '127.0.0.1');

    expect($result->success)->toBeTrue()
        ->and($result->user?->id)->toBe($user->id)
        ->and($result->errorMessage)->toBeNull();
});

it('returns error for invalid credentials and increments throttle attempts', function (): void {
    User::factory()->create(['email' => 'admin@example.com']);
    $service = app(AdminAuthService::class);
    $throttleKey = 'admin@example.com|127.0.0.1';

    RateLimiter::clear($throttleKey);

    $result = $service->attempt([
        'email' => 'admin@example.com',
        'password' => 'zle-haslo',
    ], '127.0.0.1');

    expect($result->success)->toBeFalse()
        ->and($result->errorMessage)->toBe('Nieprawidłowe dane logowania.')
        ->and(RateLimiter::attempts($throttleKey))->toBe(1);
});

it('returns lockout message after too many failed attempts', function (): void {
    User::factory()->create(['email' => 'admin@example.com']);
    $service = app(AdminAuthService::class);
    $throttleKey = 'admin@example.com|127.0.0.1';

    RateLimiter::clear($throttleKey);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $service->attempt([
            'email' => 'admin@example.com',
            'password' => 'zle-haslo',
        ], '127.0.0.1');
    }

    $result = $service->attempt([
        'email' => 'admin@example.com',
        'password' => 'zle-haslo',
    ], '127.0.0.1');

    expect($result->success)->toBeFalse()
        ->and($result->errorMessage)->toContain('Zbyt dużo prób logowania.');
});
