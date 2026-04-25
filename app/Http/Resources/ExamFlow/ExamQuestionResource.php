<?php

namespace App\Http\Resources\ExamFlow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'content' => $this->content,
            'explanation' => $this->explanation,
            'answers' => ExamAnswerResource::collection($this->answers)->resolve(),
        ];
    }
}
