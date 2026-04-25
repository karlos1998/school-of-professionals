<?php

namespace App\DTOs\ExamFlow;

use App\Models\Exam;

readonly class ExamVariantDto
{
    public function __construct(
        public Exam $exam,
        public AuthorityMetaDto $authority,
        public AuthorityMetaDto $test,
        public ?AuthorityMetaDto $selectedClass,
    ) {}
}
