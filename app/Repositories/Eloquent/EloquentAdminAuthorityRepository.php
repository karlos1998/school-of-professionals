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
                ->orderBy('name'),
            $perPage,
        );
    }

    public function findById(int $authorityId): ?ExamAuthority
    {
        return ExamAuthority::query()->find($authorityId);
    }

    public function update(ExamAuthority $authority, array $data): ExamAuthority
    {
        $authority->update($data);

        return $authority->refresh();
    }
}
