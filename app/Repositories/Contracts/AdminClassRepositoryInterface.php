<?php

namespace App\Repositories\Contracts;

use App\Models\ExamClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminClassRepositoryInterface
{
    public function paginate(int $perPage = 50): LengthAwarePaginator;

    public function findById(int $classId): ?ExamClass;

    /** @param array{name:string,slug:string} $data */
    public function create(array $data): ExamClass;

    /** @param array{name:string,slug:string} $data */
    public function update(ExamClass $examClass, array $data): ExamClass;

    public function delete(ExamClass $examClass): void;
}
