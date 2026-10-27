<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Enums\EmployeeStatus;
use App\Filament\Resources\Employees\EmployeeActions;
use App\Models\Department;
use App\Support\Money;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_code')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('designation')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('monthly_salary')
                    ->label('Monthly salary')
                    ->formatStateUsing(fn ($state) => Money::bdt($state))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('joining_date')
                    ->label('Joining date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(EmployeeStatus::class),
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(fn () => Department::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
            ])
            ->recordActions([
                EditAction::make(),
                EmployeeActions::suspend(),
                EmployeeActions::reactivate(),
            ])
            ->defaultSort('employee_code', 'asc');
    }
}
