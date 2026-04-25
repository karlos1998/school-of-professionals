<?php

namespace App\DTOs\ExamFlow;

readonly class AuthorityTestsDto
{
    /**
     * @param  list<TestOptionDto>  $tests
     */
    public function __construct(
        public AuthorityMetaDto $authority,
        public array $tests,
    ) {}
}
