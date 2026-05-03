<?php

namespace App\Repositories\Contracts;

interface ExamSettingsRepositoryInterface
{
    public function getInt(string $key, int $default): int;

    public function setInt(string $key, int $value): void;
}
