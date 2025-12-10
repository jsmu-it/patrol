<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('client_position')->nullable();
            $table->string('client_company')->nullable();
            $table->string('client_photo')->nullable();
            $table->text('content');
            $table->tinyInteger('rating')->default(5); // 1-5 stars
            $table->string('token')->unique(); // untuk form publik
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
