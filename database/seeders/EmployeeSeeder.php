<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Three demo employees. updateOrCreate keeps re-runs safe.
        $employees = [
            ['full_name' => 'Amina Rahman', 'email' => 'amina.rahman@demo.com', 'phone' => '+1 555 0101', 'department' => 'Finance', 'designation' => 'Accountant', 'joining_date' => '2024-03-01', 'status' => 'active'],
            ['full_name' => 'Tariq Hassan', 'email' => 'tariq.hassan@demo.com', 'phone' => '+1 555 0102', 'department' => 'Engineering', 'designation' => 'Software Engineer', 'joining_date' => '2023-07-15', 'status' => 'active'],
            ['full_name' => 'Lena Novak', 'email' => 'lena.novak@demo.com', 'phone' => null, 'department' => 'Human Resources', 'designation' => 'HR Coordinator', 'joining_date' => '2022-11-20', 'status' => 'suspended'],
        ];

        // Demo monthly salaries (BDT). Only applied while the stored value is still 0.
        $salaries = [
            'amina.rahman@demo.com' => 45000,
            'tariq.hassan@demo.com' => 60000,
            'lena.novak@demo.com' => 35000,
        ];

        foreach ($employees as $employee) {
            // Link to the department by name. DepartmentSeeder must run first.
            $departmentName = $employee['department'];
            unset($employee['department']);
            $employee['department_id'] = Department::where('name', $departmentName)->value('id');

            $record = Employee::updateOrCreate(['email' => $employee['email']], $employee);

            // Only while still 0, so an admin's edit survives re-runs.
            Employee::query()
                ->whereKey($record->id)
                ->where('monthly_salary', 0)
                ->update(['monthly_salary' => $salaries[$employee['email']]]);
        }
    }
}
