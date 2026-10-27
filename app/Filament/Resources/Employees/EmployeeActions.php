<?php

namespace App\Filament\Resources\Employees;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

// Suspend and Reactivate, shared by the list page and the edit page.
class EmployeeActions
{
    public static function suspend(): Action
    {
        return Action::make('suspend')
            ->label('Suspend')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Suspend this employee?')
            ->modalDescription('The employee stays in the list with status Suspended. Nothing is deleted.')
            ->modalSubmitActionLabel('Suspend')
            ->visible(fn (Employee $record) => $record->status === EmployeeStatus::Active)
            ->action(function (Employee $record, $livewire) {
                $record->suspend();
                self::refreshEditForm($livewire);
            })
            ->successNotificationTitle('Employee suspended');
    }

    public static function reactivate(): Action
    {
        return Action::make('reactivate')
            ->label('Reactivate')
            ->color('success')
            ->action(function (Employee $record, $livewire) {
                $record->reactivate();
                self::refreshEditForm($livewire);
            })
            ->visible(fn (Employee $record) => $record->status === EmployeeStatus::Suspended)
            ->successNotificationTitle('Employee reactivated');
    }

    // On the edit page, reload the status field so it shows the new value.
    private static function refreshEditForm($livewire): void
    {
        if ($livewire instanceof EditRecord) {
            $livewire->refreshFormData(['status']);
        }
    }
}
