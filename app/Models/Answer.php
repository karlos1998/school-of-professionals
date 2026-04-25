<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $question_id
 * @property string $content
 * @property bool $is_correct
 * @property-read Question $question
 */
#[Fillable([
    'question_id',
    'content',
    'is_correct',
])]
class Answer extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_correct' => 'bool',
        ];
    }

    /** @return BelongsTo<Question, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
