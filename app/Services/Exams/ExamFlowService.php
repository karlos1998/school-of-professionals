<?php

namespace App\Services\Exams;

use App\Domain\Exams\Exceptions\ExamFlowException;
use App\Enums\ExamMode;
use App\Http\Resources\ExamFlow\ExamSessionResource;
use App\Models\Exam;
use App\Repositories\Contracts\ExamRepositoryInterface;
use Illuminate\Support\Collection;

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
        $authority = $this->examRepository->getExamAuthorities()->firstWhere('slug', $authoritySlug);

        if (!$authority) {
            throw new ExamFlowException('Authority not found.');
        }

        $tests = $this->examRepository->getExamsForAuthority($authoritySlug)
            ->groupBy(fn (Exam $exam): string => $exam->category->slug)
            ->map(function (Collection $items, string $testSlug) use ($authoritySlug): array {
                $first = $items->first();
                $classes = $items
                    ->filter(fn (Exam $exam): bool => $exam->examClass !== null)
                    ->map(fn (Exam $exam): array => [
                        'name' => $exam->examClass->name,
                        'slug' => $exam->examClass->slug,
                        'url' => route('exam-flow.mode-selection.with-class', [
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
                    'url' => route('exam-flow.mode-selection', [
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

    public function getModeSelectionPayload(string $authoritySlug, string $testSlug, ?string $classSlug = null): array
    {
        $variant = $this->resolveExamVariant($authoritySlug, $testSlug, $classSlug);

        $modeRoutes = collect(ExamMode::cases())
            ->map(function (ExamMode $mode) use ($authoritySlug, $testSlug, $classSlug): array {
                $params = [
                    'authority' => $authoritySlug,
                    'test' => $testSlug,
                    'mode' => $mode->value,
                ];

                if ($classSlug !== null) {
                    $params['class'] = $classSlug;
                }

                return [
                    'value' => $mode->value,
                    'label' => $mode->label(),
                    'url' => route(
                        $classSlug !== null ? 'exam-flow.session.mode.with-class' : 'exam-flow.session.mode',
                        $params,
                    ),
                ];
            })
            ->values()
            ->all();

        return [
            'authority' => $variant['authority'],
            'test' => $variant['test'],
            'selectedClass' => $variant['selectedClass'],
            'backUrl' => route('exam-flow.authority-tests', ['authority' => $authoritySlug]),
            'modeRoutes' => $modeRoutes,
        ];
    }

    public function resolveExamSession(
        string $authoritySlug,
        string $testSlug,
        ?string $classSlug = null,
        ?string $modeSlug = null,
    ): array {
        $variant = $this->resolveExamVariant($authoritySlug, $testSlug, $classSlug);

        $selectedMode = ExamMode::tryFrom($modeSlug ?? '') ?? ExamMode::Sequential;
        $fullExam = $this->examRepository->getExamWithQuestionsById($variant['exam']->id);

        if (!$fullExam) {
            throw new ExamFlowException('Exam session data not found.');
        }

        $modeSelectionUrl = route(
            $classSlug !== null ? 'exam-flow.mode-selection.with-class' : 'exam-flow.mode-selection',
            array_filter([
                'authority' => $authoritySlug,
                'test' => $testSlug,
                'class' => $classSlug,
            ]),
        );

        return [
            'authority' => $variant['authority'],
            'test' => $variant['test'],
            'selectedClass' => $variant['selectedClass'],
            'selectedMode' => $selectedMode->value,
            'selectedModeLabel' => $selectedMode->label(),
            'modeSelectionUrl' => $modeSelectionUrl,
            'backUrl' => route('exam-flow.authority-tests', ['authority' => $authoritySlug]),
            'exam' => ExamSessionResource::make($fullExam)->resolve(),
        ];
    }

    private function resolveExamVariant(string $authoritySlug, string $testSlug, ?string $classSlug = null): array
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

        return [
            'exam' => $exam,
            'authority' => $testsPayload['authority'],
            'test' => [
                'name' => $exam->category->name,
                'slug' => $exam->category->slug,
            ],
            'selectedClass' => $exam->examClass ? [
                'name' => $exam->examClass->name,
                'slug' => $exam->examClass->slug,
            ] : null,
        ];
    }
}
