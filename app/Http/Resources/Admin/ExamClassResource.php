<?php

namespace App\Http\Resources\Admin;

use App\Models\ExamClass;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamClassResource extends JsonResource
{
    /** @return array{id:int,name:string,exams_count:int} */
    public function toArray(Request $request): array
    {
        /** @var ExamClass $examClass */
        $examClass = $this->resource;

        return [
            'id' => $examClass->id,
            'name' => $examClass->name,
            'exams_count' => (int) ($examClass->exams_count ?? 0),
        ];
    }
}
