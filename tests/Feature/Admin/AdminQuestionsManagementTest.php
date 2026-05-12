<?php

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
});

it('allows admin to create update and delete question with answers', function (): void {
    config()->set('exam_sync.image_disk', 's3');
    config()->set('exam_sync.image_directory', 'exam-questions');
    config()->set('uploads.temporary_disk', 'public');
    config()->set('uploads.temporary_directory', 'tmp/uploads');

    Storage::fake('public');
    Storage::fake('s3');

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
        'image_path' => 'tmp/uploads/question.png',
        'answers' => [
            ['content' => 'A', 'is_correct' => true],
            ['content' => 'B', 'is_correct' => false],
        ],
    ];

    Storage::disk('public')->put($createPayload['image_path'], 'image-bytes');

    actingAs($admin)
        ->post("/admin-panel/tests/{$exam->id}/questions", $createPayload)
        ->assertRedirect();

    $question = Question::query()->where('exam_id', $exam->id)->firstOrFail();
    $createdImagePath = 'exam-questions/egzamin-testowy-1/0001.png';

    expect($question->content)->toBe($createPayload['content'])
        ->and($question->image_path)->toBe($createdImagePath)
        ->and(Answer::query()->where('question_id', $question->id)->count())->toBe(2)
        ->and(Answer::query()->where('question_id', $question->id)->where('is_correct', true)->count())->toBe(1);

    Storage::disk('public')->assertMissing($createPayload['image_path']);
    Storage::disk('s3')->assertExists($createdImagePath);

    $updatePayload = [
        'position' => 2,
        'content' => 'Nowa treść pytania',
        'explanation' => null,
        'image_path' => 'tmp/uploads/replacement.webp',
        'answers' => [
            ['content' => 'C', 'is_correct' => false],
            ['content' => 'D', 'is_correct' => true],
            ['content' => 'E', 'is_correct' => false],
        ],
    ];

    Storage::disk('public')->put($updatePayload['image_path'], 'replacement-bytes');

    actingAs($admin)
        ->put("/admin-panel/tests/{$exam->id}/questions/{$question->id}", $updatePayload)
        ->assertRedirect();

    $question->refresh();
    $updatedImagePath = 'exam-questions/egzamin-testowy-1/0002.webp';

    expect($question->position)->toBe(2)
        ->and($question->content)->toBe('Nowa treść pytania')
        ->and($question->image_path)->toBe($updatedImagePath)
        ->and(Answer::query()->where('question_id', $question->id)->count())->toBe(3)
        ->and(Answer::query()->where('question_id', $question->id)->where('is_correct', true)->count())->toBe(1);

    Storage::disk('s3')->assertMissing($createdImagePath);
    Storage::disk('s3')->assertExists($updatedImagePath);

    actingAs($admin)
        ->delete("/admin-panel/tests/{$exam->id}/questions/{$question->id}")
        ->assertRedirect();

    expect(Question::query()->find($question->id))->toBeNull();
    Storage::disk('s3')->assertMissing($updatedImagePath);
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
