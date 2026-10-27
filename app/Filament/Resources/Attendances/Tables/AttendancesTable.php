<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveType;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Employee Name')
                    // Search by name or by employee ID (e.g. EMP-0001).
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('employee', fn (Builder $employees) => $employees
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%"));
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('attendance_date')
                    ->label('Attendance Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('in_time')
                    ->label('In Time')
                    ->formatStateUsing(fn ($state) => self::formatTime($state))
                    ->placeholder('-'),
                TextColumn::make('out_time')
                    ->label('Out Time')
                    ->formatStateUsing(fn ($state) => self::formatTime($state))
                    ->placeholder('-'),
                TextColumn::make('working_hours')
                    ->label('Working Hours')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'full_name')
                    ->searchable(),
                SelectFilter::make('status')
                    ->options(AttendanceStatus::class),
                SelectFilter::make('leave_type')
                    ->label('Leave Type')
                    ->options(LeaveType::class),
                Filter::make('attendance_date')
                    ->label('Attendance date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $from) => $query->whereDate('attendance_date', '>=', $from))
                            ->when($data['until'] ?? null, fn (Builder $query, $until) => $query->whereDate('attendance_date', '<=', $until));
                    }),
            ], layout: FiltersLayout::AboveContent)
            // Edit only. No delete action: nothing is deleted in the demo.
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    private static function formatTime(?string $time): ?string
    {
        if (blank($time)) {
            return null;
        }

        // Show HH:MM only, whether the stored value has seconds or not.
        return substr($time, 0, 5);
    }
}
