<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'cms_career_id',
        'name',
        'email',
        'phone',
        'resume_path',
        'cover_letter',
        'status',
        'notes',
        
        // PDP Fields
        'join_date', 'contract_period', 'employment_status', 'ktp_number',
        'satpam_qualification', 'satpam_training_date', 'satpam_training_institution',
        'satpam_training_location', 'satpam_kta_number', 'satpam_certificate_number',
        'education_level', 'education_graduation_year', 'education_school_name',
        'education_city', 'education_major',
        'birth_city', 'birth_date', 'age', 'gender', 'mother_name', 'religion', 'blood_type',
        'height_cm', 'weight_kg',
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
    ];

    public function career()
    {
        return $this->belongsTo(CmsCareer::class, 'cms_career_id');
    }
}
