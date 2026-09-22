<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persetujuan berjenjang untuk Laporan Sasaran Mutu.
 *
 * Semula kolom pemeriksa dan penyetuju hanya berisi nama yang diketik pembuat
 * lalu dicetak di dokumen. Sekarang keduanya menjadi tahap persetujuan sungguhan
 * — kepala departemen memeriksa, lalu Direktur menyetujui — seperti alur RPTK,
 * jadi nama dan tanggalnya terisi dari akun yang benar-benar menekan tombol.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sasaran_mutu_reports', function (Blueprint $table) {
            $table->dropColumn(['diperiksa_oleh', 'disetujui_oleh']);
        });

        Schema::table('sasaran_mutu_reports', function (Blueprint $table) {
            $table->enum('status', ['menunggu_manager', 'menunggu_direktur', 'disetujui', 'ditolak'])
                ->default('menunggu_manager')->after('tanggal');

            $table->foreignId('manager_oleh')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('manager_pada')->nullable()->after('manager_oleh');

            $table->foreignId('direktur_oleh')->nullable()->after('manager_pada')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('direktur_pada')->nullable()->after('direktur_oleh');

            $table->text('alasan_penolakan')->nullable()->after('direktur_pada');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('sasaran_mutu_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_oleh');
            $table->dropConstrainedForeignId('direktur_oleh');
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'manager_pada', 'direktur_pada', 'alasan_penolakan']);
            $table->string('diperiksa_oleh')->nullable();
            $table->string('disetujui_oleh')->nullable();
        });
    }
};
