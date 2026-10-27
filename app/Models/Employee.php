<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['full_name', 'email', 'phone', 'department_id', 'designation', 'joining_date', 'status', 'monthly_salary'])]
class Employee extends Model
{
    protected static function booted(): void
    {
        // Give each new employee the next code, e.g. EMP-0001.
        static::creating(function (Employee $employee) {
            if (blank($employee->employee_code)) {
                $next = (int) static::query()->max('id') + 1;
                $employee->employee_code = 'EMP-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            }

            $employee->status ??= EmployeeStatus::Active;
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Only active employees are offered when recording attendance.
    public function scopeActive(Builder $query): void
    {
        $query->where('status', EmployeeStatus::Active);
    }

    public function suspend(): void
    {
        $this->update(['status' => EmployeeStatus::Suspended]);
    }

    public function reactivate(): void
    {
        $this->update(['status' => EmployeeStatus::Active]);
    }

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'status' => EmployeeStatus::class,
            'monthly_salary' => 'decimal:2',
        ];
    }
}
