<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class PayrollSetting extends Model
{
    // Default rules. The seeder stores these; the calculation falls back to them if a row is missing.
    public static function defaults(): array
    {
        return [
            'days_in_month' => '30',
            'late_days_per_absent' => '3',
            'half_day_fraction' => '0.5',
        ];
    }

    // The rules the payroll calculation reads.
    public static function rules(): array
    {
        $defaults = static::defaults();
        $stored = static::query()->pluck('value', 'key');

        return [
            'days_in_month' => (int) ($stored['days_in_month'] ?? $defaults['days_in_month']),
            'late_days_per_absent' => (int) ($stored['late_days_per_absent'] ?? $defaults['late_days_per_absent']),
            'half_day_fraction' => (float) ($stored['half_day_fraction'] ?? $defaults['half_day_fraction']),
        ];
    }
}
