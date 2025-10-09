<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Faculty ↔ Subject ↔ SY/Sem
        Schema::create('faculty_assigned_subjects', function (Blueprint $table) {
            $table->id();
            // Keep this pointing to users (with a faculty role/profile)
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_curriculum_id')->constrained('subject_curricula')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

$table->unique(
    ['faculty_id','subject_curriculum_id','school_year_id','semester_id'],
    'uniq_fac_sub_sy_sem'
);

        $table->index(['faculty_id','school_year_id','semester_id'], 'idx_fac_sy_sem');
        });

        // Class schedule for a Section, linked only to the assignment (faculty is derivable)
        Schema::create('section_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_assigned_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();

            $table->enum('day', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();

$table->unique(
    ['section_id','faculty_assigned_subject_id','room_id','day','start_time','end_time'],
    'uniq_section_assignment_room_slot'
);
$table->index(['day','start_time','end_time'], 'idx_day_window');
$table->index(['room_id','day'], 'idx_room_day');
        });

        // Student enrollment to a Section for a specific term
        Schema::create('section_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->date('date_enrolled')->nullable();
            $table->timestamps();

$table->unique(['student_id','section_id','school_year_id','semester_id'], 'uniq_student_section_term');
$table->index(['section_id','school_year_id','semester_id'], 'idx_section_term');
        });
    }

    public function down(): void {
        Schema::dropIfExists('section_enrollments');
        Schema::dropIfExists('section_schedules');
        Schema::dropIfExists('faculty_assigned_subjects');
    }
};
