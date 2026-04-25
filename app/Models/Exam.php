<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_authority_id',
        'exam_category_id',
        'exam_class_id',
        'name',
        'slug',
        'description',
    ];

    public function authority(): BelongsTo
    {
        return $this->belongsTo(ExamAuthority::class, 'exam_authority_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExamCategory::class, 'exam_category_id');
    }

    public function examClass(): BelongsTo
    {
        return $this->belongsTo(ExamClass::class, 'exam_class_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }
}
