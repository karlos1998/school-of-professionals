<?php

namespace App\Repositories\Contracts;

use App\Models\ExamCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminCategoryRepositoryInterface
{
    /** @return LengthAwarePaginator<int, ExamCategory> */
    public function paginate(int $perPage = 50): LengthAwarePaginator;

    public function findById(int $categoryId): ?ExamCategory;

    /** @param array{name:string,slug:string} $data */
    public function create(array $data): ExamCategory;

    /** @param array{name:string,slug:string} $data */
    public function update(ExamCategory $category, array $data): ExamCategory;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    public function delete(ExamCategory $category): void;
}
