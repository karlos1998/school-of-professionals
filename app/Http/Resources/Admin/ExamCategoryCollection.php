<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ExamCategoryCollection extends ResourceCollection
{
    public $collects = ExamCategoryResource::class;

    /** @return array{data: array<int, array<string, mixed>>} */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection?->values()->all() ?? [],
        ];
    }
}
