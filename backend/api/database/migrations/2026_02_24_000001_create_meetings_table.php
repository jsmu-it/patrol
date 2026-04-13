<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug', 100)->unique();
            $table->string('room_name')->unique();
            $table->string('password')->nullable();
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['scheduled', 'active', 'ended'])->default('scheduled');
            $table->integer('max_participants')->default(100);
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
