<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 2025-2026
            $table->date('date_start');
            $table->date('date_end');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('school_years');
    }
};
