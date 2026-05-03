<?php

namespace App\Repositories\Eloquent;

use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\ExamClass;
use App\Repositories\Contracts\AdminLookupRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAdminLookupRepository extends BaseEloquentRepository implements AdminLookupRepositoryInterface
{
    public function examAuthorities(): Collection
    {
        return $this->pluckOptions(ExamAuthority::query());
    }

    public function examCategories(): Collection
    {
        return $this->pluckOptions(ExamCategory::query());
    }

    public function examClasses(): Collection
    {
        return $this->pluckOptions(ExamClass::query());
    }
}
