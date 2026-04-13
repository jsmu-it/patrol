<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psikotest_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('participant_name'); // Nama peserta (bisa non-user)
            $table->string('participant_email')->nullable();
            $table->string('participant_phone')->nullable();
            $table->enum('test_type', ['kraepelin', 'papikostik']);
            $table->foreignId('question_set_id'); // ID dari kraepelin_questions atau papikostik_questions
            $table->enum('status', ['pending', 'in_progress', 'completed', 'expired'])->default('pending');
            $table->string('access_token')->unique(); // Token untuk akses test
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['test_type', 'status']);
            $table->index('access_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psikotest_sessions');
    }
};
