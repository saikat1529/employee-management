<?php

namespace App\Filament\Widgets;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayLeaveTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading("Today's Leave and Half Day")
            ->query(fn (): Builder => Attendance::query()
                ->with('employee')
                ->where('attendance_date', now()->toDateString())
                ->whereIn('status', [AttendanceStatus::OnLeave->value, AttendanceStatus::HalfDay->value]))
            ->columns([
                TextColumn::make('employee.employee_code')
                    ->label('Employee ID'),
                TextColumn::make('employee.full_name')
                    ->label('Name'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('leave_type')
                    ->label('Leave Type')
                    ->badge()
                    ->placeholder('-'),
            ])
            ->emptyStateHeading('Nobody is on leave or on half day today')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->paginated(false);
    }
}
