<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('attendance_date');
            $table->string('status');
            $table->string('leave_type')->nullable();
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->decimal('working_hours', 5, 2)->default(0);
            $table->timestamps();

            // One row per employee per date.
            $table->unique(['employee_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
