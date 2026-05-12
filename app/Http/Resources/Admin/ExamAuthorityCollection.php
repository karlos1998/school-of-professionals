<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ExamAuthorityCollection extends ResourceCollection
{
    public $collects = ExamAuthorityResource::class;

    /** @return array{data: array<int, array<string, mixed>>} */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection?->values()->all() ?? [],
        ];
    }
}
