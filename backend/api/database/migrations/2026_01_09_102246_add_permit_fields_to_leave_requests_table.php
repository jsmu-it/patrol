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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->time('time_from')->nullable()->after('date_to');
            $table->time('time_to')->nullable()->after('time_from');
            $table->string('permit_photo')->nullable()->after('doctor_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['time_from', 'time_to', 'permit_photo']);
        });
    }
};
