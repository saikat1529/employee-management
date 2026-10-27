<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'attendance_date', 'status', 'leave_type', 'in_time', 'out_time'])]
class Attendance extends Model
{
    protected static function booted(): void
    {
        // Keep the leave type only for On Leave rows, and recalculate working hours on every save.
        static::saving(function (Attendance $attendance) {
            if ($attendance->status !== AttendanceStatus::OnLeave) {
                $attendance->leave_type = null;
            }

            $attendance->working_hours = self::calculateWorkingHours($attendance->in_time, $attendance->out_time);
        });
    }

    /**
     * Hours between in and out, as a 2-decimal string.
     * Returns 0.00 when a time is missing, and when out is not after in (no crash, no negative hours).
     */
    public static function calculateWorkingHours(?string $inTime, ?string $outTime): string
    {
        $in = self::toMinutes($inTime);
        $out = self::toMinutes($outTime);

        if ($in === null || $out === null || $out <= $in) {
            return '0.00';
        }

        return number_format(round(($out - $in) / 60, 2), 2, '.', '');
    }

    // Accepts "09:30", "09:30:00" or a full datetime ending in a time.
    private static function toMinutes(?string $time): ?int
    {
        if (blank($time) || ! preg_match('/(\d{1,2}):(\d{2})(?::\d{2})?\s*$/', $time, $matches)) {
            return null;
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date:Y-m-d',
            'status' => AttendanceStatus::class,
            'leave_type' => LeaveType::class,
            'working_hours' => 'decimal:2',
        ];
    }
}
