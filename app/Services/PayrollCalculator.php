<?php

namespace App\Services;

/**
 * Pure payroll maths: no database, no dates. Same inputs always give the same outputs.
 *
 * Inputs:
 *  - $monthlySalary: salary in BDT
 *  - $counts: present, late, half_day, absent, leave (number of attendance rows per status)
 *  - $rules: days_in_month, late_days_per_absent, half_day_fraction
 *
 * Rules:
 *  - Present and On Leave: no deduction.
 *  - Late: no deduction by itself. Every late_days_per_absent Late days = 1 absent day.
 *  - Absent: 1 day deduction each, plus the late-equivalent days.
 *  - Half Day: half_day_fraction of a day each (0.5 by default).
 *  - Daily rate = salary / days_in_month.
 *  - Deduction = (absent + late equivalent + half days x fraction) x daily rate, rounded to 2 decimals.
 *  - Net pay = salary - deduction, rounded to 2 decimals, never below 0.
 */
class PayrollCalculator
{
    /**
     * @param  array{present?: int, late?: int, half_day?: int, absent?: int, leave?: int}  $counts
     * @param  array{days_in_month: int, late_days_per_absent: int, half_day_fraction: float}  $rules
     * @return array{
     *     daily_rate: float,
     *     late_equivalent_days: int,
     *     absent_deduction_days: float,
     *     half_day_deduction_days: float,
     *     deduction: float,
     *     net_pay: float
     * }
     */
    public function calculate(float $monthlySalary, array $counts, array $rules): array
    {
        $absent = (int) ($counts['absent'] ?? 0);
        $late = (int) ($counts['late'] ?? 0);
        $halfDay = (int) ($counts['half_day'] ?? 0);

        $daysInMonth = max(1, (int) $rules['days_in_month']);
        $latePerAbsent = max(1, (int) $rules['late_days_per_absent']);
        $halfDayFraction = (float) $rules['half_day_fraction'];

        $lateEquivalent = intdiv($late, $latePerAbsent);
        $absentDeductionDays = (float) ($absent + $lateEquivalent);
        $halfDayDeductionDays = $halfDay * $halfDayFraction;

        $dailyRate = $monthlySalary / $daysInMonth;
        $deduction = round(($absentDeductionDays + $halfDayDeductionDays) * $dailyRate, 2);
        $netPay = max(0.0, round($monthlySalary - $deduction, 2));

        return [
            'daily_rate' => round($dailyRate, 4),
            'late_equivalent_days' => $lateEquivalent,
            'absent_deduction_days' => $absentDeductionDays,
            'half_day_deduction_days' => round($halfDayDeductionDays, 2),
            'deduction' => $deduction,
            'net_pay' => $netPay,
        ];
    }
}
