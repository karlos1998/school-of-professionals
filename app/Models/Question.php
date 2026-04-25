<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $exam_id
 * @property int $position
 * @property string $content
 * @property string|null $explanation
 * @property-read Exam $exam
 */
class Question extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'exam_id',
        'position',
        'content',
        'explanation',
    ];

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
