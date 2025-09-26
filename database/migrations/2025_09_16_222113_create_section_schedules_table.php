<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('section_schedules', function (Blueprint $table) {
            $table->id();

            // Core relations
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('faculty_assigned_subject_id')->constrained()->cascadeOnDelete();

            // Optional room binding (lets you validate scans per device/room)
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();

            // Time window
            $table->enum('day', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();

            // 🔒 Uniqueness: prevent duplicate overlapping entries of the exact same slot in the same room
            $table->unique(
                ['section_id','faculty_id','faculty_assigned_subject_id','room_id','day','start_time','end_time'],
                'uniq_section_fac_sub_room_slot'
            );

            // ⚡ Helpful indexes for lookups (current class checks, per-room/day queries, etc.)
            $table->index(['day','start_time','end_time'], 'idx_day_window');
            $table->index(['faculty_id','day'], 'idx_faculty_day');
            $table->index(['room_id','day'], 'idx_room_day');
        });

        // Optional (MySQL 8+/Postgres): enforce end_time > start_time at DB level.
        // Uncomment if your DB supports CHECK constraints.
        // DB::statement('ALTER TABLE section_schedules ADD CONSTRAINT chk_time_order CHECK (end_time > start_time)');
    }

    public function down(): void {
        // If you added the CHECK constraint above, drop it first for engines that require it.
        // DB::statement('ALTER TABLE section_schedules DROP CONSTRAINT chk_time_order');

        Schema::dropIfExists('section_schedules');
    }
};
