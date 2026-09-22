<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Gabungkan peran PROJECT_ADMIN ke ADMIN.
 *
 * Keduanya tidak pernah berbeda perilakunya: cakupan akses ditentukan tabel
 * user_project_access (dengan active_project_id sebagai cadangan), dan di
 * setiap middleware rute selalu disebut berpasangan. Menyisakan dua peran
 * yang sama hanya membingungkan saat menambah admin.
 *
 * Konstanta User::ROLE_PROJECT_ADMIN sengaja tidak dihapus dari kode supaya
 * pengecekan lama tetap aman; peran ini hanya tidak bisa dipilih lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'PROJECT_ADMIN')->update(['role' => 'ADMIN']);
    }

    public function down(): void
    {
        // Tidak bisa dikembalikan: setelah digabung, tidak ada penanda mana
        // yang dulunya PROJECT_ADMIN. Dibiarkan kosong dengan sengaja.
    }
};
