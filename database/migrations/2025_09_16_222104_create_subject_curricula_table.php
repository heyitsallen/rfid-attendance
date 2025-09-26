<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
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
    }
};
