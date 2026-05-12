<?php

namespace App\Repositories\Eloquent;

use App\Models\ExamCategory;
use App\Repositories\Contracts\AdminCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminCategoryRepository extends BaseEloquentRepository implements AdminCategoryRepositoryInterface
{
    /** @return LengthAwarePaginator<int, ExamCategory> */
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateQuery(
            ExamCategory::query()
                ->withCount('exams')
                ->orderByDesc('is_favorite')
                ->orderBy('name'),
            $perPage,
        );
    }

    public function findById(int $categoryId): ?ExamCategory
    {
        return ExamCategory::query()->find($categoryId);
    }

    public function create(array $data): ExamCategory
    {
        return ExamCategory::query()->create($data);
    }

    public function update(ExamCategory $category, array $data): ExamCategory
    {
        $category->update($data);

        return $category->refresh();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = ExamCategory::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function delete(ExamCategory $category): void
    {
        $category->delete();
    }
}
