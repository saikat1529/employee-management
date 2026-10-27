<?php

namespace App\Filament\Pages;

use App\Models\PayrollSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class SalaryRulesPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    // Shown right after Calendar (sort 4) as a flat item, with no group.
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Salary Rules';

    protected static ?string $slug = 'salary-rules';

    protected string $view = 'filament.pages.salary-rules';

    public int $daysInMonth = 30;

    public int $lateDaysPerAbsent = 3;

    public float $halfDayFraction = 0.5;

    public function mount(): void
    {
        $rules = PayrollSetting::rules();

        $this->daysInMonth = $rules['days_in_month'];
        $this->lateDaysPerAbsent = $rules['late_days_per_absent'];
        $this->halfDayFraction = $rules['half_day_fraction'];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Salary Rules';
    }

    public function save(): void
    {
        $this->validate([
            'daysInMonth' => ['required', 'integer', 'min:1', 'max:31'],
            'lateDaysPerAbsent' => ['required', 'integer', 'min:1', 'max:31'],
            'halfDayFraction' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        $values = [
            'days_in_month' => $this->daysInMonth,
            'late_days_per_absent' => $this->lateDaysPerAbsent,
            'half_day_fraction' => $this->halfDayFraction,
        ];

        foreach ($values as $key => $value) {
            PayrollSetting::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        Notification::make()
            ->title('Salary rules saved')
            ->success()
            ->send();
    }
}
