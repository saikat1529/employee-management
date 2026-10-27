<?php

namespace App\Filament\Widgets;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BestEmployeesTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Best employees (last 30 days)')
            ->query(fn (): Builder => $this->bestEmployeesQuery())
            ->columns([
                TextColumn::make('employee_code')
                    ->label('Employee ID'),
                TextColumn::make('full_name')
                    ->label('Name'),
                TextColumn::make('present_days')
                    ->label('Present days'),
            ])
            ->emptyStateHeading('No Present days in the last 30 days')
            ->emptyStateIcon('heroicon-o-trophy')
            ->paginated(false);
    }

    // Active employees with the most Present days in the last 30 days (today included).
    // Everyone tied at the top count is returned, sorted by name.
    private function bestEmployeesQuery(): Builder
    {
        $today = now();
        $start = $today->copy()->subDays(29)->toDateString();

        $presentDays = Attendance::query()
            ->selectRaw('count(*)')
            ->whereColumn('attendances.employee_id', 'employees.id')
            ->where('status', AttendanceStatus::Present->value)
            ->whereBetween('attendance_date', [$start, $today->toDateString()]);

        $query = Employee::query()
            ->active()
            ->select('employees.*')
            ->selectSub($presentDays, 'present_days');

        $top = (int) (clone $query)->orderByDesc('present_days')->first()?->present_days;

        if ($top === 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereRaw('('.$presentDays->toBase()->toSql().') = ?', [...$presentDays->toBase()->getBindings(), $top])
            ->orderBy('full_name');
    }
}
