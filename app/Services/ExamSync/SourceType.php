<?php

namespace App\Services\ExamSync;

enum SourceType: string
{
    case Wit = 'wit';
    case Udt = 'udt';

    public function label(): string
    {
        return match ($this) {
            self::Wit => 'Testy WIT',
            self::Udt => 'Testy UDT',
        };
    }

    public function authorityName(): string
    {
        return strtoupper($this->value);
    }

    public function baseUrl(): string
    {
        return match ($this) {
            self::Wit => 'https://www.testy-wit.pl',
            self::Udt => 'https://www.testy-udt.pl',
        };
    }
}
