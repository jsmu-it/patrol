<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laporan Pencapaian Sasaran Mutu — formulir MR/FM-07-02a.
 *
 * Mengikuti alur PDCA pada formulir cetak: PLAN dan HASIL memakai baris yang
 * sama (satu sasaran punya target sekaligus realisasinya), sedangkan ACTION
 * berdiri sendiri sebagai daftar tindak lanjut. Keduanya disimpan sebagai JSON
 * karena jumlah barisnya bebas dan tidak pernah dicari per baris.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sasaran_mutu_reports', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama');              // pembuat, disalin saat kirim
            $table->string('jabatan_pembuat')->nullable();

            $table->string('bulan', 7);          // YYYY-MM
            $table->string('departemen');
            $table->string('sub_departemen')->nullable();

            $table->json('sasaran');             // PLAN + HASIL
            $table->text('pelaksanaan');         // DO
            $table->text('penyebab')->nullable(); // CHECK — penyebab
            $table->json('tindak_lanjut')->nullable(); // ACTION

            $table->date('tanggal');
            $table->string('diperiksa_oleh')->nullable();  // Kepala Departemen
            $table->string('disetujui_oleh')->nullable();  // Direktur

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['departemen', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sasaran_mutu_reports');
    }
};
