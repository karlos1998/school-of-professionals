<?php

namespace App\Http\Resources\ExamFlow;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use UnexpectedValueException;

/** @mixin Exam */
class ExamSessionResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     authoritySlug: string,
     *     testSlug: string,
     *     name: string,
     *     description: string|null,
     *     class: array{name: string, slug: string}|null,
     *     questions: list<array{
     *         id: int,
     *         position: int,
     *         content: string,
     *         imageUrl: string|null,
     *         explanation: string|null,
     *         answers: list<array{id: int, content: string, isCorrect: bool}>
     *     }>
     * }
     */
    public function toArray(Request $request): array
    {
        if (! ($this->resource instanceof Exam)) {
            throw new UnexpectedValueException('ExamSessionResource expects Exam model.');
        }

        return [
            'id' => $this->resource->id,
            'authoritySlug' => $this->resource->authority->slug,
            'testSlug' => $this->resource->category->slug,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'class' => $this->resource->examClass ? [
                'name' => $this->resource->examClass->name,
                'slug' => $this->resource->examClass->slug,
            ] : null,
            'questions' => array_values(ExamQuestionResource::collection($this->resource->questions)->resolve()),
        ];
    }
}
