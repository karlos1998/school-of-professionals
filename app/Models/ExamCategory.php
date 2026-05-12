<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $is_favorite
 */
#[Fillable(['name', 'slug', 'is_favorite'])]
class ExamCategory extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_favorite' => 'bool',
        ];
    }

    /** @return HasMany<Exam, $this> */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
