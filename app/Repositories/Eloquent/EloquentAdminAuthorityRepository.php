<?php

namespace App\Repositories\Eloquent;

use App\Models\ExamAuthority;
use App\Repositories\Contracts\AdminAuthorityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminAuthorityRepository extends BaseEloquentRepository implements AdminAuthorityRepositoryInterface
{
    /** @return LengthAwarePaginator<int, ExamAuthority> */
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateQuery(
            ExamAuthority::query()
                ->withCount('exams')
                ->orderBy('sort_order')
                ->orderBy('name'),
            $perPage,
        );
    }

    public function findById(int $authorityId): ?ExamAuthority
    {
        return ExamAuthority::query()->find($authorityId);
    }

    public function create(array $data): ExamAuthority
    {
        return ExamAuthority::query()->create($data);
    }

    public function update(ExamAuthority $authority, array $data): ExamAuthority
    {
        $authority->update($data);

        return $authority->refresh();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $authorityId) {
            ExamAuthority::query()
                ->whereKey($authorityId)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function nextSortOrder(): int
    {
        return (int) ExamAuthority::query()->max('sort_order') + 1;
    }
}
