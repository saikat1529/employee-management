<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Enums\DepartmentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Department details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Department name')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('status')
                        ->options(DepartmentStatus::class)
                        ->default(DepartmentStatus::Active)
                        ->required(),
                ]),
        ]);
    }
}
