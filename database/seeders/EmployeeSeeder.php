<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Three demo employees for local tests. The demo data itself is inserted by the
        // migration database/migrations/2026_10_28_000000_insert_demo_data.php.
        $employees = [
            ['full_name' => 'Amina Rahman', 'email' => 'amina.rahman@demo.com', 'phone' => '+880 1711 100001', 'department' => 'Finance', 'designation' => 'Accountant', 'joining_date' => '2024-03-01', 'status' => 'active', 'monthly_salary' => 45000],
            ['full_name' => 'Tariq Hassan', 'email' => 'tariq.hassan@demo.com', 'phone' => '+880 1711 100002', 'department' => 'Engineering', 'designation' => 'Software Engineer', 'joining_date' => '2023-07-15', 'status' => 'active', 'monthly_salary' => 60000],
            ['full_name' => 'Lena Novak', 'email' => 'lena.novak@demo.com', 'phone' => null, 'department' => 'Human Resources', 'designation' => 'HR Coordinator', 'joining_date' => '2023-11-20', 'status' => 'suspended', 'monthly_salary' => 35000],
        ];

        foreach ($employees as $employee) {
            // Insert only. An existing employee is never changed, so a status set by an admin
            // (for example reactivating Lena Novak) survives every run.
            if (Employee::where('email', $employee['email'])->exists()) {
                continue;
            }

            // Link to the department by name. DepartmentSeeder must run first.
            $departmentName = $employee['department'];
            unset($employee['department']);
            $employee['department_id'] = Department::where('name', $departmentName)->value('id');

            Employee::create($employee);
        }
    }
}
