<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_schedule_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('section_schedule_id')->constrained('section_schedules')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('reason')->nullable();   // e.g., "IRREG", "MAKE-UP", "PETITION"
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id','section_schedule_id','school_year_id','semester_id'],
                'uniq_student_schedule_term'
            );

            $table->index(['student_id','school_year_id','semester_id'], 'idx_student_term');
            $table->index(['section_schedule_id'], 'idx_schedule');
        });
    }
    public function down(): void {
        Schema::dropIfExists('student_schedule_enrollments');
    }
};

