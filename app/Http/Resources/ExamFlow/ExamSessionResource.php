<?php

namespace App\Http\Resources\ExamFlow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'authoritySlug' => $this->authority->slug,
            'testSlug' => $this->category->slug,
            'name' => $this->name,
            'description' => $this->description,
            'class' => $this->examClass ? [
                'name' => $this->examClass->name,
                'slug' => $this->examClass->slug,
            ] : null,
            'questions' => ExamQuestionResource::collection($this->questions)->resolve(),
        ];
    }
}
