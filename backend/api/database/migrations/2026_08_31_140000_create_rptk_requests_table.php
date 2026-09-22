<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RPTK — Rencana Permintaan Tenaga Kerja (formulir HR/FM-04-01).
 * Versi digital dari formulir cetak, lengkap dengan alur persetujuan
 * dua tingkat: HR & GA Manager, lalu Direktur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rptk_requests', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // A. Permintaan
            $table->string('nama');
            $table->string('divisi')->nullable();
            $table->string('lokasi_kerja')->nullable();
            $table->unsignedSmallInteger('jumlah')->default(1);
            $table->string('ref_dokumen')->nullable();
            $table->string('no_tanggal_dokumen')->nullable();

            $table->enum('alasan', ['karyawan_keluar', 'mutasi', 'penambahan', 'lain']);
            $table->string('alasan_lain')->nullable();

            $table->enum('status_karyawan', ['bulanan_tetap', 'bulanan_kontrak', 'harian', 'lain']);
            $table->string('status_karyawan_lain')->nullable();

            // Spesifikasi jabatan
            $table->string('jabatan');
            $table->text('uraian_tugas');

            // B. Spesifikasi tenaga kerja yang diharapkan
            $table->string('tinggi_badan')->nullable();
            $table->string('berat_badan')->nullable();
            $table->string('usia')->nullable();
            $table->enum('jenis_kelamin', ['laki', 'perempuan', 'bebas'])->default('bebas');
            $table->string('pendidikan_formal')->nullable();
            $table->string('pendidikan_non_formal')->nullable();
            $table->string('keahlian_khusus')->nullable();
            $table->string('pengalaman')->nullable();

            // Rekomendasi sumber rekrutmen
            $table->enum('sumber', ['eksternal', 'internal'])->default('eksternal');
            $table->text('catatan')->nullable();
            $table->string('tanggal_efektif')->nullable();

            // Persetujuan berjenjang
            $table->enum('status', ['menunggu_hrga', 'menunggu_direktur', 'disetujui', 'ditolak'])
                  ->default('menunggu_hrga');
            $table->foreignId('hrga_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hrga_pada')->nullable();
            $table->foreignId('direktur_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('direktur_pada')->nullable();
            $table->text('alasan_penolakan')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rptk_requests');
    }
};
