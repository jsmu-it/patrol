<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psikotest_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('psikotest_sessions')->cascadeOnDelete();
            $table->json('raw_answers'); // Jawaban mentah
            $table->json('scores'); // Skor per dimensi/metrik
            $table->json('interpretation')->nullable(); // Interpretasi hasil
            $table->integer('total_correct')->nullable(); // Kraepelin: total benar
            $table->integer('total_wrong')->nullable(); // Kraepelin: total salah
            $table->decimal('accuracy_percentage', 5, 2)->nullable();
            $table->decimal('speed_score', 5, 2)->nullable(); // PANKER
            $table->decimal('consistency_score', 5, 2)->nullable(); // JANKER
            $table->decimal('endurance_score', 5, 2)->nullable(); // HANKER
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psikotest_results');
    }
};
