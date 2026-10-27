<?php

namespace App\Filament\Widgets;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Filament\Widgets\ChartWidget;

class LateAttendanceRateChartWidget extends ChartWidget
{
    protected ?string $heading = 'Late attendance rate (last 30 days)';

    protected ?string $description = 'Share of attendance records marked Late, per day.';

    protected int|string|array $columnSpan = 'full';

    protected ?array $options = [
        'scales' => ['y' => ['beginAtZero' => true]],
    ];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $today = now();
        $start = $today->copy()->subDays(29)->startOfDay();

        // Dates are compared as Y-m-d strings so the day boundaries are exact.
        $rows = Attendance::query()
            ->whereBetween('attendance_date', [$start->toDateString(), $today->toDateString()])
            ->get(['attendance_date', 'status']);

        $totals = [];
        $lates = [];

        foreach ($rows as $row) {
            $day = $row->attendance_date->format('Y-m-d');
            $totals[$day] = ($totals[$day] ?? 0) + 1;

            if ($row->status === AttendanceStatus::Late) {
                $lates[$day] = ($lates[$day] ?? 0) + 1;
            }
        }

        $labels = [];
        $data = [];

        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->format('Y-m-d');

            $labels[] = $day->format('M j');
            $data[] = isset($totals[$key])
                ? round(($lates[$key] ?? 0) / $totals[$key] * 100, 1)
                : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Late rate (%)',
                    'data' => $data,
                    'borderColor' => '#d97706',
                    'backgroundColor' => 'rgba(217, 119, 6, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
