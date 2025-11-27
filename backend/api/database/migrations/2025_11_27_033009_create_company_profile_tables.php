<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. CMS Contents (Static pages/sections)
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'home_hero', 'about_us'
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->longText('body')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // 2. Services
        Schema::create('cms_services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('icon')->nullable(); // Can be an image path or icon class
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Achievements
        Schema::create('cms_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('year')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 4. Activities (Internal Events, etc.)
        Schema::create('cms_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->date('date')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('type')->default('internal_event'); // internal_event, news, etc.
            $table->timestamps();
        });

        // 5. Clients
        Schema::create('cms_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 6. Careers
        Schema::create('cms_careers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->string('type')->nullable(); // Full-time, Contract
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Contact Messages
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('cms_careers');
        Schema::dropIfExists('cms_clients');
        Schema::dropIfExists('cms_activities');
        Schema::dropIfExists('cms_achievements');
        Schema::dropIfExists('cms_services');
        Schema::dropIfExists('cms_contents');
    }
};
