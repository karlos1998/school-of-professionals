<?php

namespace App\Repositories\Contracts;

use App\Models\Question;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminQuestionRepositoryInterface
{
    public function paginateForExam(int $examId, int $perPage = 50): LengthAwarePaginator;

    /** @param array<string, mixed> $data */
    public function createForExam(int $examId, array $data): Question;

    public function findById(int $questionId): ?Question;

    /** @param array<string, mixed> $data */
    public function update(Question $question, array $data): Question;

    public function replaceAnswers(Question $question, array $answers): void;

    public function delete(Question $question): void;
}
