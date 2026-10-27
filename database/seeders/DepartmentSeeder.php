<?php

namespace Database\Seeders;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate keeps re-runs safe and does not reactivate a department an admin has suspended.
        foreach (['Finance', 'Engineering', 'Human Resources'] as $name) {
            Department::firstOrCreate(
                ['name' => $name],
                ['status' => DepartmentStatus::Active],
            );
        }
    }
}
