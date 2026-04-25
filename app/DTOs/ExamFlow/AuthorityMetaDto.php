<?php

namespace App\DTOs\ExamFlow;

readonly class AuthorityMetaDto
{
    public function __construct(
        public string $name,
        public string $slug,
    ) {}
}
