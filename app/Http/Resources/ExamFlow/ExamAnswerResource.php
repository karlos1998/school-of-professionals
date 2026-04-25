<?php

namespace App\Http\Resources\ExamFlow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'isCorrect' => (bool) $this->is_correct,
        ];
    }
}
