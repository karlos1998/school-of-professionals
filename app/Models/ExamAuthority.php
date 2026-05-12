<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $sort_order
 */
#[Fillable(['name', 'slug', 'sort_order'])]
class ExamAuthority extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'int',
        ];
    }

    /** @return HasMany<Exam, $this> */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
