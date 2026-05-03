<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('admin:sync')]
#[Description('Synchronize admin users from .env')]
class SyncAdminUsers extends Command
{
    public function handle(): int
    {
        $expectedUsers = [
            [
                'name' => 'Admin',
                'email' => config('app.admin_login'),
                'password' => config('app.admin_password'),
            ],
            [
                'name' => 'Super Admin',
                'email' => config('app.super_admin_login'),
                'password' => config('app.super_admin_password'),
            ],
        ];

        $expectedUsers = array_values(array_filter($expectedUsers, fn (array $user): bool => filled($user['email']) && filled($user['password'])));
        $expectedEmails = array_column($expectedUsers, 'email');

        $rows = [];

        foreach ($expectedUsers as $expectedUser) {
            $user = User::query()->where('email', $expectedUser['email'])->first();
            $action = 'unchanged';
            $passwordStatus = 'unchanged';

            if (! $user) {
                User::query()->create([
                    'name' => $expectedUser['name'],
                    'email' => $expectedUser['email'],
                    'password' => Hash::make($expectedUser['password']),
                ]);
                $action = 'created';
                $passwordStatus = 'set';
            } else {
                $updated = false;

                if ($user->name !== $expectedUser['name']) {
                    $user->name = $expectedUser['name'];
                    $updated = true;
                }

                if (! Hash::check($expectedUser['password'], (string) $user->password)) {
                    $user->password = Hash::make($expectedUser['password']);
                    $passwordStatus = 'updated';
                    $updated = true;
                }

                if ($updated) {
                    $user->save();
                    $action = 'updated';
                }
            }

            $rows[] = [$expectedUser['email'], $action, $passwordStatus];
        }

        $deletedCount = User::query()->whereNotIn('email', $expectedEmails)->delete();
        if ($deletedCount > 0) {
            $rows[] = ['other users', 'deleted', (string) $deletedCount];
        }

        $this->table(['Email', 'Action', 'Password'], $rows);

        return self::SUCCESS;
    }
}
