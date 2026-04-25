<?php

namespace App\Repositories\Contracts;

use App\Models\Exam;
use App\Models\ExamAuthority;
use Illuminate\Support\Collection;

interface ExamRepositoryInterface
{
    /** @return Collection<int, ExamAuthority> */
    public function getExamAuthorities(): Collection;

    /** @return Collection<int, Exam> */
    public function getExamsForAuthority(string $authoritySlug): Collection;

    public function getExamWithQuestionsById(int $examId): ?Exam;
}
