<?php

namespace App\Services\Exams;

use App\Domain\Exams\Exceptions\ExamFlowException;
use App\Enums\ExamMode;
use App\Http\Resources\ExamFlow\ExamSessionResource;
use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Repositories\Contracts\ExamRepositoryInterface;

/**
 * @phpstan-type AuthorityLink array{name: string, slug: string, url: string}
 * @phpstan-type AuthorityMeta array{name: string, slug: string}
 * @phpstan-type ClassOption array{name: string, slug: string, url: string}
 * @phpstan-type TestOption array{
 *   name: string,
 *   slug: string,
 *   description: string|null,
 *   questionCount: int,
 *   hasClassSelection: bool,
 *   classes: list<ClassOption>,
 *   url: string
 * }
 * @phpstan-type AuthorityTestsPayload array{
 *   authority: AuthorityMeta,
 *   tests: list<TestOption>
 * }
 * @phpstan-type ModeRoute array{value: string, label: string, url: string}
 * @phpstan-type ModeSelectionPayload array{
 *   authority: AuthorityMeta,
 *   test: array{name: string, slug: string},
 *   selectedClass: array{name: string, slug: string}|null,
 *   backUrl: string,
 *   modeRoutes: list<ModeRoute>
 * }
 * @phpstan-type ExamVariant array{
 *   exam: Exam,
 *   authority: AuthorityMeta,
 *   test: array{name: string, slug: string},
 *   selectedClass: array{name: string, slug: string}|null
 * }
 * @phpstan-type ExamQuestionPayload array{
 *   id: int,
 *   position: int,
 *   content: string,
 *   explanation: string|null,
 *   answers: list<array{id: int, content: string, isCorrect: bool}>
 * }
 * @phpstan-type ExamSessionPayload array{
 *   id: int,
 *   authoritySlug: string,
 *   testSlug: string,
 *   name: string,
 *   description: string|null,
 *   class: array{name: string, slug: string}|null,
 *   questions: list<ExamQuestionPayload>
 * }
 * @phpstan-type ExamConfigPayload array{questionLimit: int, passingThreshold: int}
 */
class ExamFlowService
{
    public function __construct(
        private readonly ExamRepositoryInterface $examRepository,
    ) {}

    /** @return list<AuthorityLink> */
    public function getAuthoritiesForWelcome(): array
    {
        $payload = [];

        foreach ($this->examRepository->getExamAuthorities() as $authority) {
            $payload[] = [
                'name' => $authority->name,
                'slug' => $authority->slug,
                'url' => route('exam-flow.authority-tests', ['authority' => $authority->slug]),
            ];
        }

        return $payload;
    }

    /** @return AuthorityTestsPayload */
    public function getAuthorityTests(string $authoritySlug): array
    {
        $authority = $this->resolveAuthority($authoritySlug);

        $testsBySlug = [];

        foreach ($this->examRepository->getExamsForAuthority($authoritySlug) as $exam) {
            $testsBySlug[$exam->category->slug][] = $exam;
        }

        /** @var list<TestOption> $tests */
        $tests = [];

        foreach ($testsBySlug as $testSlug => $items) {
            /** @var Exam $first */
            $first = $items[0];

            $classesBySlug = [];
            foreach ($items as $exam) {
                if ($exam->examClass === null) {
                    continue;
                }

                $classesBySlug[$exam->examClass->slug] = [
                    'name' => $exam->examClass->name,
                    'slug' => $exam->examClass->slug,
                    'url' => route('exam-flow.mode-selection.with-class', [
                        'authority' => $authoritySlug,
                        'test' => $testSlug,
                        'class' => $exam->examClass->slug,
                    ]),
                ];
            }

            /** @var list<ClassOption> $classes */
            $classes = array_values($classesBySlug);

            usort(
                $classes,
                static fn (array $left, array $right): int => strcmp($left['name'], $right['name']),
            );

            $defaultExam = $first;
            foreach ($items as $exam) {
                if ($exam->examClass === null) {
                    $defaultExam = $exam;
                    break;
                }
            }

            $tests[] = [
                'name' => $first->category->name,
                'slug' => $testSlug,
                'description' => $first->description,
                'questionCount' => $defaultExam->questions_count,
                'hasClassSelection' => $classes !== [],
                'classes' => $classes,
                'url' => route('exam-flow.mode-selection', [
                    'authority' => $authoritySlug,
                    'test' => $testSlug,
                ]),
            ];
        }

        return [
            'authority' => [
                'name' => $authority->name,
                'slug' => $authority->slug,
            ],
            'tests' => $tests,
        ];
    }

