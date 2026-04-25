<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 */
class ExamAuthority extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
    ];

    /** @return HasMany<Exam, $this> */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
