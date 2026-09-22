<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layanan bisa bernaung di bawah layanan lain.
 *
 * Menu "Our Services" di situs disusun dari tabel ini. Dengan induk, sebuah
 * layanan bisa ditaruh sebagai menu tersendiri atau sebagai anak menu yang
 * sudah ada — misalnya beberapa layanan di bawah K-9 Guard.
 *
 * Induk yang dihapus tidak menyeret anaknya: anaknya naik menjadi menu utama,
 * supaya isi yang sudah ditulis tidak ikut hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_services', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')
                ->constrained('cms_services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cms_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
