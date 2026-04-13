<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkwt_income_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default income types
        $types = [
            ['name' => 'Gaji Pokok', 'code' => 'gaji_pokok', 'is_default' => true, 'sort_order' => 1],
            ['name' => 'Tunjangan Jabatan', 'code' => 'tunjangan_jabatan', 'is_default' => true, 'sort_order' => 2],
            ['name' => 'Uang Makan', 'code' => 'uang_makan', 'is_default' => true, 'sort_order' => 3],
            ['name' => 'Uang Transport', 'code' => 'uang_transport', 'is_default' => true, 'sort_order' => 4],
            ['name' => 'Uang Kehadiran', 'code' => 'uang_kehadiran', 'is_default' => true, 'sort_order' => 5],
            ['name' => 'Tunjangan Lain-lain', 'code' => 'tunjangan_lain', 'is_default' => true, 'sort_order' => 6],
            ['name' => 'Tunjangan Hari Raya', 'code' => 'thr', 'is_default' => true, 'sort_order' => 7],
            ['name' => 'Kompensasi PKWT', 'code' => 'kompensasi_pkwt', 'is_default' => true, 'sort_order' => 8],
            ['name' => 'Overtime Reguler', 'code' => 'overtime_reguler', 'is_default' => true, 'sort_order' => 9],
            ['name' => 'Lembur Shift', 'code' => 'lembur_shift', 'is_default' => true, 'sort_order' => 10],
            ['name' => 'Insentif', 'code' => 'insentif', 'is_default' => true, 'sort_order' => 11],
            ['name' => 'Tunjangan Rumah', 'code' => 'tunjangan_rumah', 'is_default' => true, 'sort_order' => 12],
            ['name' => 'Tunjangan Operasional', 'code' => 'tunjangan_operasional', 'is_default' => true, 'sort_order' => 13],
            ['name' => 'Tunjangan Pulsa', 'code' => 'tunjangan_pulsa', 'is_default' => true, 'sort_order' => 14],
            ['name' => 'Extra Fooding', 'code' => 'extra_fooding', 'is_default' => true, 'sort_order' => 15],
            ['name' => 'Tunjangan Shift', 'code' => 'tunjangan_shift', 'is_default' => true, 'sort_order' => 16],
            ['name' => 'Tunjangan Vitamin', 'code' => 'tunjangan_vitamin', 'is_default' => true, 'sort_order' => 17],
            ['name' => 'Tunjangan Parkir', 'code' => 'tunjangan_parkir', 'is_default' => true, 'sort_order' => 18],
        ];

        $now = now();
        foreach ($types as &$type) {
            $type['created_at'] = $now;
            $type['updated_at'] = $now;
        }

        DB::table('pkwt_income_types')->insert($types);
    }

    public function down(): void
    {
        Schema::dropIfExists('pkwt_income_types');
    }
};
