<?php

namespace App\Services\Exams;

use App\Domain\Exams\Exceptions\ExamFlowException;
use App\DTOs\ExamFlow\AuthorityLinkDto;
use App\DTOs\ExamFlow\AuthorityMetaDto;
use App\DTOs\ExamFlow\AuthorityTestsDto;
use App\DTOs\ExamFlow\ClassOptionDto;
use App\DTOs\ExamFlow\ExamSessionDto;
use App\DTOs\ExamFlow\ExamVariantDto;
use App\DTOs\ExamFlow\ModeRouteDto;
use App\DTOs\ExamFlow\ModeSelectionDto;
use App\DTOs\ExamFlow\TestOptionDto;
use App\Enums\ExamMode;
use App\Http\Resources\ExamFlow\ExamSessionResource;
use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamFlowService
{
    public function __construct(
        private readonly ExamRepositoryInterface $examRepository,
    ) {}

    /** @return list<AuthorityLinkDto> */
    public function getAuthoritiesForWelcome(): array
    {
        $payload = [];

        foreach ($this->examRepository->getExamAuthorities() as $authority) {
            $payload[] = new AuthorityLinkDto(
                name: $authority->name,
                slug: $authority->slug,
                url: route('exam-flow.authority-tests', ['authority' => $authority->slug]),
            );
        }

        return $payload;
    }

    public function getAuthorityTests(string $authoritySlug): AuthorityTestsDto
    {
        $authority = $this->resolveAuthority($authoritySlug);

        $testsBySlug = [];

        foreach ($this->examRepository->getExamsForAuthority($authoritySlug) as $exam) {
            $testsBySlug[$exam->category->slug][] = $exam;
        }

        /** @var list<TestOptionDto> $tests */
        $tests = [];

        foreach ($testsBySlug as $testSlug => $items) {
            /** @var Exam $first */
            $first = $items[0];

            $classesBySlug = [];
            foreach ($items as $exam) {
                if ($exam->examClass === null) {
                    continue;
                }

                $classesBySlug[$exam->examClass->slug] = new ClassOptionDto(
                    name: $exam->examClass->name,
                    slug: $exam->examClass->slug,
                    url: route('exam-flow.mode-selection.with-class', [
                        'authority' => $authoritySlug,
                        'test' => $testSlug,
                        'class' => $exam->examClass->slug,
                    ]),
                );
            }

            /** @var list<ClassOptionDto> $classes */
            $classes = array_values($classesBySlug);

            usort(
                $classes,
                static fn (ClassOptionDto $left, ClassOptionDto $right): int => strcmp($left->name, $right->name),
            );

            $defaultExam = array_find($items, static fn (Exam $exam): bool => $exam->examClass === null) ?? $first;

            $tests[] = new TestOptionDto(
                name: $first->category->name,
                slug: $testSlug,
                description: $first->description,
                questionCount: $defaultExam->questions_count,
                hasClassSelection: $classes !== [],
                classes: $classes,
                url: route('exam-flow.mode-selection', [
                    'authority' => $authoritySlug,
                    'test' => $testSlug,
                ]),
            );
        }

        return new AuthorityTestsDto(
            authority: new AuthorityMetaDto(
                name: $authority->name,
                slug: $authority->slug,
            ),
            tests: $tests,
        );
    }

    public function getModeSelectionPayload(string $authoritySlug, string $testSlug, ?string $classSlug = null): ModeSelectionDto
    {
        $variant = $this->resolveExamVariant($authoritySlug, $testSlug, $classSlug);

        /** @var list<ModeRouteDto> $modeRoutes */
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

            $modeRoutes[] = new ModeRouteDto(
                value: $mode->value,
                label: $this->modeLabel($mode),
                url: route(
                    $classSlug !== null ? 'exam-flow.session.mode.with-class' : 'exam-flow.session.mode',
                    $params,
                ),
            );
        }

        return new ModeSelectionDto(
            authority: $variant->authority,
            test: $variant->test,
            selectedClass: $variant->selectedClass,
            backUrl: route('exam-flow.authority-tests', ['authority' => $authoritySlug]),
            modeRoutes: $modeRoutes,
        );
    }

    public function resolveExamSession(
        string $authoritySlug,
        string $testSlug,
        ?string $classSlug = null,
        ?string $modeSlug = null,
    ): ExamSessionDto {
        $variant = $this->resolveExamVariant($authoritySlug, $testSlug, $classSlug);
        $selectedMode = $this->resolveMode($modeSlug);
        $fullExam = $this->examRepository->getExamWithQuestionsById($variant->exam->id);

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

        /**
         * @var array{
         *   id: int,
         *   authoritySlug: string,
         *   testSlug: string,
         *   name: string,
         *   description: string|null,
         *   class: array{name: string, slug: string}|null,
         *   questions: list<array{
         *     id: int,
         *     position: int,
         *     content: string,
         *     explanation: string|null,
         *     answers: list<array{id: int, content: string, isCorrect: bool}>
         *   }>
         * } $examPayload
         */
        $examPayload = ExamSessionResource::make($fullExam)->resolve();

        return new ExamSessionDto(
            authority: $variant->authority,
            test: $variant->test,
            selectedClass: $variant->selectedClass,
            selectedMode: $selectedMode->value,
            selectedModeLabel: $this->modeLabel($selectedMode),
            modeSelectionUrl: $modeSelectionUrl,
            backUrl: route('exam-flow.authority-tests', ['authority' => $authoritySlug]),
            examConfig: [
                'questionLimit' => $this->questionLimit,
                'passingThreshold' => $this->passingThreshold,
            ],
            exam: $examPayload,
        );
    }

    private function resolveExamVariant(string $authoritySlug, string $testSlug, ?string $classSlug = null): ExamVariantDto
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
            $exam = array_find($items->all(), static fn (Exam $item): bool => $item->examClass?->slug === $classSlug);

            if (! $exam instanceof Exam) {
                throw new ExamFlowException('Requested class variant not found.');
            }
        } else {
            $exam = array_find($items->all(), static fn (Exam $item): bool => $item->examClass === null);

            if (! $exam instanceof Exam) {
                $hasClasses = array_any($items->all(), static fn (Exam $item): bool => $item->examClass !== null);

                if ($hasClasses) {
                    throw new ExamFlowException('Class is required for this UDT test.');
                }

                $exam = $items->first();
            }
        }

        return new ExamVariantDto(
            exam: $exam,
            authority: new AuthorityMetaDto(
                name: $authority->name,
                slug: $authority->slug,
            ),
            test: new AuthorityMetaDto(
                name: $exam->category->name,
                slug: $exam->category->slug,
            ),
            selectedClass: $exam->examClass ? new AuthorityMetaDto(
                name: $exam->examClass->name,
                slug: $exam->examClass->slug,
            ) : null,
        );
    }

    private function resolveAuthority(string $authoritySlug): ExamAuthority
    {
        $authorities = $this->examRepository->getExamAuthorities()->all();

        $authority = array_find($authorities, fn ($item) => $item->slug === $authoritySlug);

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
            return sprintf('Egzamin (%d pytań)', $this->questionLimit);
        }

        return $mode->label();
    }

    public int $questionLimit {
        get {
            $configured = (int) config('exam.session.question_limit', 20);

            return $configured > 0 ? $configured : 20;
        }
    }

    public int $passingThreshold {
        get {
            $configured = (int) config('exam.session.passing_threshold', 16);

            return $configured >= 0 ? $configured : 16;
        }
    }
}
