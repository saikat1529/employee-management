<?php

namespace App\Filament\Resources\Departments;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

// Suspend and Reactivate, shared by the list page and the edit page.
class DepartmentActions
{
    public static function suspend(): Action
    {
        return Action::make('suspend')
            ->label('Suspend')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Suspend this department?')
            ->modalDescription('The department is hidden from the employee department list. Existing employees keep their department. Nothing is deleted.')
            ->modalSubmitActionLabel('Suspend')
            ->visible(fn (Department $record) => $record->status === DepartmentStatus::Active)
            ->action(function (Department $record, $livewire) {
                $record->suspend();
                self::refreshEditForm($livewire);
            })
            ->successNotificationTitle('Department suspended');
    }

    public static function reactivate(): Action
    {
        return Action::make('reactivate')
            ->label('Reactivate')
            ->color('success')
            ->action(function (Department $record, $livewire) {
                $record->reactivate();
                self::refreshEditForm($livewire);
            })
            ->visible(fn (Department $record) => $record->status === DepartmentStatus::Suspended)
            ->successNotificationTitle('Department reactivated');
    }

    // On the edit page, reload the status field so it shows the new value.
    private static function refreshEditForm($livewire): void
    {
        if ($livewire instanceof EditRecord) {
            $livewire->refreshFormData(['status']);
        }
    }
}
