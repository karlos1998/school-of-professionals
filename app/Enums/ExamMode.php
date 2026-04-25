<?php

namespace App\Enums;

enum ExamMode: string
{
    case Sequential = 'sequential';
    case Random = 'random';
    case Study = 'study';
    case Exam20 = 'exam20';

    public function label(): string
    {
        return match ($this) {
            self::Sequential => 'Po kolei + feedback',
            self::Random => 'Losowo + feedback',
            self::Study => 'Tryb nauki',
            self::Exam20 => 'Losowe 20 pytań',
        };
    }
}
