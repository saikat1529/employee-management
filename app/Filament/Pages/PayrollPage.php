<?php

namespace App\Filament\Pages;

use App\Enums\PayrollStatus;
use App\Models\PayrollRun;
use App\Services\PayrollService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use InvalidArgumentException;
use RuntimeException;

class PayrollPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    // Shown right after Salary Rules (sort 5) as a flat item, with no group.
    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?string $slug = 'payroll';

    protected string $view = 'filament.pages.payroll';

    // Month shown, as Y-m (for example 2026-10). Set to the current month on load.
    public string $month = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Payroll';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate payroll')
                ->icon(Heroicon::OutlinedArrowPath)
                // A paid run is locked and cannot be recalculated.
                ->disabled(fn () => $this->currentRun()?->status === PayrollStatus::Paid)
                ->action(fn () => $this->generate()),
            Action::make('markAsPaid')
                ->label('Mark as paid')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn () => $this->currentRun()?->status === PayrollStatus::Draft)
                ->requiresConfirmation()
                ->modalHeading('Mark this payroll as paid?')
                ->modalDescription('The run is locked after this. Nothing is deleted, but the numbers can no longer be recalculated.')
                ->modalSubmitActionLabel('Mark as paid')
                ->action(fn () => $this->markAsPaid()),
        ];
    }

    public function generate(): void
    {
        try {
            $run = app(PayrollService::class)->generate($this->month);
        } catch (InvalidArgumentException|RuntimeException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Payroll generated for '.$run->monthLabel())
            ->body($run->items()->count().' active employees calculated.')
            ->send();
    }

    public function markAsPaid(): void
    {
        $run = $this->currentRun();

        if ($run === null) {
            Notification::make()->danger()->title('Generate the payroll first.')->send();

            return;
        }

        try {
            app(PayrollService::class)->markAsPaid($run);
        } catch (RuntimeException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();

            return;
        }

        Notification::make()->success()->title('Payroll marked as paid')->send();
    }

    protected function currentRun(): ?PayrollRun
    {
        return PayrollRun::query()->where('month', $this->month)->first();
    }

    protected function getViewData(): array
    {
        $run = $this->currentRun();
        $items = $run
            ? $run->items()->with('employee')->get()->sortBy('employee.employee_code')->values()
            : collect();

        $sum = fn (string $field) => round((float) $items->sum($field), 2);

        $totals = [
            'monthly_salary' => $sum('monthly_salary'),
            'present_days' => (int) $items->sum('present_days'),
            'late_days' => (int) $items->sum('late_days'),
            'half_day_days' => (int) $items->sum('half_day_days'),
            'absent_days' => (int) $items->sum('absent_days'),
            'leave_days' => (int) $items->sum('leave_days'),
            'deduction' => $sum('deduction'),
            'net_pay' => $sum('net_pay'),
        ];

        return [
            'monthLabel' => CarbonImmutable::createFromFormat('!Y-m', $this->month)->format('F Y'),
            'run' => $run,
            'items' => $items,
            'totals' => $totals,
        ];
    }
}
