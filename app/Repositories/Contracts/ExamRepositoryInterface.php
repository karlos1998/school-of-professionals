<?php

namespace App\Repositories\Contracts;

use App\Models\Exam;
use Illuminate\Support\Collection;

interface ExamRepositoryInterface
{
    public function getExamAuthorities(): Collection;

    public function getExamsForAuthority(string $authoritySlug): Collection;

    public function getExamWithQuestionsById(int $examId): ?Exam;
}
