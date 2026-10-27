<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BestEmployeesTableWidget;
use App\Filament\Widgets\EmployeesByDepartmentChartWidget;
use App\Filament\Widgets\LateAttendanceRateChartWidget;
use App\Filament\Widgets\TodayLeaveTableWidget;
use App\Filament\Widgets\TotalEmployeesStatsWidget;
use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    // Greeting based on the app timezone (config/app.php).
    public function getTitle(): string
    {
        $hour = (int) now()->format('G');

        $greeting = match (true) {
            $hour < 12 => 'Good Morning',
            $hour < 17 => 'Good Afternoon',
            default => 'Good Evening',
        };

        return $greeting.' Admin';
    }

    public function getSubheading(): ?string
    {
        $user = auth()->user();

        return sprintf(
            'Signed in as %s (%s) · %s',
            $user?->name,
            $user?->email,
            now()->translatedFormat('l, j F Y'),
        );
    }

    /**
     * Stats row, then the late-rate chart, the two tables side by side, then the department pie.
     */
    public function getWidgets(): array
    {
        return [
            TotalEmployeesStatsWidget::class,
            LateAttendanceRateChartWidget::class,
            TodayLeaveTableWidget::class,
            BestEmployeesTableWidget::class,
            EmployeesByDepartmentChartWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }
}
