<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $exam_authority_id
 * @property int $exam_category_id
 * @property int|null $exam_class_id
 * @property string|null $source
 * @property string|null $source_slug
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $questions_count
 * @property-read ExamAuthority $authority
 * @property-read ExamCategory $category
 * @property-read ExamClass|null $examClass
 */
#[Fillable([
    'exam_authority_id',
    'exam_category_id',
    'exam_class_id',
    'source',
    'source_slug',
    'name',
    'slug',
    'description',
])]
class Exam extends Model
{
    /** @return BelongsTo<ExamAuthority, $this> */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(ExamAuthority::class, 'exam_authority_id');
    }

    /** @return BelongsTo<ExamCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExamCategory::class, 'exam_category_id');
    }

    /** @return BelongsTo<ExamClass, $this> */
    public function examClass(): BelongsTo
    {
        return $this->belongsTo(ExamClass::class, 'exam_class_id');
    }

    /** @return HasMany<Question, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }
}
