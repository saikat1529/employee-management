<?php

namespace App\Filament\Pages;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class CalendarPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    // Shown right after Attendance (sort 3) as a flat item, with no group.
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Calendar';

    protected static ?string $slug = 'calendar';

    protected string $view = 'filament.pages.calendar';

    // Empty string means "All employees". Otherwise the employee id.
    public string $employeeId = '';

    // Month shown, as Y-m (for example 2026-10). Set to the current month on load.
    public string $month = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function getTitle(): string|Htmlable
    {
        $employee = $this->selectedEmployee();

        if ($employee === null) {
            return 'Calendar: All employees';
        }

        return 'Calendar: '.$employee->full_name.' ('.$employee->employee_code.')';
    }

    // Filament's own header sits outside the part of the page Livewire redraws after a change,
    // so the heading is drawn inside calendar.blade.php instead (it uses getTitle()).
    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    // Keeps the browser tab title in step with the chosen employee.
    public function updatedEmployeeId(): void
    {
        $this->js('document.title = '.json_encode($this->getTitle().' - '.strip_tags((string) filament()->getBrandName())).';');
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonth()->format('Y-m');
    }

    /**
     * Monday-to-Sunday grid for the month. Cells before and after the month are returned
     * with inMonth = false and no counts, so the page can show them faded.
     */
    protected function getViewData(): array
    {
        $monthStart = $this->monthStart();
        $monthEnd = $monthStart->endOfMonth();
        $employee = $this->selectedEmployee();

        // Only rows inside the selected month are loaded.
        $rows = Attendance::query()
            ->when($employee, fn ($query) => $query->where('employee_id', $employee->id))
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['attendance_date', 'status', 'in_time', 'out_time']);

        $rowsByDay = [];

        foreach ($rows as $row) {
            $rowsByDay[$row->attendance_date->format('Y-m-d')][] = $row;
        }

        $weeks = [];
        $day = $monthStart->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $monthEnd->endOfWeek(CarbonInterface::SUNDAY);

        while ($day <= $gridEnd) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $key = $day->format('Y-m-d');
                $inMonth = $day->month === $monthStart->month;
                $dayRows = $inMonth ? ($rowsByDay[$key] ?? []) : [];

                $week[] = [
                    'day' => $day->day,
                    'inMonth' => $inMonth,
                    // Single employee: one entry per record, with its time text and colour.
                    'entries' => $employee === null ? [] : array_map($this->singleEntry(...), $dayRows),
                    // All employees: counts shown as badges. Only counts above zero are displayed.
                    'badges' => $employee === null ? $this->allBadges($dayRows) : [],
                ];

                $day = $day->addDay();
            }

            $weeks[] = $week;
        }

        return [
            'monthLabel' => $monthStart->format('F Y'),
            'weeks' => $weeks,
            'employeeOptions' => $this->employeeOptions(),
            'isSingle' => $employee !== null,
        ];
    }

    /**
     * One record for a single employee: its text and colour tone.
     * Tones: 'green' (Present), 'red' (Late), 'ash' (On Leave, Half Day), '' (Absent: no colour).
     *
     * @return array{text: string, tone: string}
     */
    private function singleEntry(Attendance $row): array
    {
        $status = $row->status;

        if ($status === AttendanceStatus::Absent) {
            return ['text' => 'Absent', 'tone' => ''];
        }

        $tone = match ($status) {
            AttendanceStatus::Present => 'green',
            AttendanceStatus::Late => 'red',
            default => 'ash',
        };

        $in = $this->formatTime($row->in_time);
        $out = $this->formatTime($row->out_time);

        $text = match (true) {
            $in !== null && $out !== null => $in.' – '.$out,
            $in !== null => 'In '.$in,
            $out !== null => 'Out '.$out,
            default => '–',
        };

        return ['text' => $text, 'tone' => $tone];
    }

    /**
     * Badges for all employees on one day. Each badge is shown only when its count is above zero.
     *
     * @param  array<int, Attendance>  $dayRows
     * @return array<int, array{label: string, tone: string}>
     */
    private function allBadges(array $dayRows): array
    {
        $attendance = 0;
        $late = 0;
        $other = 0;

        foreach ($dayRows as $row) {
            match ($row->status) {
                AttendanceStatus::Present => $attendance++,
                AttendanceStatus::Late => $late++,
                default => $other++,
            };
        }

        $badges = [];

        if ($attendance > 0) {
            $badges[] = ['label' => 'Attendance: '.$attendance, 'tone' => 'green'];
        }

        if ($late > 0) {
            $badges[] = ['label' => 'Late: '.$late, 'tone' => 'red'];
        }

        if ($other > 0) {
            $badges[] = ['label' => 'Other: '.$other, 'tone' => 'ash'];
        }

        return $badges;
    }

    // Shows a stored time as HH:MM (for example 09:00). Returns null when there is no time.
    private function formatTime(?string $time): ?string
    {
        if (blank($time) || ! preg_match('/(\d{1,2}):(\d{2})/', (string) $time, $matches)) {
            return null;
        }

        return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
    }

    /** @return array<string, string> */
    private function employeeOptions(): array
    {
        $options = ['' => 'All employees'];

        // Every employee is listed, suspended ones included, sorted by name.
        foreach (Employee::query()->orderBy('full_name')->get(['id', 'full_name', 'employee_code']) as $employee) {
            $options[(string) $employee->id] = $employee->full_name.' ('.$employee->employee_code.')';
        }

        return $options;
    }

    private function selectedEmployee(): ?Employee
    {
        if ($this->employeeId === '') {
            return null;
        }

        return Employee::query()->find((int) $this->employeeId);
    }

    private function monthStart(): CarbonImmutable
    {
        $parsed = CarbonImmutable::createFromFormat('!Y-m', $this->month);

        return ($parsed ?: CarbonImmutable::now())->startOfMonth();
    }
}
