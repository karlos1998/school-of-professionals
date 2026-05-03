<?php

use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\ExamClass;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to create update and delete exam', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $authority = ExamAuthority::query()->create(['name' => 'UDT', 'slug' => 'udt']);
    $category = ExamCategory::query()->create(['name' => 'Operator', 'slug' => 'operator']);
    $class = ExamClass::query()->create(['name' => 'I', 'slug' => 'i']);

    actingAs($admin)
        ->post('/admin-panel/tests', [
            'exam_authority_id' => $authority->id,
            'exam_category_id' => $category->id,
            'exam_class_id' => $class->id,
            'name' => 'Nowy test UDT',
            'description' => 'Opis testu',
        ])
        ->assertRedirect();

    $exam = Exam::query()->where('name', 'Nowy test UDT')->firstOrFail();
    expect($exam->slug)->toStartWith('nowy-test-udt-')
        ->and($exam->exam_class_id)->toBe($class->id);

    $newCategory = ExamCategory::query()->create(['name' => 'Hakowy', 'slug' => 'hakowy']);

    actingAs($admin)
        ->put("/admin-panel/tests/{$exam->id}", [
            'exam_authority_id' => $authority->id,
            'exam_category_id' => $newCategory->id,
            'exam_class_id' => null,
            'name' => 'Nowy test UDT po zmianie',
            'description' => 'Opis po zmianie',
        ])
        ->assertRedirect();

    $exam->refresh();

    expect($exam->name)->toBe('Nowy test UDT po zmianie')
        ->and($exam->exam_category_id)->toBe($newCategory->id)
        ->and($exam->exam_class_id)->toBeNull();

    actingAs($admin)
        ->delete("/admin-panel/tests/{$exam->id}")
        ->assertRedirect();

    expect(Exam::query()->find($exam->id))->toBeNull();
});

it('validates required fields when creating exam', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($admin)
        ->post('/admin-panel/tests', [])
        ->assertSessionHasErrors([
            'exam_authority_id',
            'exam_category_id',
            'name',
        ]);
});
