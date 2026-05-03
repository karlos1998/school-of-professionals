<?php

use App\Models\ExamClass;
use App\Models\User;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to create update and delete exam class', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    $this->actingAs($admin)
        ->post('/admin-panel/classes', [
            'name' => 'Klasa IV',
            'slug' => 'iv',
        ])
        ->assertRedirect();

    $class = ExamClass::query()->where('slug', 'iv')->first();
    expect($class)->not->toBeNull();

    $this->actingAs($admin)
        ->put("/admin-panel/classes/{$class->id}", [
            'name' => 'Klasa IVA',
            'slug' => 'iva',
        ])
        ->assertRedirect();

    expect(ExamClass::query()->find($class->id)?->slug)->toBe('iva');

    $this->actingAs($admin)
        ->delete("/admin-panel/classes/{$class->id}")
        ->assertRedirect();

    expect(ExamClass::query()->find($class->id))->toBeNull();
});
