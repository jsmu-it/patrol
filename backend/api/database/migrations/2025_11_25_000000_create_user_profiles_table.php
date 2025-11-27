<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Data karyawan
            $table->text('nip')->nullable();
            $table->text('position')->nullable(); // Jabatan
            $table->text('division')->nullable();
            $table->date('join_date')->nullable();
            $table->text('contract_period')->nullable();
            $table->text('employment_status')->nullable();
            $table->text('ktp_number')->nullable();

            // Pendidikan Satpam
            $table->text('satpam_qualification')->nullable();
            $table->date('satpam_training_date')->nullable();
            $table->text('satpam_training_institution')->nullable();
            $table->text('satpam_training_location')->nullable();
            $table->text('satpam_kta_number')->nullable();
            $table->text('satpam_certificate_number')->nullable();

            // Pendidikan akademis
            $table->text('education_level')->nullable();
            $table->text('education_graduation_year')->nullable();
            $table->text('education_school_name')->nullable();
            $table->text('education_city')->nullable();
            $table->text('education_major')->nullable();

            // Biodata (BOD)
            $table->text('birth_city')->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('gender')->nullable();
            $table->text('mother_name')->nullable();
            $table->text('religion')->nullable();
            $table->text('blood_type')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('personal_email')->nullable();

            // Postur tubuh
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();

            // Seragam
            $table->text('uniform_shirt_size')->nullable();
            $table->text('uniform_pants_size')->nullable();
            $table->text('uniform_shoes_size')->nullable();

            // Kontak darurat
            $table->text('emergency_phone')->nullable();
            $table->text('emergency_name')->nullable();
            $table->text('emergency_relation')->nullable();

            // Identitas tambahan
            $table->text('npwp')->nullable();
            $table->text('sim_c_number')->nullable();
            $table->text('sim_a_number')->nullable();
            $table->text('bpjs_tk_number')->nullable();
            $table->text('bpjs_kes_number')->nullable();
            $table->text('kk_number')->nullable();

            // Alamat KTP
            $table->text('address_province')->nullable();
            $table->text('address_regency')->nullable();
            $table->text('address_district')->nullable();
            $table->text('address_subdistrict')->nullable();
            $table->text('address_street')->nullable();
            $table->text('address_rt')->nullable();
            $table->text('address_rw')->nullable();
            $table->text('address_postal_code')->nullable();

            // Alamat domisili
            $table->text('domicile_province')->nullable();
            $table->text('domicile_regency')->nullable();
            $table->text('domicile_district')->nullable();
            $table->text('domicile_subdistrict')->nullable();
            $table->text('domicile_street')->nullable();
            $table->text('domicile_rt')->nullable();
            $table->text('domicile_rw')->nullable();
            $table->text('domicile_postal_code')->nullable();

            $table->text('marital_status')->nullable();
            $table->unsignedTinyInteger('children_count')->nullable();

            // Pengalaman kerja 1-3
            $table->text('exp1_year')->nullable();
            $table->text('exp1_position')->nullable();
            $table->text('exp1_company')->nullable();
            $table->text('exp1_city')->nullable();

            $table->text('exp2_year')->nullable();
            $table->text('exp2_position')->nullable();
            $table->text('exp2_company')->nullable();
            $table->text('exp2_city')->nullable();

            $table->text('exp3_year')->nullable();
            $table->text('exp3_position')->nullable();
            $table->text('exp3_company')->nullable();
            $table->text('exp3_city')->nullable();

            // Sertifikasi 1-3
            $table->date('cert1_date')->nullable();
            $table->text('cert1_training')->nullable();
            $table->text('cert1_organizer')->nullable();
            $table->text('cert1_city')->nullable();

            $table->date('cert2_date')->nullable();
            $table->text('cert2_training')->nullable();
            $table->text('cert2_organizer')->nullable();
            $table->text('cert2_city')->nullable();

            $table->date('cert3_date')->nullable();
            $table->text('cert3_training')->nullable();
            $table->text('cert3_organizer')->nullable();
            $table->text('cert3_city')->nullable();

            // Media sosial
            $table->text('instagram')->nullable();
            $table->text('facebook')->nullable();
            $table->text('twitter')->nullable();
            $table->text('tiktok')->nullable();
            $table->text('linkedin')->nullable();
            $table->text('youtube')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
