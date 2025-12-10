<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('target', ['all', 'project', 'role'])->default('all');
            $table->foreignId('target_project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('target_role')->nullable(); // GUARD, ADMIN, etc
            $table->integer('recipients_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_notifications');
    }
};
