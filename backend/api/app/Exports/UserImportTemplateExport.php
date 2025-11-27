<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserImportTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        // Template tanpa data, hanya header.
        return new Collection();
    }

    public function headings(): array
    {
        return [
            // Data akun dasar
            'name',
            'username',
            'email',
            'role', // ADMIN / GUARD
            'project_name',
            'password',

            // Data karyawan
            'nip',
            'position',
            'division',
            'join_date',
            'contract_period',
            'employment_status',
            'ktp_number',

            // Pendidikan Satpam
            'satpam_qualification',
            'satpam_training_date',
            'satpam_training_institution',
            'satpam_training_location',
            'satpam_kta_number',
            'satpam_certificate_number',

            // Pendidikan akademis
            'education_level',
            'education_graduation_year',
            'education_school_name',
            'education_city',
            'education_major',

            // BOD
            'birth_city',
            'birth_date',
            'age',
            'gender',
            'mother_name',
            'religion',
            'blood_type',
            'phone_number',
            'personal_email',

            // Postur & seragam
            'height_cm',
            'weight_kg',
            'uniform_shirt_size',
            'uniform_pants_size',
            'uniform_shoes_size',

            // Kontak darurat
            'emergency_phone',
            'emergency_name',
            'emergency_relation',

            // Identitas
            'npwp',
            'sim_c_number',
            'sim_a_number',
            'bpjs_tk_number',
            'bpjs_kes_number',
            'kk_number',

            // Alamat KTP
            'address_province',
            'address_regency',
            'address_district',
            'address_subdistrict',
            'address_street',
            'address_rt',
            'address_rw',
            'address_postal_code',

            // Domisili
            'domicile_province',
            'domicile_regency',
            'domicile_district',
            'domicile_subdistrict',
            'domicile_street',
            'domicile_rt',
            'domicile_rw',
            'domicile_postal_code',
            'marital_status',
            'children_count',

            // Pengalaman kerja 1-3
            'exp1_year',
            'exp1_position',
            'exp1_company',
            'exp1_city',
            'exp2_year',
            'exp2_position',
            'exp2_company',
            'exp2_city',
            'exp3_year',
            'exp3_position',
            'exp3_company',
            'exp3_city',

            // Sertifikasi 1-3
            'cert1_date',
            'cert1_training',
            'cert1_organizer',
            'cert1_city',
            'cert2_date',
            'cert2_training',
            'cert2_organizer',
            'cert2_city',
            'cert3_date',
            'cert3_training',
            'cert3_organizer',
            'cert3_city',

            // Media sosial
            'instagram',
            'facebook',
            'twitter',
            'tiktok',
            'linkedin',
            'youtube',
            'profile_photo_url',
        ];
    }
}
