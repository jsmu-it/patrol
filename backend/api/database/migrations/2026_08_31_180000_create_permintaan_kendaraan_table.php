<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Form Permintaan Kendaraan.
 *
 * Mengikuti tiga kolom tanda tangan pada formulir cetak: pemohon mengisi,
 * atasan langsung menyetujui, lalu bagian GA menetapkan driver dan nomor
 * polisi pada bagian "CATATAN GA".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama');               // pemohon, disalin saat kirim
            $table->string('bagian');

            $table->date('tanggal_pakai');
            $table->time('jam');
            $table->text('keperluan');

            $table->enum('status', ['menunggu_atasan', 'menunggu_ga', 'siap', 'ditolak'])
                ->default('menunggu_atasan');

            // Atasan langsung
            $table->foreignId('atasan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atasan_pada')->nullable();
            $table->text('alasan_penolakan')->nullable();

            // Catatan GA
            $table->foreignId('ga_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ga_pada')->nullable();
            $table->string('driver')->nullable();
            $table->string('no_polisi', 40)->nullable();
            $table->text('catatan_ga')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_kendaraan');
    }
};
