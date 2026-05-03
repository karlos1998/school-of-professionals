<?php

namespace App\Http\Resources\Admin;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Question $question */
        $question = $this->resource;

        return [
            'id' => $question->id,
            'position' => $question->position,
            'content' => $question->content,
            'explanation' => $question->explanation,
            'answers' => $question->answers->map(fn ($answer): array => [
                'id' => $answer->id,
                'content' => $answer->content,
                'is_correct' => $answer->is_correct,
            ])->values(),
        ];
    }
}
