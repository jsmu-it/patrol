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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                  ->nullable()
                  ->after('active_project_id')
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->index('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropIndex(['supervisor_id']);
            $table->dropColumn('supervisor_id');
        });
    }
};
