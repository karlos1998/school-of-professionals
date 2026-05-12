<?php

use App\Models\Question;
use App\Services\ExamSync\ExamSyncQuestionScraper;
use App\Services\ExamSync\ExamSyncService;
use App\Services\ExamSync\SourceType;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('stores synchronized question images on the configured disk', function (): void {
    Config::set('exam_sync.image_disk', 's3');
    Config::set('exam_sync.image_directory', 'exam-questions');

    Storage::fake('s3');
    Http::fake([
        'https://assets.example.test/sign.png' => Http::response('png-bytes', 200, [
            'Content-Type' => 'image/png',
        ]),
    ]);

    $service = new ExamSyncService(new class extends ExamSyncQuestionScraper
    {
        public function scrapeQuestions(string $basePageUrl): array
        {
            return [
                [
                    'key' => '1',
                    'position' => 1,
                    'content' => 'Co oznacza znak?',
                    'answers' => [
                        ['content' => 'Odpowiedz A', 'is_correct' => true],
                        ['content' => 'Odpowiedz B', 'is_correct' => false],
                    ],
                    'image_url' => 'https://assets.example.test/sign.png',
                ],
            ];
        }
    });

    $service->sync(SourceType::Wit, [
        [
            'title' => 'Podesty ruchome',
            'class' => [
                'label' => 'Klasa I',
                'slug' => 'podesty-klasa-i',
                'url' => 'https://example.test/podesty',
            ],
        ],
    ]);

    $imagePath = 'exam-questions/wit-podesty-klasa-i/0001.png';

    Storage::disk('s3')->assertExists($imagePath);

    expect(Storage::disk('s3')->get($imagePath))->toBe('png-bytes')
        ->and(Question::query()->firstOrFail()->image_path)->toBe($imagePath);
});
