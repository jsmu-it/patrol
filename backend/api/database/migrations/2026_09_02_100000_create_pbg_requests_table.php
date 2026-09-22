<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PBG — Permintaan Barang Gudang (formulir GA/FM-01-01).
 *
 * Daftar barang disimpan sebagai JSON karena jumlah barisnya bebas dan tidak
 * pernah dicari per baris. Kolom tanda tangan mengikuti formulir cetak:
 * pemohon mengajukan, kepala bagian menyetujui, lalu gudang memprosesnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbg_requests', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama');                 // pemohon, disalin saat kirim

            $table->date('tgl_pbg');
            $table->string('departemen');
            $table->string('unit_kerja')->nullable();
            $table->date('tgl_penggunaan')->nullable();

            $table->json('barang');

            $table->enum('status', ['menunggu_kabag', 'menunggu_gudang', 'selesai', 'ditolak'])
                ->default('menunggu_kabag');

            // Menyetujui — Kepala Bagian
            $table->foreignId('kabag_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('kabag_pada')->nullable();
            $table->text('alasan_penolakan')->nullable();

            // Diterima — Petugas Gudang, beserta nama Kepala Gudang yang mengetahui
            $table->foreignId('gudang_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gudang_pada')->nullable();
            $table->string('kepala_gudang')->nullable();
            $table->text('catatan_gudang')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbg_requests');
    }
};
