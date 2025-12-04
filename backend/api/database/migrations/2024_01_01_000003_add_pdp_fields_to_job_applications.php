<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Data Karyawan (Excluding NIP, Position, Division)
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
            // phone_number and personal_email already exist as phone and email

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
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'join_date', 'contract_period', 'employment_status', 'ktp_number',
                'satpam_qualification', 'satpam_training_date', 'satpam_training_institution',
                'satpam_training_location', 'satpam_kta_number', 'satpam_certificate_number',
                'education_level', 'education_graduation_year', 'education_school_name',
                'education_city', 'education_major', 'birth_city', 'birth_date', 'age',
                'gender', 'mother_name', 'religion', 'blood_type', 'height_cm', 'weight_kg',
                'uniform_shirt_size', 'uniform_pants_size', 'uniform_shoes_size',
                'emergency_phone', 'emergency_name', 'emergency_relation',
                'npwp', 'sim_c_number', 'sim_a_number', 'bpjs_tk_number', 'bpjs_kes_number', 'kk_number',
                'address_province', 'address_regency', 'address_district', 'address_subdistrict',
                'address_street', 'address_rt', 'address_rw', 'address_postal_code',
                'domicile_province', 'domicile_regency', 'domicile_district', 'domicile_subdistrict',
                'domicile_street', 'domicile_rt', 'domicile_rw', 'domicile_postal_code',
                'marital_status', 'children_count',
                'exp1_year', 'exp1_position', 'exp1_company', 'exp1_city',
                'exp2_year', 'exp2_position', 'exp2_company', 'exp2_city',
                'exp3_year', 'exp3_position', 'exp3_company', 'exp3_city',
                'cert1_date', 'cert1_training', 'cert1_organizer', 'cert1_city',
                'cert2_date', 'cert2_training', 'cert2_organizer', 'cert2_city',
                'cert3_date', 'cert3_training', 'cert3_organizer', 'cert3_city',
                'instagram', 'facebook', 'twitter', 'tiktok', 'linkedin', 'youtube'
            ]);
        });
    }
};
