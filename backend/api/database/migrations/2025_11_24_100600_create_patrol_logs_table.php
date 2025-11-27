<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checkpoint_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('post_name');
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->dateTimeTz('occurred_at');

            $table->timestamps();

            $table->index(['user_id', 'project_id', 'checkpoint_id', 'occurred_at'], 'patrol_logs_main_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_logs');
    }
};
