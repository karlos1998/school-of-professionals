<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guest from dashboard to login', function (): void {
    get('/admin-panel')->assertRedirect('/login');
});

it('shows dashboard modules for authenticated admin', function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');

    $admin = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($admin)
        ->get('/admin-panel')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/DashboardPage')
            ->where('modules.0.title', 'Testy')
            ->where('modules.1.title', 'Klasy')
        );
});
