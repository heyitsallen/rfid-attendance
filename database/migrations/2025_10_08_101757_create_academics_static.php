<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->unique();
            $table->string('token', 64)->unique();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('room_id');
        });

        Schema::create('year_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Grade 11, 1st Year
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('year_level_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 2025-2026
            $table->date('date_start');
            $table->date('date_end');
            $table->timestamps();
        });

        // Normalize semesters under a school year (no dangling semester semantics)
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "1st", "2nd", "Summer"
            $table->timestamps();

            $table->unique(['school_year_id','name'], 'uniq_sy_sem_name');
        });

        Schema::create('subject_curricula', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->integer('unit');
            $table->integer('lecture');
            $table->integer('laboratory');
            $table->string('course_code')->unique();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('subject_curricula');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('school_years');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('year_levels');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('rooms');
    }
};
