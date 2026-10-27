<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'run_id', 'employee_id', 'monthly_salary', 'daily_rate',
    'present_days', 'late_days', 'half_day_days', 'absent_days', 'leave_days',
    'late_equivalent_days', 'absent_deduction_days', 'half_day_deduction_days',
    'deduction', 'net_pay',
])]
class PayrollItem extends Model
{
    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * The deduction lines shown on the payslip and the PDF. Amounts use the stored daily rate.
     *
     * @return array<int, array{label: string, days: float, amount: float}>
     */
    public function deductionLines(): array
    {
        $rate = (float) $this->daily_rate;

        return [
            [
                'label' => 'Absent days',
                'days' => (float) $this->absent_days,
                'amount' => round((float) $this->absent_days * $rate, 2),
            ],
            [
                'label' => 'Late days turned into absent days (every 3 Late days = 1 Absent day)',
                'days' => (float) $this->late_equivalent_days,
                'amount' => round((float) $this->late_equivalent_days * $rate, 2),
            ],
            [
                'label' => 'Half days (half a day each)',
                'days' => (float) $this->half_day_deduction_days,
                'amount' => round((float) $this->half_day_deduction_days * $rate, 2),
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'monthly_salary' => 'decimal:2',
            'daily_rate' => 'decimal:4',
            'absent_deduction_days' => 'decimal:2',
            'half_day_deduction_days' => 'decimal:2',
            'deduction' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }
}
