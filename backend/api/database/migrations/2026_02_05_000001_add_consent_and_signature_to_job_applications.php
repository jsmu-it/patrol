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
        Schema::table('job_applications', function (Blueprint $table) {
            $table->boolean('data_consent')->default(false)->after('youtube');
            $table->timestamp('data_consent_at')->nullable()->after('data_consent');
            $table->longText('signature')->nullable()->after('data_consent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['data_consent', 'data_consent_at', 'signature']);
        });
    }
};
