<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeStatus;
use App\Enums\LeaveType;
use App\Models\Attendance;
use App\Models\Employee;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Attendance details')
                ->columns(2)
                ->schema([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->searchable()
                        ->required()
                        ->options(fn (?Attendance $record) => self::employeeOptions($record?->employee_id))
                        ->rules([
                            fn (?Attendance $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                if (blank($value) || ($record && (int) $record->employee_id === (int) $value)) {
                                    return;
                                }

                                if (! Employee::query()->active()->whereKey($value)->exists()) {
                                    $fail('Only active employees can be selected.');
                                }
                            },
                            // One row per employee per date.
                            fn (Get $get, ?Attendance $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                $date = $get('attendance_date');

                                if (blank($value) || blank($date)) {
                                    return;
                                }

                                $exists = Attendance::query()
                                    ->where('employee_id', $value)
                                    ->whereDate('attendance_date', $date)
                                    ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                                    ->exists();

                                if ($exists) {
                                    $fail('This employee already has an attendance row for this date. Edit that row instead.');
                                }
                            },
                        ]),
                    DatePicker::make('attendance_date')
                        ->label('Attendance date')
                        ->required()
                        ->default(now()),
                    Select::make('status')
                        ->options(AttendanceStatus::class)
                        ->default(AttendanceStatus::Present->value)
                        ->required()
                        ->live(),
                    Select::make('leave_type')
                        ->label('Leave type')
                        ->options(LeaveType::class)
                        ->placeholder('Select a leave type')
                        ->visible(fn (Get $get) => self::statusFromState($get('status')) === AttendanceStatus::OnLeave),
                    TimePicker::make('in_time')
                        ->label('In time')
                        ->seconds(false),
                    TimePicker::make('out_time')
                        ->label('Out time')
                        ->seconds(false),
                ]),
        ]);
    }

    /**
     * Active employees only. When editing a row whose employee is now suspended, that employee
     * stays in the list (marked as suspended) so saving the row does not lose the link.
     */
    public static function employeeOptions(?int $currentEmployeeId = null): array
    {
        return Employee::query()
            ->where(function ($query) use ($currentEmployeeId) {
                $query->active();

                if ($currentEmployeeId) {
                    $query->orWhere('id', $currentEmployeeId);
                }
            })
            ->orderBy('full_name')
            ->get()
            ->mapWithKeys(fn (Employee $employee) => [
                $employee->id => $employee->status === EmployeeStatus::Active
                    ? $employee->full_name.' ('.$employee->employee_code.')'
                    : $employee->full_name.' ('.$employee->employee_code.', suspended)',
            ])
            ->all();
    }

    // The status field can hold the enum or its raw value, depending on where the form is filled.
    public static function statusFromState(mixed $state): ?AttendanceStatus
    {
        if ($state instanceof AttendanceStatus) {
            return $state;
        }

        return filled($state) ? AttendanceStatus::tryFrom((string) $state) : null;
    }
}
