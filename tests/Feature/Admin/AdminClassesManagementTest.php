<?php

use App\Models\ExamClass;
use App\Models\User;
use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to create update and delete exam class', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($admin)
        ->post('/admin-panel/classes', [
            'name' => 'Klasa IV',
        ])
        ->assertRedirect();

    $class = ExamClass::query()->where('slug', 'klasa-iv')->firstOrFail();
    expect($class)->not->toBeNull();

    actingAs($admin)
        ->put("/admin-panel/classes/{$class->id}", [
            'name' => 'Klasa IVA',
        ])
        ->assertRedirect();

    expect(ExamClass::query()->find($class->id)?->slug)->toBe('klasa-iva');

    actingAs($admin)
        ->delete("/admin-panel/classes/{$class->id}")
        ->assertRedirect();

    expect(ExamClass::query()->find($class->id))->toBeNull();
});
