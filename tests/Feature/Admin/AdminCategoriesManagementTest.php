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
        ])
        ->assertRedirect();

    $category = ExamCategory::query()->where('slug', 'maszyny-drogowe')->firstOrFail();
    expect($category)->not->toBeNull();

    actingAs($admin)
        ->put("/admin-panel/categories/{$category->id}", [
            'name' => 'Urządzenia drogowe',
        ])
        ->assertRedirect();

    expect(ExamCategory::query()->find($category->id)?->slug)->toBe('urzadzenia-drogowe');

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
        ])
        ->assertRedirect();

    expect(ExamCategory::query()->where('slug', 'operator-1')->exists())->toBeTrue();
});
