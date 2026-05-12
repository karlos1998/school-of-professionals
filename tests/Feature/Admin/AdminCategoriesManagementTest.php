<?php

use App\Models\ExamCategory;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to create update and delete exam category', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($admin)
        ->post('/admin-panel/categories', [
            'name' => 'Maszyny drogowe',
            'is_favorite' => true,
        ])
        ->assertRedirect();

    $category = ExamCategory::query()->where('slug', 'maszyny-drogowe')->firstOrFail();
    expect($category)->not->toBeNull()
        ->and($category->is_favorite)->toBeTrue();

    actingAs($admin)
        ->put("/admin-panel/categories/{$category->id}", [
            'name' => 'Urządzenia drogowe',
            'is_favorite' => false,
        ])
        ->assertRedirect();

    $category->refresh();

    expect($category->slug)->toBe('urzadzenia-drogowe')
        ->and($category->is_favorite)->toBeFalse();

    actingAs($admin)
        ->delete("/admin-panel/categories/{$category->id}")
        ->assertRedirect();

    expect(ExamCategory::query()->find($category->id))->toBeNull();
});

it('generates unique slugs for duplicate category names', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    ExamCategory::query()->create([
        'name' => 'Operator',
        'slug' => 'operator',
    ]);

    actingAs($admin)
        ->post('/admin-panel/categories', [
            'name' => 'Operator',
            'is_favorite' => false,
        ])
        ->assertRedirect();

    expect(ExamCategory::query()->where('slug', 'operator-1')->exists())->toBeTrue();
});

it('lists favorite categories first in admin panel', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    ExamCategory::query()->create(['name' => 'Zwykła kategoria', 'slug' => 'zwykla-kategoria']);
    ExamCategory::query()->create(['name' => 'Ulubiona kategoria', 'slug' => 'ulubiona-kategoria', 'is_favorite' => true]);

    actingAs($admin)
        ->get('/admin-panel/categories')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CategoriesPage')
            ->where('categories.data.0.name', 'Ulubiona kategoria')
            ->where('categories.data.0.is_favorite', true)
        );
});
