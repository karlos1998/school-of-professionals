<?php

namespace App\Repositories\Contracts;

use App\Models\Exam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminExamRepositoryInterface
{
    public function paginate(int $perPage = 50): LengthAwarePaginator;

    public function findById(int $examId): ?Exam;

    /** @param array<string, mixed> $data */
    public function create(array $data): Exam;

    /** @param array<string, mixed> $data */
    public function update(Exam $exam, array $data): Exam;

    public function delete(Exam $exam): void;
}
