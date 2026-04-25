<?php

namespace App\DTOs\ExamFlow;

readonly class ExamSessionDto
{
    /**
     * @param  array{questionLimit: int, passingThreshold: int}  $examConfig
     * @param array{
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
     * } $exam
     */
    public function __construct(
        public AuthorityMetaDto $authority,
        public AuthorityMetaDto $test,
        public ?AuthorityMetaDto $selectedClass,
        public string $selectedMode,
        public string $selectedModeLabel,
        public string $modeSelectionUrl,
        public string $backUrl,
        public array $examConfig,
        public array $exam,
    ) {}
}
