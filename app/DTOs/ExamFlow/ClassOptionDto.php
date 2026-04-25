<?php

namespace App\DTOs\ExamFlow;

readonly class ClassOptionDto
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $url,
    ) {}
}
