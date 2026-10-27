<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PayrollStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Paid = 'paid';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Paid => 'Paid',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Paid => 'success',
        };
    }
}
