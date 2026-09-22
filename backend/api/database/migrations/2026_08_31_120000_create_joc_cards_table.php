<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * JOC — JSMU Observation Card.
 *
 * Versi digital dari kartu pengamatan yang selama ini diisi tangan.
 * Setiap kartu yang dikirim memicu notifikasi ke divisi HSE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joc_cards', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();

            // Data pengamatan
            $table->date('tanggal');
            $table->time('jam');
            $table->string('lokasi');
            $table->string('nama');           // nama pengamat, disalin saat kirim

            // Temuan observasi: tindakan atau kondisi tidak aman
            $table->enum('jenis_temuan', ['tindakan_tidak_aman', 'kondisi_tidak_aman']);

            // Kategori temuan (boleh lebih dari satu)
            $table->json('kategori')->nullable();
            $table->string('kategori_lain')->nullable();

            $table->text('rincian_temuan');
            $table->text('rincian_tindakan');
            $table->text('catatan')->nullable();

            // Tanda tangan pengamat (data URL kanvas)
            $table->longText('ttd')->nullable();

            // Ditindaklanjuti HSE
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->text('tanggapan_hse')->nullable();
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditangani_pada')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joc_cards');
    }
};
