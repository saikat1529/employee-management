<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeActions;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    // No delete button: "Revoke" is Suspend.
    protected function getHeaderActions(): array
    {
        return [
            EmployeeActions::suspend(),
            EmployeeActions::reactivate(),
        ];
    }
}