    /** @return ModeSelectionPayload */
    public function getModeSelectionPayload(string $authoritySlug, string $testSlug, ?string $classSlug = null): array
    {
        $variant = $this->resolveExamVariant($authoritySlug, $testSlug, $classSlug);

        /** @var list<ModeRoute> $modeRoutes */
        $modeRoutes = [];

        foreach (ExamMode::cases() as $mode) {
            $params = [
                'authority' => $authoritySlug,
                'test' => $testSlug,
                'mode' => $mode->value,
            ];

            if ($classSlug !== null) {
                $params['class'] = $classSlug;
            }

            $modeRoutes[] = [
                'value' => $mode->value,
                'label' => $this->modeLabel($mode),
                'url' => route(
                    $classSlug !== null ? 'exam-flow.session.mode.with-class' : 'exam-flow.session.mode',
                    $params,
                ),
            ];
        }

        return [
            'authority' => $variant['authority'],
            'test' => $variant['test'],
            'selectedClass' => $variant['selectedClass'],
            'backUrl' => route('exam-flow.authority-tests', ['authority' => $authoritySlug]),
            'modeRoutes' => $modeRoutes,
        ];
    }

    /**
     * @return array{
     *   authority: AuthorityMeta,
     *   test: array{name: string, slug: string},
     *   selectedClass: array{name: string, slug: string}|null,
     *   selectedMode: string,
     *   selectedModeLabel: string,
     *   modeSelectionUrl: string,
     *   backUrl: string,
     *   examConfig: ExamConfigPayload,
     *   exam: ExamSessionPayload
     * }
     */
    public function resolveExamSession(
        string $authoritySlug,
        string $testSlug,
        ?string $classSlug = null,
        ?string $modeSlug = null,
    ): array {
        $variant = $this->resolveExamVariant($authoritySlug, $testSlug, $classSlug);
        $selectedMode = $this->resolveMode($modeSlug);
        $fullExam = $this->examRepository->getExamWithQuestionsById($variant['exam']->id);

        if (! $fullExam instanceof Exam) {
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

        /** @var ExamSessionPayload $examPayload */
        $examPayload = ExamSessionResource::make($fullExam)->resolve();

        return [
            'authority' => $variant['authority'],
            'test' => $variant['test'],
            'selectedClass' => $variant['selectedClass'],
            'selectedMode' => $selectedMode->value,
            'selectedModeLabel' => $this->modeLabel($selectedMode),
            'modeSelectionUrl' => $modeSelectionUrl,
            'backUrl' => route('exam-flow.authority-tests', ['authority' => $authoritySlug]),
            'examConfig' => [
                'questionLimit' => $this->examQuestionLimit(),
                'passingThreshold' => $this->examPassingThreshold(),
            ],
            'exam' => $examPayload,
        ];
    }

    /** @return ExamVariant */
    private function resolveExamVariant(string $authoritySlug, string $testSlug, ?string $classSlug = null): array
    {
        $authority = $this->resolveAuthority($authoritySlug);

        $items = $this->examRepository->getExamsForAuthority($authoritySlug)
            ->filter(static fn (Exam $exam): bool => $exam->category->slug === $testSlug)
            ->values();

        if ($items->isEmpty()) {
            throw new ExamFlowException('Test variants not found.');
        }

        $exam = null;
        if ($classSlug !== null) {
            $exam = $items->first(static fn (Exam $item): bool => $item->examClass?->slug === $classSlug);

            if (! $exam instanceof Exam) {
                throw new ExamFlowException('Requested class variant not found.');
            }
        } else {
            $exam = $items->first(static fn (Exam $item): bool => $item->examClass === null);

            if (! $exam instanceof Exam) {
                $hasClasses = $items->contains(static fn (Exam $item): bool => $item->examClass !== null);

                if ($hasClasses) {
                    throw new ExamFlowException('Class is required for this UDT test.');
                }

                $exam = $items->first();
            }
        }

        return [
            'exam' => $exam,
            'authority' => [
                'name' => $authority->name,
                'slug' => $authority->slug,
            ],
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

    private function resolveAuthority(string $authoritySlug): ExamAuthority
    {
        $authority = $this->examRepository->getExamAuthorities()->firstWhere('slug', $authoritySlug);

        if (! $authority instanceof ExamAuthority) {
            throw new ExamFlowException('Authority not found.');
        }

        return $authority;
    }

    private function resolveMode(?string $modeSlug): ExamMode
    {
        if ($modeSlug === 'exam20') {
            return ExamMode::Exam;
        }

        return ExamMode::tryFrom($modeSlug ?? '') ?? ExamMode::Sequential;
    }

    private function modeLabel(ExamMode $mode): string
    {
        if ($mode === ExamMode::Exam) {
            return sprintf('Egzamin (%d pytań)', $this->examQuestionLimit());
        }

        return $mode->label();
    }

    private function examQuestionLimit(): int
    {
        $configured = (int) config('exam.session.question_limit', 20);

        return $configured > 0 ? $configured : 20;
    }

    private function examPassingThreshold(): int
    {
        $configured = (int) config('exam.session.passing_threshold', 16);

        return $configured >= 0 ? $configured : 16;
    }
}
