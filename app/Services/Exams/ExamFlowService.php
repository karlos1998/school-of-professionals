<?php

namespace App\Services\Exams;

use App\Domain\Exams\Exceptions\ExamFlowException;
use App\Models\Exam;
use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamFlowService
{
    public function __construct(
        private readonly ExamRepositoryInterface $examRepository,
    ) {}

    public function getAuthoritiesForWelcome(): array
    {
        return $this->examRepository->getExamAuthorities()
            ->map(fn ($authority): array => [
                'name' => $authority->name,
                'slug' => $authority->slug,
                'url' => route('exam-flow.authority-tests', ['authority' => $authority->slug]),
            ])
            ->values()
            ->all();
    }

    public function getAuthorityTests(string $authoritySlug): array
    {
        $authority = $this->examRepository->getExamAuthorities()
            ->firstWhere('slug', $authoritySlug);

        if (!$authority) {
            throw new ExamFlowException('Authority not found.');
        }

        $tests = $this->examRepository->getExamsForAuthority($authoritySlug)
            ->groupBy(fn (Exam $exam): string => $exam->category->slug)
            ->map(function ($items, string $testSlug) use ($authoritySlug): array {
                /** @var \Illuminate\Support\Collection<int, Exam> $items */
                $first = $items->first();
                $classes = $items
                    ->filter(fn (Exam $exam): bool => $exam->examClass !== null)
                    ->map(fn (Exam $exam): array => [
                        'name' => $exam->examClass->name,
                        'slug' => $exam->examClass->slug,
                        'url' => route('exam-flow.session.with-class', [
                            'authority' => $authoritySlug,
                            'test' => $testSlug,
                            'class' => $exam->examClass->slug,
                        ]),
                    ])
                    ->unique('slug')
                    ->sortBy('name')
                    ->values()
                    ->all();

                $defaultExam = $items->first(fn (Exam $exam): bool => $exam->examClass === null) ?? $first;

                return [
                    'name' => $first->category->name,
                    'slug' => $testSlug,
                    'description' => $first->description,
                    'questionCount' => $defaultExam->questions_count,
                    'hasClassSelection' => count($classes) > 0,
                    'classes' => $classes,
                    'url' => route('exam-flow.session', [
                        'authority' => $authoritySlug,
                        'test' => $testSlug,
                    ]),
                ];
            })
            ->values()
            ->all();

        return [
            'authority' => [
                'name' => $authority->name,
                'slug' => $authority->slug,
            ],
            'tests' => $tests,
        ];
    }

    public function resolveExamSession(string $authoritySlug, string $testSlug, ?string $classSlug = null): array
    {
        $testsPayload = $this->getAuthorityTests($authoritySlug);

        $testMeta = collect($testsPayload['tests'])->firstWhere('slug', $testSlug);

        if (!$testMeta) {
            throw new ExamFlowException('Test not found for authority.');
        }

        $items = $this->examRepository->getExamsForAuthority($authoritySlug)
            ->filter(fn (Exam $exam): bool => $exam->category->slug === $testSlug)
            ->values();

        if ($items->isEmpty()) {
            throw new ExamFlowException('Test variants not found.');
        }

        $hasClasses = $items->contains(fn (Exam $exam): bool => $exam->examClass !== null);

        $exam = null;

        if ($classSlug !== null) {
            $exam = $items->first(fn (Exam $item): bool => $item->examClass?->slug === $classSlug);

            if (!$exam) {
                throw new ExamFlowException('Requested class variant not found.');
            }
        } else {
            $exam = $items->first(fn (Exam $item): bool => $item->examClass === null);

            if (!$exam) {
                if ($hasClasses) {
                    throw new ExamFlowException('Class is required for this UDT test.');
                }

                $exam = $items->first();
            }
        }

        $fullExam = $this->examRepository->getExamWithQuestionsById($exam->id);

        if (!$fullExam) {
            throw new ExamFlowException('Exam session data not found.');
        }

        return [
            'authority' => $testsPayload['authority'],
            'test' => [
                'name' => $fullExam->category->name,
                'slug' => $fullExam->category->slug,
            ],
            'selectedClass' => $fullExam->examClass ? [
                'name' => $fullExam->examClass->name,
                'slug' => $fullExam->examClass->slug,
            ] : null,
            'backUrl' => route('exam-flow.authority-tests', ['authority' => $authoritySlug]),
            'exam' => [
                'id' => $fullExam->id,
                'authoritySlug' => $fullExam->authority->slug,
                'testSlug' => $fullExam->category->slug,
                'name' => $fullExam->name,
                'description' => $fullExam->description,
                'class' => $fullExam->examClass ? [
                    'name' => $fullExam->examClass->name,
                    'slug' => $fullExam->examClass->slug,
                ] : null,
                'questions' => $fullExam->questions
                    ->map(fn ($question): array => [
                        'id' => $question->id,
                        'position' => $question->position,
                        'content' => $question->content,
                        'explanation' => $question->explanation,
                        'answers' => $question->answers
                            ->map(fn ($answer): array => [
                                'id' => $answer->id,
                                'content' => $answer->content,
                                'isCorrect' => (bool) $answer->is_correct,
                            ])
                            ->values()
                            ->all(),
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }
}
