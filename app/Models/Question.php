<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $exam_id
 * @property int $position
 * @property string|null $source_question_key
 * @property string $content
 * @property string|null $image_path
 * @property string|null $explanation
 * @property-read Exam $exam
 */
#[Fillable([
    'exam_id',
    'position',
    'source_question_key',
    'content',
    'image_path',
    'explanation',
])]
class Question extends Model
{
    /** @return BelongsTo<Exam, $this> */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /** @return HasMany<Answer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
