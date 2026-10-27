<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\PayrollStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\PayrollSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PayrollService
{
    public function __construct(private PayrollCalculator $calculator) {}

    /**
     * Creates the run for a month if needed, then (re)calculates one item per ACTIVE employee.
     * A paid run is locked and is never recalculated.
     *
     * @throws InvalidArgumentException when the month is not YYYY-MM
     * @throws RuntimeException when the run is already paid
     */
    public function generate(string $month): PayrollRun
    {
        $start = $this->monthStart($month);
        $end = $start->endOfMonth();

        return DB::transaction(function () use ($month, $start, $end) {
            $run = PayrollRun::query()->firstOrCreate(
                ['month' => $month],
                ['status' => PayrollStatus::Draft],
            );

            if ($run->status === PayrollStatus::Paid) {
                throw new RuntimeException('This month is already paid. Its payroll is locked and cannot be recalculated.');
            }

            $rules = PayrollSetting::rules();
            $countsByEmployee = $this->countsByEmployee($start, $end);
            $keptEmployeeIds = [];

            foreach (Employee::query()->active()->orderBy('employee_code')->get() as $employee) {
                $counts = $countsByEmployee[$employee->id] ?? [];
                $salary = (float) $employee->monthly_salary;
                $result = $this->calculator->calculate($salary, $counts, $rules);

                PayrollItem::query()->updateOrCreate(
                    ['run_id' => $run->id, 'employee_id' => $employee->id],
                    [
                        'monthly_salary' => $salary,
                        'daily_rate' => $result['daily_rate'],
                        'present_days' => $counts['present'] ?? 0,
                        'late_days' => $counts['late'] ?? 0,
                        'half_day_days' => $counts['half_day'] ?? 0,
                        'absent_days' => $counts['absent'] ?? 0,
                        'leave_days' => $counts['leave'] ?? 0,
                        'late_equivalent_days' => $result['late_equivalent_days'],
                        'absent_deduction_days' => $result['absent_deduction_days'],
                        'half_day_deduction_days' => $result['half_day_deduction_days'],
                        'deduction' => $result['deduction'],
                        'net_pay' => $result['net_pay'],
                    ],
                );

                $keptEmployeeIds[] = $employee->id;
            }

            // Draft only: items for employees who are no longer active are removed so they do not stay stale.
            $run->items()->whereNotIn('employee_id', $keptEmployeeIds)->delete();

            return $run->refresh();
        });
    }

    /**
     * Marks a draft run as paid. Nothing is deleted.
     *
     * @throws RuntimeException when the run is already paid
     */
    public function markAsPaid(PayrollRun $run): PayrollRun
    {
        if ($run->status === PayrollStatus::Paid) {
            throw new RuntimeException('This payroll is already marked as paid.');
        }

        $run->update([
            'status' => PayrollStatus::Paid,
            'paid_at' => now(),
        ]);

        return $run->refresh();
    }

    public function monthStart(string $month): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            throw new InvalidArgumentException("Invalid month [{$month}]. Use YYYY-MM.");
        }

        return CarbonImmutable::createFromFormat('!Y-m', $month)->startOfMonth();
    }

    /**
     * Counts of each attendance status per employee, inside the month.
     * Days with no record are not counted, so they add no deduction.
     *
     * @return array<int, array<string, int>>
     */
    private function countsByEmployee(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $keys = [
            AttendanceStatus::Present->value => 'present',
            AttendanceStatus::Late->value => 'late',
            AttendanceStatus::HalfDay->value => 'half_day',
            AttendanceStatus::Absent->value => 'absent',
            AttendanceStatus::OnLeave->value => 'leave',
        ];

        $counts = [];

        $rows = Attendance::query()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('employee_id, status, count(*) as total')
            ->groupBy('employee_id', 'status')
            ->get();

        foreach ($rows as $row) {
            // The model casts status to an enum, so take its string value before the lookup.
            $status = $row->status instanceof AttendanceStatus ? $row->status->value : (string) $row->status;

            if (isset($keys[$status])) {
                $counts[(int) $row->employee_id][$keys[$status]] = (int) $row->total;
            }
        }

        return $counts;
    }
}
