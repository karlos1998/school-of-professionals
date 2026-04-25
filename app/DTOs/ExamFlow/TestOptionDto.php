<?php

namespace App\DTOs\ExamFlow;

readonly class TestOptionDto
{
    /**
     * @param  list<ClassOptionDto>  $classes
     */
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public int $questionCount,
        public bool $hasClassSelection,
        public array $classes,
        public string $url,
    ) {}
}
