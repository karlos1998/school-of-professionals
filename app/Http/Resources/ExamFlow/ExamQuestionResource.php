<?php

namespace App\Http\Resources\ExamFlow;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use UnexpectedValueException;

/** @mixin Question */
class ExamQuestionResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     position: int,
     *     content: string,
     *     explanation: string|null,
     *     answers: list<array{id: int, content: string, isCorrect: bool}>
     * }
     */
    public function toArray(Request $request): array
    {
        if (! ($this->resource instanceof Question)) {
            throw new UnexpectedValueException('ExamQuestionResource expects Question model.');
        }

        return [
            'id' => $this->resource->id,
            'position' => $this->resource->position,
            'content' => $this->resource->content,
            'explanation' => $this->resource->explanation,
            'answers' => array_values(ExamAnswerResource::collection($this->resource->answers)->resolve()),
        ];
    }
}
