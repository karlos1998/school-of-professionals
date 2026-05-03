<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('forbids authenticated non admin user from accessing admin panel', function (): void {
    $regularUser = User::factory()->create(['email' => 'student@example.com']);

    actingAs($regularUser)
        ->get('/admin-panel')
        ->assertForbidden();
});

it('shows exam settings module on admin dashboard', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($admin)
        ->get('/admin-panel')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/DashboardPage')
            ->where('modules.2.title', 'Ustawienia egzaminu')
        );
});

it('redirects logged in regular user away from admin login form and keeps admin panel forbidden', function (): void {
    $regularUser = User::factory()->create(['email' => 'student2@example.com']);

    actingAs($regularUser);

    get('/admin-panel/login')->assertRedirect('/');
    get('/admin-panel')->assertForbidden();
});
