@php
    $cell = 'padding: 8px 10px; border-bottom: 1px solid rgba(128, 128, 128, 0.25); white-space: nowrap;';
    $head = 'padding: 8px 10px; text-align: left; font-size: 0.75rem; font-weight: 600; opacity: 0.7; border-bottom: 1px solid rgba(128, 128, 128, 0.4); white-space: nowrap;';
    $num = 'text-align: right;';
@endphp

<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <x-filament-panels::header :heading="$this->getTitle()" :actions="$this->getCachedHeaderActions()" />

    <x-filament::section>
        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <label for="payroll-month" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">Month</label>
                <x-filament::input.wrapper wire:target="month">
                    <x-filament::input id="payroll-month" type="month" wire:model.live="month" />
                </x-filament::input.wrapper>
            </div>

            <div style="font-size: 0.9rem;">
                @if ($run)
                    Status:
                    <x-filament::badge :color="$run->status->getColor()">{{ $run->status->getLabel() }}</x-filament::badge>
                    @if ($run->status === \App\Enums\PayrollStatus::Paid && $run->paid_at)
                        <span style="opacity: 0.7; margin-left: 0.5rem;">Paid on {{ $run->paid_at->format('d M Y, H:i') }}</span>
                    @endif
                @else
                    <span style="opacity: 0.7;">No payroll for {{ $monthLabel }} yet. Press "Generate payroll".</span>
                @endif
            </div>
        </div>

        <div style="margin-top: 1rem; font-size: 0.8rem; opacity: 0.75; line-height: 1.5;">
            <div><strong>Rules:</strong> Daily rate = monthly salary / 30. Present and On Leave: no deduction. Late: no deduction, but every 3 Late days = 1 Absent day.
                Absent: 1 day. Half Day: 0.5 day. Net pay is never below ৳ 0.00.</div>
            <div><strong>Days with no attendance record count as nothing (no deduction).</strong></div>
            <div>Only ACTIVE employees are included. The rules can be changed on the Salary Rules page.</div>
        </div>
    </x-filament::section>

    <x-filament::section>
        @if ($run === null)
            <p style="opacity: 0.7;">Nothing to show yet.</p>
        @elseif ($items->isEmpty())
            <p style="opacity: 0.7;">No active employees were found for this month.</p>
        @else
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; max-width: 100%;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th style="{{ $head }}">Employee ID</th>
                            <th style="{{ $head }}">Name</th>
                            <th style="{{ $head }} {{ $num }}">Monthly salary</th>
                            <th style="{{ $head }} {{ $num }}">Present</th>
                            <th style="{{ $head }} {{ $num }}">Late</th>
                            <th style="{{ $head }} {{ $num }}">Half Day</th>
                            <th style="{{ $head }} {{ $num }}">Absent</th>
                            <th style="{{ $head }} {{ $num }}">On Leave</th>
                            <th style="{{ $head }} {{ $num }}">Deduction</th>
                            <th style="{{ $head }} {{ $num }}">Net pay</th>
                            <th style="{{ $head }}"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td style="{{ $cell }}">{{ $item->employee->employee_code }}</td>
                                <td style="{{ $cell }}">{{ $item->employee->full_name }}</td>
                                <td style="{{ $cell }} {{ $num }}">{{ \App\Support\Money::bdt($item->monthly_salary) }}</td>
                                <td style="{{ $cell }} {{ $num }}">{{ $item->present_days }}</td>
                                <td style="{{ $cell }} {{ $num }}">{{ $item->late_days }}</td>
                                <td style="{{ $cell }} {{ $num }}">{{ $item->half_day_days }}</td>
                                <td style="{{ $cell }} {{ $num }}">{{ $item->absent_days }}</td>
                                <td style="{{ $cell }} {{ $num }}">{{ $item->leave_days }}</td>
                                <td style="{{ $cell }} {{ $num }}">{{ \App\Support\Money::bdt($item->deduction) }}</td>
                                <td style="{{ $cell }} {{ $num }} font-weight: 600;">{{ \App\Support\Money::bdt($item->net_pay) }}</td>
                                <td style="{{ $cell }}">
                                    <a href="{{ \App\Filament\Pages\PayslipPage::getUrl(['run' => $run->id, 'item' => $item->id]) }}" style="color: #d97706; font-weight: 600;">Payslip</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: 700;">
                            <td style="{{ $cell }}" colspan="2">Total ({{ $items->count() }} employees)</td>
                            <td style="{{ $cell }} {{ $num }}">{{ \App\Support\Money::bdt($totals['monthly_salary']) }}</td>
                            <td style="{{ $cell }} {{ $num }}">{{ $totals['present_days'] }}</td>
                            <td style="{{ $cell }} {{ $num }}">{{ $totals['late_days'] }}</td>
                            <td style="{{ $cell }} {{ $num }}">{{ $totals['half_day_days'] }}</td>
                            <td style="{{ $cell }} {{ $num }}">{{ $totals['absent_days'] }}</td>
                            <td style="{{ $cell }} {{ $num }}">{{ $totals['leave_days'] }}</td>
                            <td style="{{ $cell }} {{ $num }}">{{ \App\Support\Money::bdt($totals['deduction']) }}</td>
                            <td style="{{ $cell }} {{ $num }}">{{ \App\Support\Money::bdt($totals['net_pay']) }}</td>
                            <td style="{{ $cell }}"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </x-filament::section>

    {{-- Confirmation pop-up for "Mark as paid". Custom views skip Filament's page wrapper, which normally adds this. --}}
    <x-filament-actions::modals />
</div>
