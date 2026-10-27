<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the text "department" column on employees with a link to the departments table.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('phone')
                ->constrained('departments')
                ->restrictOnDelete();
        });

        $now = now();

        // One department row for each distinct text value, then link employees by that text.
        $textValues = DB::table('employees')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');

        foreach ($textValues as $rawValue) {
            $name = trim((string) $rawValue);

            if ($name === '') {
                continue;
            }

            $departmentId = DB::table('departments')->where('name', $name)->value('id');

            if ($departmentId === null) {
                $departmentId = DB::table('departments')->insertGetId([
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('employees')
                ->where('department', $rawValue)
                ->update(['department_id' => $departmentId]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }

    /**
     * Put the text "department" column back and copy the department names into it.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('department')->nullable()->after('phone');
        });

        foreach (DB::table('departments')->get() as $department) {
            DB::table('employees')
                ->where('department_id', $department->id)
                ->update(['department' => $department->name]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
