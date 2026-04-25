<?php

namespace App\Http\Resources\ExamFlow;

use App\Models\Answer;
use UnexpectedValueException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Answer */
class ExamAnswerResource extends JsonResource
{
    /**
     * @return array{id: int, content: string, isCorrect: bool}
     */
    public function toArray(Request $request): array
    {
        if (!($this->resource instanceof Answer)) {
            throw new UnexpectedValueException('ExamAnswerResource expects Answer model.');
        }

        return [
            'id' => $this->resource->id,
            'content' => $this->resource->content,
            'isCorrect' => $this->resource->is_correct,
        ];
    }
}
