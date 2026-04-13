<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name'); // e.g., "Cuti Tahunan", "Cuti Melahirkan", "Cuti Duka"
            $table->text('description')->nullable();
            $table->unsignedInteger('default_quota')->default(0); // Default quota in days
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default leave types
        DB::table('leave_types')->insert([
            [
                'name' => 'Cuti Tahunan',
                'description' => 'Cuti tahunan untuk karyawan tetap',
                'default_quota' => 12,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuti Melahirkan',
                'description' => 'Cuti melahirkan untuk karyawan wanita',
                'default_quota' => 90,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cuti Duka',
                'description' => 'Cuti untuk keluarga yang meninggal',
                'default_quota' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Izin',
                'description' => 'Izin tidak masuk kerja',
                'default_quota' => 0, // Unlimited/no quota
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sakit',
                'description' => 'Izin sakit dengan surat dokter',
                'default_quota' => 0, // Unlimited/no quota
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
