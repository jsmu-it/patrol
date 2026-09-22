<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Berbagi folder di Portal.
 *
 * Tiga mode, mengikuti pola Google Drive:
 *   privat  — hanya pemilik (bawaan)
 *   internal— semua karyawan yang login bisa membuka lewat sidebar
 *   tautan  — siapa pun yang punya tautan, dengan atau tanpa kata sandi
 *
 * Token dibuat acak dan panjang supaya tautan tidak bisa ditebak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_items', function (Blueprint $table) {
            $table->enum('share_mode', ['privat', 'internal', 'tautan'])
                  ->default('privat')->after('trashed_at');
            $table->string('share_token', 64)->nullable()->unique()->after('share_mode');
            $table->string('share_password')->nullable()->after('share_token');

            $table->index(['share_mode', 'trashed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('portal_items', function (Blueprint $table) {
            $table->dropIndex(['share_mode', 'trashed_at']);
            $table->dropColumn(['share_mode', 'share_token', 'share_password']);
        });
    }
};
