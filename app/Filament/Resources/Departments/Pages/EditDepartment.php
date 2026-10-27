<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentActions;
use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    // No delete button: "Revoke" is Suspend.
    protected function getHeaderActions(): array
    {
        return [
            DepartmentActions::suspend(),
            DepartmentActions::reactivate(),
        ];
    }
}
