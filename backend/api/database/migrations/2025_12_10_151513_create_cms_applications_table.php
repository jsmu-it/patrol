<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version')->nullable();
            $table->text('description')->nullable();
            $table->string('platform')->default('android'); // android, ios, windows, etc
            $table->string('file_path');
            $table->string('icon')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('download_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_applications');
    }
};
