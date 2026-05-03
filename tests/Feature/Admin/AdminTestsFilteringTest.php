<?php

use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('filters tests by authority slug and search phrase', function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');

    $admin = User::factory()->create(['email' => 'admin@example.com']);

    $udt = ExamAuthority::query()->create(['name' => 'UDT', 'slug' => 'udt']);
    $wit = ExamAuthority::query()->create(['name' => 'WIT', 'slug' => 'wit']);
    $cat = ExamCategory::query()->create(['name' => 'Operator', 'slug' => 'operator']);

    Exam::query()->create([
        'exam_authority_id' => $udt->id,
        'exam_category_id' => $cat->id,
        'exam_class_id' => null,
        'name' => 'Wozki jezdniowe',
        'slug' => 'wozki-jezdniowe-1',
        'description' => null,
    ]);

    Exam::query()->create([
        'exam_authority_id' => $wit->id,
        'exam_category_id' => $cat->id,
        'exam_class_id' => null,
        'name' => 'Maszyny drogowe',
        'slug' => 'maszyny-drogowe-1',
        'description' => null,
    ]);

    actingAs($admin)
        ->get('/admin-panel/tests?authority=udt&search=wozki')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ExamsPage')
            ->where('filters.authority', 'udt')
            ->where('filters.search', 'wozki')
            ->where('exams.pagination.total', 1)
            ->where('exams.data.0.name', 'Wozki jezdniowe')
        );
});
