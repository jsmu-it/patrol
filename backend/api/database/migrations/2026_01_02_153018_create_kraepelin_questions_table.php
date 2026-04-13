<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kraepelin_questions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Set');
            $table->integer('column_count')->default(50);
            $table->integer('rows_per_column')->default(60);
            $table->integer('time_per_column_seconds')->default(15);
            $table->json('columns_data'); // Array of 50 columns, each with 60 numbers
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kraepelin_questions');
    }
};
