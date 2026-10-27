<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/*
 * One-time demo data.
 *
 * This is a data migration: Laravel records it in the `migrations` table, so it runs once per
 * database. As an extra guard, a `demo_data_loaded` row in `payroll_settings` marks that the
 * demo data was inserted. Every insert also checks that the record is missing first, so existing
 * records (and any status or edit an admin has made) are never overwritten.
 *
 * No payroll run is created here.
 */
return new class extends Migration
{
    private const MARKER_KEY = 'demo_data_loaded';

    public function up(): void
    {
        if (DB::table('payroll_settings')->where('key', self::MARKER_KEY)->exists()) {
            return;
        }

        $now = now();

        $this->insertAdminUser($now);
        $departmentIds = $this->insertDepartments($now);
        $employeeIds = $this->insertEmployees($now, $departmentIds);
        $this->insertAttendance($now, $employeeIds);
        $this->insertPayrollSettings($now);

        DB::table('payroll_settings')->insert([
            'key' => self::MARKER_KEY,
            'value' => $now->toDateTimeString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Demo data is left in place on rollback, so an admin's data is never deleted by accident.
     */
    public function down(): void
    {
        //
    }

    private function insertAdminUser(Carbon $now): void
    {
        if (DB::table('users')->where('email', 'admin@demo.com')->exists()) {
            return;
        }

        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @return array<string, int> department id by name */
    private function insertDepartments(Carbon $now): array
    {
        foreach (['Finance', 'Engineering', 'Human Resources', 'Sales', 'Operations'] as $name) {
            if (! DB::table('departments')->where('name', $name)->exists()) {
                DB::table('departments')->insert([
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return DB::table('departments')->pluck('id', 'name')->all();
    }

    /** @return array<string, int> employee id by email */
    private function insertEmployees(Carbon $now, array $departmentIds): array
    {
        $employees = [
            ['Amina Rahman', 'amina.rahman@demo.com', '+880 1711 100001', 'Finance', 'Accountant', '2024-03-01', 'active', 45000],
            ['Tariq Hassan', 'tariq.hassan@demo.com', '+880 1711 100002', 'Engineering', 'Software Engineer', '2023-07-15', 'active', 60000],
            ['Lena Novak', 'lena.novak@demo.com', null, 'Human Resources', 'HR Coordinator', '2023-11-20', 'suspended', 35000],
            ['Nusrat Jahan', 'nusrat.jahan@demo.com', '+880 1711 100004', 'Finance', 'Finance Manager', '2023-02-01', 'active', 85000],
            ['Tanvir Ahmed', 'tanvir.ahmed@demo.com', '+880 1711 100005', 'Engineering', 'Senior Software Engineer', '2023-03-10', 'active', 88000],
            ['Sadia Akter', 'sadia.akter@demo.com', '+880 1711 100006', 'Engineering', 'QA Engineer', '2024-01-08', 'active', 58000],
            ['Imran Hossain', 'imran.hossain@demo.com', '+880 1711 100007', 'Engineering', 'Junior Developer', '2025-02-03', 'active', 42000],
            ['Farhana Yasmin', 'farhana.yasmin@demo.com', '+880 1711 100008', 'Human Resources', 'HR Manager', '2023-04-18', 'active', 75000],
            ['Mahmudul Hasan', 'mahmudul.hasan@demo.com', '+880 1711 100009', 'Human Resources', 'HR Assistant', '2025-01-06', 'suspended', 38000],
            ['Shahriar Kabir', 'shahriar.kabir@demo.com', '+880 1711 100010', 'Sales', 'Sales Manager', '2023-08-01', 'active', 78000],
            ['Tasnim Chowdhury', 'tasnim.chowdhury@demo.com', '+880 1711 100011', 'Sales', 'Sales Executive', '2024-05-20', 'active', 40000],
            ['Mithila Sultana', 'mithila.sultana@demo.com', '+880 1711 100012', 'Sales', 'Sales Executive', '2025-03-03', 'active', 28000],
            ['Arif Mahmud', 'arif.mahmud@demo.com', '+880 1711 100013', 'Operations', 'Operations Manager', '2023-10-09', 'active', 70000],
            ['Rumana Parvin', 'rumana.parvin@demo.com', '+880 1711 100014', 'Operations', 'Office Administrator', '2024-02-14', 'active', 33000],
            ['Kamrul Hasan', 'kamrul.hasan@demo.com', '+880 1711 100015', 'Operations', 'Logistics Officer', '2025-06-01', 'suspended', 30000],
        ];

        foreach ($employees as [$name,$email, $phone, $department, $designation, $joined, $status, $salary]) {
            if (DB::table('employees')->where('email', $email)->exists()) {
                continue;
            }

            // Same code style as the Employee model: EMP-0001, EMP-0002, ...
            $next = (int) DB::table('employees')->max('id') + 1;

            DB::table('employees')->insert([
                'employee_code' => 'EMP-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT),
                'full_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'department_id' => $departmentIds[$department],
                'designation' => $designation,
                'joining_date' => $joined,
                'status' => $status,
                'monthly_salary' => $salary,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return DB::table('employees')->pluck('id', 'email')->all();
    }

    /**
     * Attendance for the last 30 days (Saturdays and Sundays left out), for active employees only.
     * The status is picked from a fixed hash of email + date, so the same demo data is always made.
     */
    private function insertAttendance(Carbon $now, array $employeeIds): void
    {
        $activeEmails = DB::table('employees')->where('status', 'active')->pluck('email');
        $leaveTypes = ['casual', 'sick', 'annual'];
        $rows = [];

        foreach ($activeEmails as $email) {
            for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
                $date = Carbon::today()->subDays($daysAgo);

                if ($date->isWeekend()) {
                    continue;
                }

                $seed = $email.'|'.$date->toDateString();
                $roll = crc32($seed) % 100;

                $row = [
                    'employee_id' => $employeeIds[$email],
                    'attendance_date' => $date->toDateString(),
                    'leave_type' => null,
                    'in_time' => null,
                    'out_time' => null,
                ];

                if ($roll < 65) {
                    // Present: in 08:30 to 09:15, out 17:00 to 18:00
                    $row['status'] = 'present';
                    $row['in_time'] = $this->clock(8, 30, crc32($seed.'in') % 46);
                    $row['out_time'] = $this->clock(17, 0, crc32($seed.'out') % 61);
                } elseif ($roll < 77) {
                    // Late: in 09:31 to 10:10, out 17:30 to 18:30
                    $row['status'] = 'late';
                    $row['in_time'] = $this->clock(9, 31, crc32($seed.'in') % 40);
                    $row['out_time'] = $this->clock(17, 30, crc32($seed.'out') % 61);
                } elseif ($roll < 82) {
                    // Half day: in 09:00 to 09:30, out 13:00 to 13:30
                    $row['status'] = 'half_day';
                    $row['in_time'] = $this->clock(9, 0, crc32($seed.'in') % 31);
                    $row['out_time'] = $this->clock(13, 0, crc32($seed.'out') % 31);
                } elseif ($roll < 90) {
                    // Absent: no times
                    $row['status'] = 'absent';
                } else {
                    // On leave: no times, with a leave type
                    $row['status'] = 'on_leave';
                    $row['leave_type'] = $leaveTypes[crc32($seed.'leave') % 3];
                }

                $row['working_hours'] = $this->workingHours($row['in_time'], $row['out_time']);
                $row['created_at'] = $now;
                $row['updated_at'] = $now;

                $rows[] = $row;
            }
        }

        // insertOrIgnore respects the unique (employee_id, attendance_date) rule.
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('attendances')->insertOrIgnore($chunk);
        }
    }

    private function insertPayrollSettings(Carbon $now): void
    {
        $defaults = [
            'days_in_month' => '30',
            'late_days_per_absent' => '3',
            'half_day_fraction' => '0.5',
        ];

        foreach ($defaults as $key => $value) {
            if (! DB::table('payroll_settings')->where('key', $key)->exists()) {
                DB::table('payroll_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function clock(int $hour, int $minute, int $addMinutes): string
    {
        $total = $hour * 60 + $minute + $addMinutes;

        return sprintf('%02d:%02d:00', intdiv($total, 60), $total % 60);
    }

    private function workingHours(?string $in, ?string $out): float
    {
        if ($in === null || $out === null) {
            return 0.0;
        }

        [$inH, $inM] = array_map('intval', explode(':', $in));
        [$outH, $outM] = array_map('intval', explode(':', $out));

        return round((($outH * 60 + $outM) - ($inH * 60 + $inM)) / 60, 2);
    }
};
