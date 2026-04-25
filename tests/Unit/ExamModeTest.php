<?php

namespace Tests\Unit;

use App\Enums\ExamMode;
use PHPUnit\Framework\TestCase;

class ExamModeTest extends TestCase
{
    public function test_it_returns_expected_labels(): void
    {
        self::assertSame('Po kolei + feedback', ExamMode::Sequential->label());
        self::assertSame('Losowo + feedback', ExamMode::Random->label());
        self::assertSame('Tryb nauki', ExamMode::Study->label());
        self::assertSame('Egzamin', ExamMode::Exam->label());
    }
}
