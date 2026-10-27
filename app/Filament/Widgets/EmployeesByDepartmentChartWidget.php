<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeesByDepartmentChartWidget extends ChartWidget
{
    protected ?string $heading = 'Employees by department';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $departments = Department::query()
            ->withCount(['employees as active_employees_count' => fn (Builder $query) => $query->active()])
            ->orderBy('name')
            ->get()
            ->filter(fn (Department $department) => $department->active_employees_count > 0)
            ->values();

        $palette = ['#d97706', '#2563eb', '#059669', '#dc2626', '#7c3aed', '#0891b2', '#db2777', '#65a30d', '#ea580c', '#4f46e5'];

        return [
            'datasets' => [
                [
                    'data' => $departments->pluck('active_employees_count')->all(),
                    'backgroundColor' => $departments->keys()
                        ->map(fn (int $index) => $palette[$index % count($palette)])
                        ->all(),
                ],
            ],
            // Legend shows the department name and its active employee count.
            'labels' => $departments
                ->map(fn (Department $department) => $department->name.' ('.$department->active_employees_count.')')
                ->all(),
        ];
    }

    public function getEmptyStateHeading(): string
    {
        return 'No active employees in any department yet';
    }

    public function getEmptyStateDescription(): ?string
    {
        return 'Add an employee to a department to see this chart.';
    }
}
