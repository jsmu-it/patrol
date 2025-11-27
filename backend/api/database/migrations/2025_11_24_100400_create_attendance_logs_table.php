<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['clock_in', 'clock_out']);
            $table->dateTimeTz('occurred_at');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('selfie_photo_path')->nullable();
            $table->text('note')->nullable();

            $table->enum('mode', ['normal', 'dinas'])->default('normal');
            $table->enum('status_dinas', ['pending', 'approved', 'rejected'])->nullable();

            $table->timestamps();

            $table->index(['user_id', 'project_id', 'shift_id', 'occurred_at'], 'attendance_logs_main_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
