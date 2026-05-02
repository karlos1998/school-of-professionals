<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'exam_authority_id' => $this->exam_authority_id,
            'exam_category_id' => $this->exam_category_id,
            'exam_class_id' => $this->exam_class_id,
            'authority' => $this->authority?->name,
            'category' => $this->category?->name,
            'exam_class' => $this->examClass?->name,
            'questions_count' => $this->questions_count ?? 0,
        ];
    }
}
