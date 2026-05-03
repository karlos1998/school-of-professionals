<?php

use App\Models\User;
use App\Repositories\Contracts\ExamSettingsRepositoryInterface;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to update exam settings from panel', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    $this->actingAs($admin)
        ->put('/admin-panel/exam-settings', [
            'question_limit' => 30,
            'passing_threshold' => 20,
        ])
        ->assertRedirect();

    $settingsRepository = app(ExamSettingsRepositoryInterface::class);

    expect($settingsRepository->getInt('exam.session.question_limit', 0))->toBe(30)
        ->and($settingsRepository->getInt('exam.session.passing_threshold', 0))->toBe(20);
});
