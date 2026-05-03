<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ExamCollection extends ResourceCollection
{
    public $collects = ExamResource::class;

    /** @return array{data: array<int, array<string, mixed>>} */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection?->values()->all() ?? [],
        ];
    }

    /**
     * @param mixed $request
     * @param array<string, mixed> $paginated
     * @param array<string, mixed> $default
     * @return array{pagination: array{current_page:int,last_page:int,per_page:int,total:int}}
     */
    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'pagination' => [
                'current_page' => $paginated['current_page'],
                'last_page' => $paginated['last_page'],
                'per_page' => $paginated['per_page'],
                'total' => $paginated['total'],
            ],
        ];
    }
}
