<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->decimal('monthly_salary', 12, 2)->default(0);
            $table->decimal('daily_rate', 12, 4)->default(0);
            $table->unsignedInteger('present_days')->default(0);
            $table->unsignedInteger('late_days')->default(0);
            $table->unsignedInteger('half_day_days')->default(0);
            $table->unsignedInteger('absent_days')->default(0);
            $table->unsignedInteger('leave_days')->default(0);
            // Late days turned into absent days (every 3 lates = 1 absent, by default).
            $table->unsignedInteger('late_equivalent_days')->default(0);
            // Days deducted for absences (absent + late equivalent).
            $table->decimal('absent_deduction_days', 8, 2)->default(0);
            // Days deducted for half days (half days x fraction).
            $table->decimal('half_day_deduction_days', 8, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
