<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasColor, HasLabel
{
    case Present = 'present';
    case Absent = 'absent';
    case OnLeave = 'on_leave';
    case Late = 'late';
    case HalfDay = 'half_day';

    public function getLabel(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::OnLeave => 'On Leave',
            self::Late => 'Late',
            self::HalfDay => 'Half Day',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::OnLeave => 'gray',
            self::Late => 'warning',
            self::HalfDay => 'info',
        };
    }
}
