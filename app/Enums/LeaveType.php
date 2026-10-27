<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeaveType: string implements HasLabel
{
    case Casual = 'casual';
    case Sick = 'sick';
    case Annual = 'annual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Casual => 'Casual',
            self::Sick => 'Sick',
            self::Annual => 'Annual',
        };
    }
}
