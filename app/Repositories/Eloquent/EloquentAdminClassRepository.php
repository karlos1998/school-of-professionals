<?php

namespace App\Repositories\Eloquent;

use App\Models\ExamClass;
use App\Repositories\Contracts\AdminClassRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminClassRepository extends BaseEloquentRepository implements AdminClassRepositoryInterface
{
    /** @return LengthAwarePaginator<int, ExamClass> */
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateQuery(
            ExamClass::query()
                ->withCount('exams')
                ->orderBy('name'),
            $perPage,
        );
    }

    public function findById(int $classId): ?ExamClass
    {
        return ExamClass::query()->find($classId);
    }

    public function create(array $data): ExamClass
    {
        return ExamClass::query()->create($data);
    }

    public function update(ExamClass $examClass, array $data): ExamClass
    {
        $examClass->update($data);

        return $examClass->refresh();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = ExamClass::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function delete(ExamClass $examClass): void
    {
        $examClass->delete();
    }
}
