<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto bukti pada kartu JOC, maksimal dua lembar.
 *
 * Disimpan sebagai daftar path di disk privat `local`, bukan disk publik,
 * karena isinya bisa memperlihatkan kondisi internal lokasi kerja. Foto
 * dilayani lewat controller yang memeriksa hak akses sama seperti kartunya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('joc_cards', function (Blueprint $table) {
            $table->json('foto')->nullable()->after('ttd');
        });
    }

    public function down(): void
    {
        Schema::table('joc_cards', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
