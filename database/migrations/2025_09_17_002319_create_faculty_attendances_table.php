<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('faculty_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('section_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->date('class_date');
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
            $table->enum('status', ['in','out'])->nullable();
            $table->timestamps();

            $table->unique(['faculty_id','section_schedule_id','class_date'], 'uniq_faculty_day_sched');
            $table->index(['class_date','status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('faculty_attendances');
    }
};
