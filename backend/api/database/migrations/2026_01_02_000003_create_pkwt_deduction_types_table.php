<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkwt_deduction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default deduction types
        $types = [
            ['name' => 'BPJS Kesehatan', 'code' => 'bpjs_kesehatan', 'is_default' => true, 'sort_order' => 1],
            ['name' => 'JHT', 'code' => 'jht', 'is_default' => true, 'sort_order' => 2],
            ['name' => 'JP', 'code' => 'jp', 'is_default' => true, 'sort_order' => 3],
            ['name' => 'Ijin/Sakit dengan SK', 'code' => 'ijin_sakit_sk', 'is_default' => true, 'sort_order' => 4],
            ['name' => 'Ijin/Sakit tanpa SK', 'code' => 'ijin_sakit_tanpa_sk', 'is_default' => true, 'sort_order' => 5],
            ['name' => 'Alpa/Tanpa Keterangan', 'code' => 'alpa', 'is_default' => true, 'sort_order' => 6],
            ['name' => 'PPh21', 'code' => 'pph21', 'is_default' => true, 'sort_order' => 7],
        ];

        $now = now();
        foreach ($types as &$type) {
            $type['created_at'] = $now;
            $type['updated_at'] = $now;
        }

        DB::table('pkwt_deduction_types')->insert($types);
    }

    public function down(): void
    {
        Schema::dropIfExists('pkwt_deduction_types');
    }
};
