<?php

namespace App\Filament\Resources\Attendances;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeStatus;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceActions
{
    // Bulk form: one date and one status for many active employees. Existing rows are never overwritten.
    public static function markAttendance(): Action
    {
        return Action::make('markAttendance')
            ->label('Mark Attendance')
            ->modalHeading('Mark attendance')
            ->modalDescription('Creates one row per selected employee for this date. Employees who already have a row for this date are skipped.')
            ->modalSubmitActionLabel('Mark attendance')
            ->schema([
                DatePicker::make('attendance_date')
                    ->label('Attendance date')
                    ->required()
                    ->default(now()),
                Select::make('status')
                    ->options(AttendanceStatus::class)
                    ->required()
                    ->default(AttendanceStatus::Present->value),
                Select::make('employee_ids')
                    ->label('Employees')
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->options(fn () => Employee::query()
                        ->active()
                        ->orderBy('full_name')
                        ->get()
                        ->mapWithKeys(fn (Employee $employee) => [
                            $employee->id => $employee->full_name.' ('.$employee->employee_code.')',
                        ])
                        ->all())
                    // The server also checks that every selected employee is active.
                    ->rules([
                        Rule::exists('employees', 'id')->where('status', EmployeeStatus::Active->value),
                    ]),
                TimePicker::make('in_time')
                    ->label('In time')
                    ->seconds(false),
                TimePicker::make('out_time')
                    ->label('Out time')
                    ->seconds(false),
            ])
            ->action(function (array $data) {
                $date = Carbon::parse($data['attendance_date'])->toDateString();
                $status = $data['status'] instanceof AttendanceStatus
                    ? $data['status']
                    : AttendanceStatus::from($data['status']);
                $employeeIds = array_values(array_unique(array_map('intval', $data['employee_ids'] ?? [])));

                $alreadyMarked = Attendance::query()
                    ->whereDate('attendance_date', $date)
                    ->whereIn('employee_id', $employeeIds)
                    ->pluck('employee_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $toCreate = array_values(array_diff($employeeIds, $alreadyMarked));
                $skipped = count($employeeIds) - count($toCreate);

                DB::transaction(function () use ($toCreate, $date, $status, $data) {
                    foreach ($toCreate as $employeeId) {
                        Attendance::create([
                            'employee_id' => $employeeId,
                            'attendance_date' => $date,
                            'status' => $status,
                            'in_time' => $data['in_time'] ?? null,
                            'out_time' => $data['out_time'] ?? null,
                        ]);
                    }
                });

                $created = count($toCreate);

                Notification::make()
                    ->title("Attendance marked: {$created} created, {$skipped} skipped")
                    ->body($skipped > 0
                        ? "{$skipped} employee(s) already had a row for this date and were skipped. Existing rows were not changed."
                        : 'Every selected employee now has a row for this date.')
                    ->status($created > 0 ? 'success' : 'warning')
                    ->send();
            });
    }
}
