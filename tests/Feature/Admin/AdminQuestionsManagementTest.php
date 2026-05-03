<?php

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\Question;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to create update and delete question with answers', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $authority = ExamAuthority::query()->create(['name' => 'UDT', 'slug' => 'udt']);
    $category = ExamCategory::query()->create(['name' => 'Operator', 'slug' => 'operator']);

    $exam = Exam::query()->create([
        'exam_authority_id' => $authority->id,
        'exam_category_id' => $category->id,
        'exam_class_id' => null,
        'name' => 'Egzamin testowy',
        'slug' => 'egzamin-testowy-1',
        'description' => null,
    ]);

    $createPayload = [
        'position' => 1,
        'content' => 'Jakie jest poprawne działanie operatora?',
        'explanation' => 'Należy wykonać procedurę.',
        'answers' => [
            ['content' => 'A', 'is_correct' => true],
            ['content' => 'B', 'is_correct' => false],
        ],
    ];

    actingAs($admin)
        ->post("/admin-panel/tests/{$exam->id}/questions", $createPayload)
        ->assertRedirect();

    $question = Question::query()->where('exam_id', $exam->id)->firstOrFail();

    expect($question->content)->toBe($createPayload['content'])
        ->and(Answer::query()->where('question_id', $question->id)->count())->toBe(2)
        ->and(Answer::query()->where('question_id', $question->id)->where('is_correct', true)->count())->toBe(1);

    $updatePayload = [
        'position' => 2,
        'content' => 'Nowa treść pytania',
        'explanation' => null,
        'answers' => [
            ['content' => 'C', 'is_correct' => false],
            ['content' => 'D', 'is_correct' => true],
            ['content' => 'E', 'is_correct' => false],
        ],
    ];

    actingAs($admin)
        ->put("/admin-panel/tests/{$exam->id}/questions/{$question->id}", $updatePayload)
        ->assertRedirect();

    $question->refresh();

    expect($question->position)->toBe(2)
        ->and($question->content)->toBe('Nowa treść pytania')
        ->and(Answer::query()->where('question_id', $question->id)->count())->toBe(3)
        ->and(Answer::query()->where('question_id', $question->id)->where('is_correct', true)->count())->toBe(1);

    actingAs($admin)
        ->delete("/admin-panel/tests/{$exam->id}/questions/{$question->id}")
        ->assertRedirect();

    expect(Question::query()->find($question->id))->toBeNull();
});

it('validates exactly one correct answer when storing question', function (): void {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $authority = ExamAuthority::query()->create(['name' => 'UDT', 'slug' => 'udt']);
    $category = ExamCategory::query()->create(['name' => 'Operator', 'slug' => 'operator']);

    $exam = Exam::query()->create([
        'exam_authority_id' => $authority->id,
        'exam_category_id' => $category->id,
        'exam_class_id' => null,
        'name' => 'Egzamin walidacja',
        'slug' => 'egzamin-walidacja',
        'description' => null,
    ]);

    actingAs($admin)
        ->post("/admin-panel/tests/{$exam->id}/questions", [
            'position' => 1,
            'content' => 'Pytanie testowe',
            'answers' => [
                ['content' => 'A', 'is_correct' => true],
                ['content' => 'B', 'is_correct' => true],
            ],
        ])
        ->assertSessionHasErrors(['answers']);
});
