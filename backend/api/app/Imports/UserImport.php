<?php


namespace App\Imports;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Project;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToCollection, WithHeadingRow
{
    /**
     * Parse Indonesian date format to Y-m-d format
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // If already in correct format or is a date object, return as is
        if ($date instanceof \DateTime || $date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        // Try to parse Indonesian month names
        $indonesianMonths = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03',
            'April' => '04', 'Mei' => '05', 'Juni' => '06',
            'Juli' => '07', 'Agustus' => '08', 'September' => '09',
            'Oktober' => '10', 'November' => '11', 'Desember' => '12'
        ];

        $dateStr = trim($date);
        
        // Pattern: DD Month YYYY (e.g., "01 Januari 2026")
        foreach ($indonesianMonths as $indo => $num) {
            if (stripos($dateStr, $indo) !== false) {
                $dateStr = str_ireplace($indo, $num, $dateStr);
                // Now format is like "01 01 2026", convert to "2026-01-01"
                $parts = preg_split('/[\s\-\/]+/', $dateStr);
                if (count($parts) >= 3) {
                    return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
                }
            }
        }

        // Try standard formats
        try {
            if (is_numeric($dateStr)) {
                // Excel serial date
                $unix = ($dateStr - 25569) * 86400;
                return date('Y-m-d', $unix);
            }
            
            // Try parsing with Carbon as fallback
            return \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (! ($row['username'] ?? null)) {
                continue;
            }

            $project = null;

            if (! empty($row['project_name'])) {
                $project = Project::firstOrCreate(
                    ['name' => $row['project_name']],
                    [
                        'client_name' => $row['project_name'],
                        'address' => '-',
                        'latitude' => 0.0,
                        'longitude' => 0.0,
                        'geofence_radius_meters' => 500,
                        'is_active' => true,
                    ]
                );
            }

            $role = strtoupper((string) ($row['role'] ?? User::ROLE_GUARD));
            if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_GUARD], true)) {
                $role = User::ROLE_GUARD;
            }

            $password = (string) ($row['password'] ?? '');
            $email = $row['email'] ?? null;
            $username = $row['username'];

            // Check for duplicate email - if email exists with different username, skip this row
            if ($email) {
                $existingUserWithEmail = User::where('email', $email)
                    ->where('username', '!=', $username)
                    ->first();
                
                if ($existingUserWithEmail) {
                    // Skip this row - email already used by another user
                    continue;
                }
            }

            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $row['name'] ?? $username,
                    'email' => $email,
                    'role' => $role,
                    'active_project_id' => $project?->id,
                    'password' => Hash::make($password !== '' ? $password : 'password'),
                ]
            );

            $profileData = [
                'nip' => $row['nip'] ?? null,
                'position' => $row['position'] ?? null,
                'division' => $row['division'] ?? null,
                'join_date' => $this->parseDate($row['join_date'] ?? null),
                'contract_period' => $row['contract_period'] ?? null,
                'employment_status' => $row['employment_status'] ?? null,
                'ktp_number' => $row['ktp_number'] ?? null,
                'satpam_qualification' => $row['satpam_qualification'] ?? null,
                'satpam_training_date' => $this->parseDate($row['satpam_training_date'] ?? null),
                'satpam_training_institution' => $row['satpam_training_institution'] ?? null,
                'satpam_training_location' => $row['satpam_training_location'] ?? null,
                'satpam_kta_number' => $row['satpam_kta_number'] ?? null,
                'satpam_certificate_number' => $row['satpam_certificate_number'] ?? null,
                'education_level' => $row['education_level'] ?? null,
                'education_graduation_year' => $row['education_graduation_year'] ?? null,
                'education_school_name' => $row['education_school_name'] ?? null,
                'education_city' => $row['education_city'] ?? null,
                'education_major' => $row['education_major'] ?? null,
                'birth_city' => $row['birth_city'] ?? null,
                'birth_date' => $this->parseDate($row['birth_date'] ?? null),
                'age' => $row['age'] ?? null,
                'gender' => $row['gender'] ?? null,
                'mother_name' => $row['mother_name'] ?? null,
                'religion' => $row['religion'] ?? null,
                'blood_type' => $row['blood_type'] ?? null,
                'phone_number' => $row['phone_number'] ?? null,
                'personal_email' => $row['personal_email'] ?? null,
                'height_cm' => $row['height_cm'] ?? null,
                'weight_kg' => $row['weight_kg'] ?? null,
                'uniform_shirt_size' => $row['uniform_shirt_size'] ?? null,
                'uniform_pants_size' => $row['uniform_pants_size'] ?? null,
                'uniform_shoes_size' => $row['uniform_shoes_size'] ?? null,
                'emergency_phone' => $row['emergency_phone'] ?? null,
                'emergency_name' => $row['emergency_name'] ?? null,
                'emergency_relation' => $row['emergency_relation'] ?? null,
                'npwp' => $row['npwp'] ?? null,
                'sim_c_number' => $row['sim_c_number'] ?? null,
                'sim_a_number' => $row['sim_a_number'] ?? null,
                'bpjs_tk_number' => $row['bpjs_tk_number'] ?? null,
                'bpjs_kes_number' => $row['bpjs_kes_number'] ?? null,
                'kk_number' => $row['kk_number'] ?? null,
                'address_province' => $row['address_province'] ?? null,
                'address_regency' => $row['address_regency'] ?? null,
                'address_district' => $row['address_district'] ?? null,
                'address_subdistrict' => $row['address_subdistrict'] ?? null,
                'address_street' => $row['address_street'] ?? null,
                'address_rt' => $row['address_rt'] ?? null,
                'address_rw' => $row['address_rw'] ?? null,
                'address_postal_code' => $row['address_postal_code'] ?? null,
                'domicile_province' => $row['domicile_province'] ?? null,
                'domicile_regency' => $row['domicile_regency'] ?? null,
                'domicile_district' => $row['domicile_district'] ?? null,
                'domicile_subdistrict' => $row['domicile_subdistrict'] ?? null,
                'domicile_street' => $row['domicile_street'] ?? null,
                'domicile_rt' => $row['domicile_rt'] ?? null,
                'domicile_rw' => $row['domicile_rw'] ?? null,
                'domicile_postal_code' => $row['domicile_postal_code'] ?? null,
                'marital_status' => $row['marital_status'] ?? null,
                'children_count' => $row['children_count'] ?? null,
                'exp1_year' => $row['exp1_year'] ?? null,
                'exp1_position' => $row['exp1_position'] ?? null,
                'exp1_company' => $row['exp1_company'] ?? null,
                'exp1_city' => $row['exp1_city'] ?? null,
                'exp2_year' => $row['exp2_year'] ?? null,
                'exp2_position' => $row['exp2_position'] ?? null,
                'exp2_company' => $row['exp2_company'] ?? null,
                'exp2_city' => $row['exp2_city'] ?? null,
                'exp3_year' => $row['exp3_year'] ?? null,
                'exp3_position' => $row['exp3_position'] ?? null,
                'exp3_company' => $row['exp3_company'] ?? null,
                'exp3_city' => $row['exp3_city'] ?? null,
                'cert1_date' => $this->parseDate($row['cert1_date'] ?? null),
                'cert1_training' => $row['cert1_training'] ?? null,
                'cert1_organizer' => $row['cert1_organizer'] ?? null,
                'cert1_city' => $row['cert1_city'] ?? null,
                'cert2_date' => $this->parseDate($row['cert2_date'] ?? null),
                'cert2_training' => $row['cert2_training'] ?? null,
                'cert2_organizer' => $row['cert2_organizer'] ?? null,
                'cert2_city' => $row['cert2_city'] ?? null,
                'cert3_date' => $this->parseDate($row['cert3_date'] ?? null),
                'cert3_training' => $row['cert3_training'] ?? null,
                'cert3_organizer' => $row['cert3_organizer'] ?? null,
                'cert3_city' => $row['cert3_city'] ?? null,
                'instagram' => $row['instagram'] ?? null,
                'facebook' => $row['facebook'] ?? null,
                'twitter' => $row['twitter'] ?? null,
                'tiktok' => $row['tiktok'] ?? null,
                'linkedin' => $row['linkedin'] ?? null,
                'youtube' => $row['youtube'] ?? null,
                'profile_photo_path' => $row['profile_photo_url'] ?? null,
            ];

            $hasProfileData = collect($profileData)->filter(static function ($value) {
                return $value !== null && $value !== '';
            })->isNotEmpty();

            if ($hasProfileData) {
                UserProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }

            // Initialize leave balances for new users (only if they don't have any yet)
            $currentYear = now()->year;
            $existingBalances = $user->leaveBalances()->where('year', $currentYear)->count();
            
            if ($existingBalances === 0) {
                $activeLeaveTypes = LeaveType::active()->get();
                
                foreach ($activeLeaveTypes as $leaveType) {
                    LeaveBalance::create([
                        'user_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'quota' => $leaveType->default_quota ?? 0,
                        'used' => 0,
                        'year' => $currentYear,
                    ]);
                }
            }
        }
    }
}
