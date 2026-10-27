@php
    $employee = $item->employee;
    $run = $item->run;
    $row = 'display: flex; justify-content: space-between; gap: 1rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(128, 128, 128, 0.25);';
@endphp

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <x-filament-panels::header :heading="$this->getTitle()" />

    <x-filament::section>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <div style="font-size: 1.25rem; font-weight: 700;">Demo Portal</div>
                <div style="font-size: 0.875rem; opacity: 0.7;">Payslip for {{ $run->monthLabel() }}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem;">
                <span>Status: <x-filament::badge :color="$run->status->getColor()">{{ $run->status->getLabel() }}</x-filament::badge></span>
                <x-filament::button tag="a" :href="$this->pdfUrl()" color="primary">Download PDF</x-filament::button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem 2rem; font-size: 0.9rem;">
            <div><span style="opacity: 0.7;">Employee name</span><br><strong>{{ $employee->full_name }}</strong></div>
            <div><span style="opacity: 0.7;">Employee ID</span><br><strong>{{ $employee->employee_code }}</strong></div>
            <div><span style="opacity: 0.7;">Department</span><br><strong>{{ $employee->department?->name ?? '-' }}</strong></div>
            <div><span style="opacity: 0.7;">Designation</span><br><strong>{{ $employee->designation ?? '-' }}</strong></div>
            <div><span style="opacity: 0.7;">Month</span><br><strong>{{ $run->monthLabel() }}</strong></div>
            <div><span style="opacity: 0.7;">Monthly salary</span><br><strong>{{ \App\Support\Money::bdt($item->monthly_salary) }}</strong></div>
            <div><span style="opacity: 0.7;">Daily rate</span><br><strong>{{ \App\Support\Money::bdt($item->daily_rate) }}</strong></div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Attendance this month">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; font-size: 0.9rem;">
            <div>Present: <strong>{{ $item->present_days }}</strong></div>
            <div>Late: <strong>{{ $item->late_days }}</strong></div>
            <div>Half Day: <strong>{{ $item->half_day_days }}</strong></div>
            <div>Absent: <strong>{{ $item->absent_days }}</strong></div>
            <div>On Leave: <strong>{{ $item->leave_days }}</strong></div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Deductions">
        @foreach ($item->deductionLines() as $line)
            <div style="{{ $row }}">
                <span>{{ $line['label'] }} ({{ rtrim(rtrim(number_format($line['days'], 2, '.', ''), '0'), '.') }} days)</span>
                <span>{{ \App\Support\Money::bdt($line['amount']) }}</span>
            </div>
        @endforeach
        <div style="{{ $row }} font-weight: 700; border-bottom: none;">
            <span>Total deduction</span>
            <span>{{ \App\Support\Money::bdt($item->deduction) }}</span>
        </div>
    </x-filament::section>

    <x-filament::section>
        <div style="display: flex; justify-content: space-between; font-size: 1.125rem; font-weight: 700;">
            <span>Net pay</span>
            <span>{{ \App\Support\Money::bdt($item->net_pay) }}</span>
        </div>
    </x-filament::section>
</div>
