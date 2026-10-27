<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\DepartmentStatus;
use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Employee details')
                ->columns(2)
                ->schema([
                    // Shown on the edit page only. The code is generated on create and cannot be changed.
                    TextInput::make('employee_code')
                        ->label('Employee ID')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (?Employee $record) => filled($record?->employee_code)),
                    TextInput::make('full_name')
                        ->label('Full name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),
                    Select::make('department_id')
                        ->label('Department')
                        // Active departments only. An employee who already belongs to a suspended
                        // department still sees that one (marked as suspended) so saving keeps it.
                        ->options(function (?Employee $record) {
                            $departments = Department::query()
                                ->where(function ($query) use ($record) {
                                    $query->active();

                                    if ($record?->department_id) {
                                        $query->orWhere('id', $record->department_id);
                                    }
                                })
                                ->orderBy('name')
                                ->get();

                            return $departments
                                ->mapWithKeys(fn (Department $department) => [
                                    $department->id => $department->status === DepartmentStatus::Active
                                        ? $department->name
                                        : $department->name.' (suspended)',
                                ])
                                ->all();
                        })
                        ->searchable()
                        ->placeholder('Select a department'),
                    TextInput::make('designation')
                        ->maxLength(255),
                    TextInput::make('monthly_salary')
                        ->label('Monthly salary (BDT)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('৳'),
                    DatePicker::make('joining_date')
                        ->label('Joining date')
                        ->required(),
                    Select::make('status')
                        ->options(EmployeeStatus::class)
                        ->default(EmployeeStatus::Active)
                        ->required(),
                ]),
        ]);
    }
}
