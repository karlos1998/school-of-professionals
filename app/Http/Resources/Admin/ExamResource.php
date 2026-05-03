<?php

namespace App\Http\Resources\Admin;

use App\Models\Exam;
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
        /** @var Exam $exam */
        $exam = $this->resource;

        return [
            'id' => $exam->id,
            'name' => $exam->name,
            'description' => $exam->description,
            'exam_authority_id' => $exam->exam_authority_id,
            'exam_category_id' => $exam->exam_category_id,
            'exam_class_id' => $exam->exam_class_id,
            'authority' => $exam->authority->name,
            'category' => $exam->category->name,
            'exam_class' => $exam->examClass?->name,
            'questions_count' => (int) ($exam->questions_count ?? 0),
        ];
    }
}
