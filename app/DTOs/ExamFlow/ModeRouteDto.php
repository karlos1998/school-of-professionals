<?php

namespace App\DTOs\ExamFlow;

readonly class ModeRouteDto
{
    public function __construct(
        public string $value,
        public string $label,
        public string $url,
    ) {}
}
