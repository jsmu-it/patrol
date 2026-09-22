<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan mutu sinyal GPS saat absensi.
 *
 * `gps_accuracy_meters` adalah radius ketidakpastian yang dilaporkan perangkat,
 * `distance_meters` jarak hasil hitung ke titik project. Keduanya dicatat agar
 * keluhan "sudah di lokasi tapi ditolak" bisa ditelusuri dengan angka, bukan
 * tebakan, dan agar radius tiap project bisa disetel berdasarkan data nyata.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('gps_accuracy_meters')->nullable()->after('longitude');
            $table->unsignedSmallInteger('distance_meters')->nullable()->after('gps_accuracy_meters');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['gps_accuracy_meters', 'distance_meters']);
        });
    }
};
