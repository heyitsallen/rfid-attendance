<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('faculty_assigned_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_curriculum_id')->constrained('subject_curricula')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['faculty_id','subject_curriculum_id','school_year_id','semester_id'], 'uniq_fac_sub_sy_sem');
        });
    }

    public function down(): void {
        Schema::dropIfExists('faculty_assigned_subjects');
    }
};
