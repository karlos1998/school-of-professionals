<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ExamClassCollection extends ResourceCollection
{
    public $collects = ExamClassResource::class;

    /** @return array{data: array<int, array<string, mixed>>} */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection?->values()->all() ?? [],
        ];
    }
}
