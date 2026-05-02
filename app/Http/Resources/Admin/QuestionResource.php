<?php

namespace App\Http\Resources\Admin;

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
        return [
            'id' => $this->id,
            'position' => $this->position,
            'content' => $this->content,
            'explanation' => $this->explanation,
            'answers' => $this->answers->map(fn ($answer): array => [
                'id' => $answer->id,
                'content' => $answer->content,
                'is_correct' => $answer->is_correct,
            ])->values(),
        ];
    }
}
