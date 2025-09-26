<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('uid')->unique(); // RFID tag UID
            $table->boolean('is_active')->default(true);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id','is_active']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('cards');
    }
};
