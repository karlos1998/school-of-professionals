<?php

namespace App\DTOs\ExamFlow;

readonly class ModeSelectionDto
{
    /**
     * @param  list<ModeRouteDto>  $modeRoutes
     */
    public function __construct(
        public AuthorityMetaDto $authority,
        public AuthorityMetaDto $test,
        public ?AuthorityMetaDto $selectedClass,
        public string $backUrl,
        public array $modeRoutes,
    ) {}
}
