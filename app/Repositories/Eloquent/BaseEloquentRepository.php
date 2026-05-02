<?php

namespace App\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class BaseEloquentRepository
{
    protected function paginateQuery(Builder $query, int $perPage): LengthAwarePaginator
    {
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, array{id:int,name:string}>
     */
    protected function pluckOptions(Builder $query): Collection
    {
        /** @var Collection<int, array{id:int,name:string}> $items */
        $items = $query
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => $item->id, 'name' => $item->name])
            ->values();

        return $items;
    }
}
