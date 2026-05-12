<?php

namespace App\Http\Resources\Admin;

use App\Models\ExamCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamCategoryResource extends JsonResource
{
    /** @return array{id:int,name:string,exams_count:int} */
    public function toArray(Request $request): array
    {
        /** @var ExamCategory $category */
        $category = $this->resource;

        return [
            'id' => $category->id,
            'name' => $category->name,
            'exams_count' => (int) ($category->exams_count ?? 0),
        ];
    }
}
