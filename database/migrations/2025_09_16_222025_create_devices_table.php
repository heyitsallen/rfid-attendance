<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->unique(); // hardware serial
            $table->string('token', 64)->unique();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('room_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('devices');
    }
};
