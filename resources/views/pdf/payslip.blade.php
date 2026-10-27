<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin: 0; }
        .muted { color: #6b7280; }
        .header { border-bottom: 2px solid #d97706; padding-bottom: 8px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        td { padding: 6px 4px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { text-align: left; padding: 6px 4px; border-bottom: 1px solid #9ca3af; background: #f3f4f6; }
        .right { text-align: right; }
        .total td { font-weight: bold; border-bottom: none; }
        .net { font-size: 16px; font-weight: bold; background: #fef3c7; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Demo Portal</h1>
        <div class="muted">Payslip for {{ $item->run->monthLabel() }}</div>
    </div>

    <table>
        <tr>
            <td style="width: 25%;" class="muted">Employee name</td>
            <td style="width: 25%;"><strong>{{ $item->employee->full_name }}</strong></td>
            <td style="width: 25%;" class="muted">Employee ID</td>
            <td style="width: 25%;"><strong>{{ $item->employee->employee_code }}</strong></td>
        </tr>
        <tr>
            <td class="muted">Department</td>
            <td>{{ $item->employee->department?->name ?? '-' }}</td>
            <td class="muted">Designation</td>
            <td>{{ $item->employee->designation ?? '-' }}</td>
        </tr>
        <tr>
            <td class="muted">Month</td>
            <td>{{ $item->run->monthLabel() }}</td>
            <td class="muted">Monthly salary</td>
            <td>BDT {{ number_format((float) $item->monthly_salary, 2, '.', ',') }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <th>Attendance</th>
            <th class="right">Days</th>
        </tr>
        <tr><td>Present</td><td class="right">{{ $item->present_days }}</td></tr>
        <tr><td>Late</td><td class="right">{{ $item->late_days }}</td></tr>
        <tr><td>Half Day</td><td class="right">{{ $item->half_day_days }}</td></tr>
        <tr><td>Absent</td><td class="right">{{ $item->absent_days }}</td></tr>
        <tr><td>On Leave</td><td class="right">{{ $item->leave_days }}</td></tr>
    </table>

    <table>
        <tr>
            <th>Deduction</th>
            <th class="right">Days</th>
            <th class="right">Amount (BDT)</th>
        </tr>
        @foreach ($item->deductionLines() as $line)
            <tr>
                <td>{{ $line['label'] }}</td>
                <td class="right">{{ rtrim(rtrim(number_format($line['days'], 2, '.', ''), '0'), '.') }}</td>
                <td class="right">{{ number_format($line['amount'], 2, '.', ',') }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td>Total deduction</td>
            <td></td>
            <td class="right">{{ number_format((float) $item->deduction, 2, '.', ',') }}</td>
        </tr>
    </table>

    <table>
        <tr class="net">
            <td>Net pay (BDT)</td>
            <td class="right">{{ number_format((float) $item->net_pay, 2, '.', ',') }}</td>
        </tr>
    </table>

    <p class="muted" style="font-size: 10px;">Daily rate: BDT {{ number_format((float) $item->daily_rate, 2, '.', ',') }}. Days with no attendance record count as nothing (no deduction).</p>
</body>
</html>
