<?php

namespace App\Services\Admin;

use App\DTOs\Admin\AdminLoginResultDto;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthService
{
    /** @param array{email:string,password:string} $credentials */
    public function attempt(array $credentials, string $ipAddress): AdminLoginResultDto
    {
        $throttleKey = $this->throttleKey($credentials['email'], $ipAddress);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return new AdminLoginResultDto(
                success: false,
                errorMessage: "Zbyt dużo prób logowania. Spróbuj ponownie za chwilę ({$seconds}s).",
            );
        }

        $allowedCredentials = [
            config('app.admin_login') => config('app.admin_password'),
            config('app.super_admin_login') => config('app.super_admin_password'),
        ];

        $expectedPassword = $allowedCredentials[$credentials['email']] ?? null;
        if (! $expectedPassword || $expectedPassword !== $credentials['password']) {
            RateLimiter::hit($throttleKey, 60);

            return new AdminLoginResultDto(
                success: false,
                errorMessage: 'Nieprawidłowe dane logowania.',
            );
        }

        $user = User::query()->where('email', $credentials['email'])->first();
        if (! $user) {
            RateLimiter::hit($throttleKey, 60);

            return new AdminLoginResultDto(
                success: false,
                errorMessage: 'Użytkownik nie istnieje. Uruchom komendę admin:sync.',
            );
        }

        RateLimiter::clear($throttleKey);

        return new AdminLoginResultDto(success: true, user: $user);
    }

    private function throttleKey(string $email, string $ipAddress): string
    {
        return Str::lower($email).'|'.$ipAddress;
    }
}
