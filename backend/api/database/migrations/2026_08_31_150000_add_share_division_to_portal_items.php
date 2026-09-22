<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mode berbagi tambahan: hanya satu divisi yang boleh melihat.
 *
 * Melengkapi mode yang sudah ada (privat / internal / tautan). Berguna untuk
 * folder yang isinya hanya relevan bagi satu bagian, misalnya berkas HR & GA
 * yang tidak perlu dilihat seluruh perusahaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE portal_items MODIFY COLUMN share_mode
            ENUM('privat','internal','divisi','tautan') NOT NULL DEFAULT 'privat'");

        Schema::table('portal_items', function (Blueprint $table) {
            $table->string('share_division')->nullable()->after('share_mode');
        });
    }

    public function down(): void
    {
        // Folder bermode divisi diturunkan jadi privat agar tidak menggantung.
        DB::table('portal_items')->where('share_mode', 'divisi')
            ->update(['share_mode' => 'privat', 'share_division' => null]);

        Schema::table('portal_items', function (Blueprint $table) {
            $table->dropColumn('share_division');
        });

        DB::statement("ALTER TABLE portal_items MODIFY COLUMN share_mode
            ENUM('privat','internal','tautan') NOT NULL DEFAULT 'privat'");
    }
};
