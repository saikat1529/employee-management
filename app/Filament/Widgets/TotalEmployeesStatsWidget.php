<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalEmployeesStatsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $active = Employee::query()->active()->count();
        $all = Employee::query()->count();

        return [
            Stat::make('Total Employees', $active)
                ->description('Total including suspended: '.$all),
        ];
    }
}
