<?php

namespace App\DTOs\ExamFlow;

readonly class AuthorityLinkDto
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $url,
    ) {}
}
