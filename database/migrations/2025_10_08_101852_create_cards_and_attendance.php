<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Cards optionally tied to a school year (supports yearly reissue)
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('uid'); // physical UID
            $table->foreignId('school_year_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['uid','school_year_id']);  // same card UID not reused in same SY
            $table->index(['user_id','is_active']);
        });

        // Student attendance: OK as-is (student & schedule are independent determinants)
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('section_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->date('class_date');
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
            $table->enum('status', ['present','late','absent','excused'])->nullable();
            $table->timestamps();

            $table->unique(['student_id','section_schedule_id','class_date'], 'uniq_student_day_sched');
            $table->index(['class_date','status']);
        });

        // Faculty attendance: faculty is derivable from section_schedules → faculty_assigned_subjects
        Schema::create('faculty_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->date('class_date');
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
            $table->enum('status', ['in','out'])->nullable();
            $table->timestamps();

            $table->unique(['section_schedule_id','class_date'], 'uniq_faculty_day_sched_derived');
            $table->index(['class_date','status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('faculty_attendances');
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('cards');
    }
};
