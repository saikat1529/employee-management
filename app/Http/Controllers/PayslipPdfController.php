<?php

namespace App\Http\Controllers;

use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PayslipPdfController extends Controller
{
    // Reads the stored payroll item, so the PDF shows the same numbers as the table.
    public function __invoke(int $run, int $item): Response
    {
        $payrollItem = PayrollItem::query()
            ->with(['run', 'employee.department'])
            ->where('run_id', $run)
            ->findOrFail($item);

        $fileName = 'payslip-'.$payrollItem->employee->employee_code.'-'.$payrollItem->run->month.'.pdf';

        return Pdf::loadView('pdf.payslip', ['item' => $payrollItem])
            ->setPaper('a4')
            ->download($fileName);
    }
}
