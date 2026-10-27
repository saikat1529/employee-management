<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveType;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Fixed dates, so re-runs do not add new rows. EmployeeSeeder must run first.
        $rows = [
            ['email' => 'amina.rahman@demo.com', 'date' => '2026-10-22', 'status' => AttendanceStatus::Present, 'in' => '09:00', 'out' => '17:30'],
            ['email' => 'amina.rahman@demo.com', 'date' => '2026-10-24', 'status' => AttendanceStatus::Late, 'in' => '09:45', 'out' => '17:30'],
            ['email' => 'tariq.hassan@demo.com', 'date' => '2026-10-22', 'status' => AttendanceStatus::OnLeave, 'leave' => LeaveType::Sick],
            ['email' => 'tariq.hassan@demo.com', 'date' => '2026-10-24', 'status' => AttendanceStatus::Present, 'in' => '08:50', 'out' => '17:00'],
            ['email' => 'lena.novak@demo.com', 'date' => '2026-10-22', 'status' => AttendanceStatus::HalfDay, 'in' => '09:00', 'out' => '13:00'],
            ['email' => 'lena.novak@demo.com', 'date' => '2026-10-24', 'status' => AttendanceStatus::Absent],
        ];

        foreach ($rows as $row) {
            $employeeId = Employee::where('email', $row['email'])->value('id');

            if ($employeeId === null) {
                continue;
            }

            // firstOrCreate: a re-run neither duplicates a row nor overwrites one an admin has changed.
            Attendance::firstOrCreate(
                ['employee_id' => $employeeId, 'attendance_date' => $row['date']],
                [
                    'status' => $row['status'],
                    'leave_type' => $row['leave'] ?? null,
                    'in_time' => $row['in'] ?? null,
                    'out_time' => $row['out'] ?? null,
                ],
            );
        }
    }
}
