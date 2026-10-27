<?php

namespace App\Filament\Pages;

use App\Http\Controllers\PayslipPdfController;
use App\Models\PayrollItem;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Pages\PageConfiguration;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Route;

class PayslipPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    // Opened from the "Payslip" link on the Payroll page. Not shown in the sidebar.
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'payroll/{run}/employees/{item}';

    protected string $view = 'filament.pages.payslip';

    public int $runId;

    public int $itemId;

    public function mount(int|string $run, int|string $item): void
    {
        $this->runId = (int) $run;
        $this->itemId = (int) $item;

        // Fails with 404 when the item does not belong to the run.
        $this->payrollItem();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Payslip: '.$this->payrollItem()->employee->full_name;
    }

    // Used by the payslip view and the PDF route name below.
    public static function routes(Panel $panel, ?PageConfiguration $configuration = null): void
    {
        parent::routes($panel, $configuration);

        Route::get('payroll/{run}/employees/{item}/pdf', PayslipPdfController::class)
            ->name('payroll.payslip.pdf');
    }

    public function pdfUrl(): string
    {
        return route('filament.admin.pages.payroll.payslip.pdf', ['run' => $this->runId, 'item' => $this->itemId]);
    }

    protected function payrollItem(): PayrollItem
    {
        return PayrollItem::query()
            ->with(['run', 'employee.department'])
            ->where('run_id', $this->runId)
            ->findOrFail($this->itemId);
    }

    protected function getViewData(): array
    {
        return ['item' => $this->payrollItem()];
    }
}
