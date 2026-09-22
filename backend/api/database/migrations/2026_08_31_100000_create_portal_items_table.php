<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penyimpanan berkas Portal JSMU.
 *
 * Sengaja tabel tersendiri, bukan menumpang `sharing_items` milik modul
 * berbagi berkas admin. Dua alasan: modul lama memakai sandi per folder dan
 * dapat diakses publik, sementara portal ini milik-per-pengguna dan wajib
 * login; mencampurnya akan membuat aturan aksesnya sulit dijamin.
 *
 * Berkas disimpan di disk privat `local` pada storage/app/portal/{user_id}/,
 * tidak di disk `public`, sehingga tidak bisa ditebak lewat URL langsung.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('portal_items')->cascadeOnDelete();

            $table->enum('type', ['folder', 'file']);
            $table->string('name');

            // Hanya untuk type=file
            $table->string('path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            // Tempat sampah: item tidak langsung hilang saat dihapus
            $table->timestamp('trashed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'parent_id']);
            $table->index(['user_id', 'trashed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_items');
    }
};
