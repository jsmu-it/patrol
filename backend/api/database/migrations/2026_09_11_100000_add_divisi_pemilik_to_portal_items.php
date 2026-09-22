<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Folder arsip milik divisi, bukan milik perorangan.
 *
 * Semula folder arsip (JOC, RPTK, PBG, Sasaran Mutu, Permintaan Kendaraan)
 * dititipkan ke salah satu anggota divisi. Begitu anggotanya bertambah atau
 * jabatannya berubah, penerima titipan ikut berganti dan folder kedua terbuat
 * — isinya jadi terbelah, persis yang terjadi pada folder JOC divisi HSE.
 *
 * Sekarang folder seperti itu dimiliki divisinya sendiri: `user_id` kosong,
 * `divisi_pemilik` diisi nama divisi. Karena tidak menempel pada siapa pun,
 * hanya ada satu dan tidak ikut terhapus ketika akun karyawan dihapus —
 * relasinya pun diubah dari cascade menjadi null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE portal_items MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('portal_items', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('divisi_pemilik', 100)->nullable()->after('user_id');
            $table->index('divisi_pemilik');
        });
    }

    public function down(): void
    {
        Schema::table('portal_items', function (Blueprint $table) {
            $table->dropIndex(['divisi_pemilik']);
            $table->dropColumn('divisi_pemilik');
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE portal_items MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('portal_items', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
