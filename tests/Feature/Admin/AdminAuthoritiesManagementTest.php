<?php

use App\Models\ExamAuthority;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to create authority', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($admin)
        ->post('/admin-panel/authorities', [
            'name' => 'Nowy organ',
            'slug' => 'nowy-organ',
        ])
        ->assertRedirect();

    $authority = ExamAuthority::query()->where('slug', 'nowy-organ')->firstOrFail();

    expect($authority->name)->toBe('Nowy organ')
        ->and($authority->sort_order)->toBe(1);
});

it('allows admin to update visible authority name without changing slug', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $authority = ExamAuthority::query()->create([
        'name' => 'WIT',
        'slug' => 'wit',
    ]);

    actingAs($admin)
        ->put("/admin-panel/authorities/{$authority->id}", [
            'name' => 'Maszyny budowlane',
        ])
        ->assertRedirect();

    $authority->refresh();

    expect($authority->name)->toBe('Maszyny budowlane')
        ->and($authority->slug)->toBe('wit');
});

it('allows admin to reorder authorities', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $udt = ExamAuthority::query()->create(['name' => 'UDT', 'slug' => 'udt', 'sort_order' => 1]);
    $wit = ExamAuthority::query()->create(['name' => 'WIT', 'slug' => 'wit', 'sort_order' => 2]);

    actingAs($admin)
        ->putJson('/admin-panel/authorities/order', [
            'ordered_ids' => [$wit->id, $udt->id],
        ])
        ->assertNoContent();

    expect($wit->refresh()->sort_order)->toBe(1)
        ->and($udt->refresh()->sort_order)->toBe(2);
});

it('renders editable authorities in admin panel', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    ExamAuthority::query()->create([
        'name' => 'Maszyny budowlane',
        'slug' => 'wit',
    ]);

    actingAs($admin)
        ->get('/admin-panel/authorities')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/AuthoritiesPage')
            ->where('authorities.data.0.name', 'Maszyny budowlane')
            ->where('authorities.data.0.slug', 'wit')
        );
});
