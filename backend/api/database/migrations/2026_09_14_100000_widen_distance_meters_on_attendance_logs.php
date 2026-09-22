<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jarak absensi tidak muat di kolom smallint.
 *
 * `distance_meters` semula unsigned smallint — maksimal 65.535 meter. Cukup
 * untuk absen normal di dalam radius, tetapi absen mode DINAS bisa berjarak
 * ratusan hingga ribuan kilometer dari titik project (mis. dari Balikpapan ke
 * kantor Jakarta, ±1.243 km). Nilai sebesar itu ditolak MySQL dan seluruh
 * penyimpanan absensi gagal dengan "Server Error".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE attendance_logs MODIFY distance_meters INT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE attendance_logs MODIFY distance_meters SMALLINT UNSIGNED NULL');
    }
};
