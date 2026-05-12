<?php

namespace App\Repositories\Contracts;

use App\Models\ExamAuthority;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminAuthorityRepositoryInterface
{
    /** @return LengthAwarePaginator<int, ExamAuthority> */
    public function paginate(int $perPage = 50): LengthAwarePaginator;

    public function findById(int $authorityId): ?ExamAuthority;

    /** @param array{name:string} $data */
    public function update(ExamAuthority $authority, array $data): ExamAuthority;
}
