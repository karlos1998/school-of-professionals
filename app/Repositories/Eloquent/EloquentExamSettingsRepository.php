<?php

namespace App\Repositories\Eloquent;

use App\Models\ExamSetting;
use App\Repositories\Contracts\ExamSettingsRepositoryInterface;

class EloquentExamSettingsRepository implements ExamSettingsRepositoryInterface
{
    public function getInt(string $key, int $default): int
    {
        $setting = ExamSetting::query()->where('key', $key)->first();
        if ($setting === null) {
            return $default;
        }

        $value = (int) $setting->value;

        return $value;
    }

    public function setInt(string $key, int $value): void
    {
        ExamSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value],
        );
    }
}
