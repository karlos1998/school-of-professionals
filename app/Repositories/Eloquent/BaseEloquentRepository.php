<?php

namespace App\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class BaseEloquentRepository
{
    /**
     * @template TModel of Model
     * @param Builder<TModel> $query
     * @return LengthAwarePaginator<int, TModel>
     */
    protected function paginateQuery(Builder $query, int $perPage): LengthAwarePaginator
    {
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @template TModel of Model
     * @param Builder<TModel> $query
     * @return Collection<int, array{id:int,name:string}>
     */
    protected function pluckOptions(Builder $query): Collection
    {
        /** @var Collection<int, array{id:int,name:string}> $items */
        $items = $query
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Model $item): array => [
                'id' => (int) $item->getAttribute('id'),
                'name' => (string) $item->getAttribute('name'),
            ])
            ->values();

        return $items;
    }
}
