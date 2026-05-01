<?php

namespace App\Services\ExamSync;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\ExamClass;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExamSyncService
{
    public function __construct(
        private readonly ExamSyncQuestionScraper $questionScraper,
    ) {}

    /**
     * @param list<array{
     *   title: string,
     *   class: array{label: string, slug: string, url: string}
     * }> $selectedItems
     */
    public function sync(SourceType $source, array $selectedItems): void
    {
        $authority = ExamAuthority::query()->updateOrCreate(
            ['slug' => Str::slug($source->authorityName())],
            ['name' => $source->authorityName()],
        );

        foreach ($selectedItems as $selectedItem) {
            $this->syncSingleExam($source, $authority, $selectedItem['title'], $selectedItem['class']);
        }
    }

    /**
     * @param  array{label: string, slug: string, url: string}  $classData
     */
    private function syncSingleExam(
        SourceType $source,
        ExamAuthority $authority,
        string $categoryName,
        array $classData,
    ): void {
        $questions = $this->questionScraper->scrapeQuestions($classData['url']);

        if ($questions === []) {
            return;
        }

        $examClass = $this->resolveExamClass($source, $classData['label']);
        $category = ExamCategory::query()->updateOrCreate(
            ['slug' => Str::slug($categoryName)],
            ['name' => $categoryName],
        );

        $examName = $categoryName;
        if ($examClass instanceof ExamClass) {
            $examName .= ' - '.$examClass->name;
        }

        $exam = Exam::query()->updateOrCreate(
            [
                'source' => $source->value,
                'source_slug' => $classData['slug'],
            ],
            [
                'exam_authority_id' => $authority->id,
                'exam_category_id' => $category->id,
                'exam_class_id' => $examClass?->id,
                'name' => $examName,
                'slug' => Str::slug($source->value.'-'.$classData['slug']),
                'description' => sprintf('Pytania zsynchronizowane z %s.', $classData['url']),
            ],
        );

        DB::transaction(function () use ($exam, $questions): void {
            $keptQuestionIds = [];

            foreach ($questions as $questionData) {
                $imagePath = $this->downloadImage($questionData['image_url'], $exam->slug, $questionData['position']);

                $question = Question::query()->updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'source_question_key' => $questionData['key'],
                    ],
                    [
                        'position' => $questionData['position'],
                        'content' => $questionData['content'],
                        'image_path' => $imagePath,
                    ],
                );

                $keptQuestionIds[] = $question->id;

                Answer::query()->where('question_id', $question->id)->delete();

                foreach ($questionData['answers'] as $answerData) {
                    Answer::query()->create([
                        'question_id' => $question->id,
                        'content' => $answerData['content'],
                        'is_correct' => $answerData['is_correct'],
                    ]);
                }
            }

            $toDelete = Question::query()
                ->where('exam_id', $exam->id)
                ->whereNotIn('id', $keptQuestionIds)
                ->get();

            foreach ($toDelete as $question) {
                if (is_string($question->image_path) && $question->image_path !== '') {
                    Storage::disk(config('exam_sync.image_disk'))->delete($question->image_path);
                }

                $question->delete();
            }
        });
    }

    private function resolveExamClass(SourceType $source, string $classLabel): ?ExamClass
    {
        $trimmed = trim($classLabel);

        if ($source === SourceType::Udt || Str::lower($trimmed) === 'wybierz') {
            return null;
        }

        if (! preg_match('/klasa\s+([ivx]+)/iu', $trimmed, $matches)) {
            return null;
        }

        $value = strtoupper($matches[1]);

        return ExamClass::query()->updateOrCreate(
            ['slug' => Str::slug($value)],
            ['name' => $value],
        );
    }

    private function downloadImage(?string $imageUrl, string $examSlug, int $position): ?string
    {
        if (! is_string($imageUrl) || $imageUrl === '') {
            return null;
        }

        $response = Http::timeout(config('exam_sync.timeout_seconds'))->get($imageUrl);

        if (! $response->successful()) {
            return null;
        }

        $contentType = (string) $response->header('Content-Type');
        $extension = match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif') => 'gif',
            default => 'jpg',
        };

        $relativePath = trim(config('exam_sync.image_directory'), '/')
            .'/'
            .$examSlug
            .'/'
            .sprintf('%04d.%s', $position, $extension);

        Storage::disk(config('exam_sync.image_disk'))->put($relativePath, $response->body());

        return $relativePath;
    }
}
