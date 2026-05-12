<?php

namespace App\Http\Resources\Admin;

use App\Models\ExamAuthority;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamAuthorityResource extends JsonResource
{
    /** @return array{id:int,name:string,slug:string,sort_order:int,exams_count:int} */
    public function toArray(Request $request): array
    {
        /** @var ExamAuthority $authority */
        $authority = $this->resource;

        return [
            'id' => $authority->id,
            'name' => $authority->name,
            'slug' => $authority->slug,
            'sort_order' => $authority->sort_order,
            'exams_count' => (int) ($authority->exams_count ?? 0),
        ];
    }
}
