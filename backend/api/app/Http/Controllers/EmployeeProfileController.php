<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    public function showForm(Request $request): View
    {
        $projects = Project::orderBy('name')->get();

        return view('pdp.form', [
            'projects' => $projects,
        ]);
    }

    public function submitForm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Data akun dasar
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'active_project_id' => ['required', 'integer', 'exists:projects,id'],
            'profile_photo' => ['required', 'image', 'max:2048'],

            // Data karyawan & profil
            'position' => ['required', 'string'],
            'division' => ['required', 'string'],
            'join_date' => ['nullable', 'date'],
            'contract_period' => ['nullable', 'string'],
            'employment_status' => ['nullable', 'string'],
            'ktp_number' => ['nullable', 'string'],
            'satpam_qualification' => ['nullable', 'string'],
            'satpam_training_date' => ['nullable', 'date'],
            'satpam_training_institution' => ['nullable', 'string'],
            'satpam_training_location' => ['nullable', 'string'],
            'satpam_kta_number' => ['nullable', 'string'],
            'satpam_certificate_number' => ['nullable', 'string'],
            
            // Pendidikan Akademis (Wajib)
            'education_level' => ['required', 'string'],
            'education_graduation_year' => ['required', 'string'],
            'education_school_name' => ['required', 'string'],
            'education_city' => ['required', 'string'],
            'education_major' => ['required', 'string'],
            
            // Data Pribadi (Wajib)
            'birth_city' => ['required', 'string'],
            'birth_date' => ['required', 'date'],
            'age' => ['required', 'integer'],
            'gender' => ['required', 'string'],
            'mother_name' => ['required', 'string'],
            'religion' => ['required', 'string'],
            'blood_type' => ['required', 'string'],
            'phone_number' => ['required', 'string'],
            'personal_email' => ['required', 'email', 'max:255'],
            
            'height_cm' => ['nullable', 'integer'],
            'weight_kg' => ['nullable', 'integer'],
            'uniform_shirt_size' => ['nullable', 'string'],
            'uniform_pants_size' => ['nullable', 'string'],
            'uniform_shoes_size' => ['nullable', 'string'],
            
            // Telp Darurat (Wajib)
            'emergency_phone' => ['required', 'string'],
            'emergency_name' => ['required', 'string'],
            'emergency_relation' => ['required', 'string'],
            
            // Identitas (Wajib)
            'npwp' => ['required', 'string'],
            'sim_c_number' => ['required', 'string'],
            'sim_a_number' => ['required', 'string'],
            'bpjs_tk_number' => ['required', 'string'],
            'bpjs_kes_number' => ['required', 'string'],
            'kk_number' => ['required', 'string'],
            
            // Alamat KTP (Wajib)
            'address_province' => ['required', 'string'],
            'address_regency' => ['required', 'string'],
            'address_district' => ['required', 'string'],
            'address_subdistrict' => ['required', 'string'],
            'address_street' => ['required', 'string'],
            'address_rt' => ['required', 'string'],
            'address_rw' => ['required', 'string'],
            'address_postal_code' => ['required', 'string'],
            
            // Domisili (Wajib)
            'domicile_province' => ['required', 'string'],
            'domicile_regency' => ['required', 'string'],
            'domicile_district' => ['required', 'string'],
            'domicile_subdistrict' => ['required', 'string'],
            'domicile_street' => ['required', 'string'],
            'domicile_rt' => ['required', 'string'],
            'domicile_rw' => ['required', 'string'],
            'domicile_postal_code' => ['required', 'string'],
            
            'marital_status' => ['nullable', 'string'],
            'children_count' => ['nullable', 'integer'],
            'exp1_year' => ['nullable', 'string'],
            'exp1_position' => ['nullable', 'string'],
            'exp1_company' => ['nullable', 'string'],
            'exp1_city' => ['nullable', 'string'],
            'exp2_year' => ['nullable', 'string'],
            'exp2_position' => ['nullable', 'string'],
            'exp2_company' => ['nullable', 'string'],
            'exp2_city' => ['nullable', 'string'],
            'exp3_year' => ['nullable', 'string'],
            'exp3_position' => ['nullable', 'string'],
            'exp3_company' => ['nullable', 'string'],
            'exp3_city' => ['nullable', 'string'],
            'cert1_date' => ['nullable', 'date'],
            'cert1_training' => ['nullable', 'string'],
            'cert1_organizer' => ['nullable', 'string'],
            'cert1_city' => ['nullable', 'string'],
            'cert2_date' => ['nullable', 'date'],
            'cert2_training' => ['nullable', 'string'],
            'cert2_organizer' => ['nullable', 'string'],
            'cert2_city' => ['nullable', 'string'],
            'cert3_date' => ['nullable', 'date'],
            'cert3_training' => ['nullable', 'string'],
            'cert3_organizer' => ['nullable', 'string'],
            'cert3_city' => ['nullable', 'string'],
            'instagram' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string'],
            'twitter' => ['nullable', 'string'],
            'tiktok' => ['nullable', 'string'],
            'linkedin' => ['nullable', 'string'],
            'youtube' => ['nullable', 'string'],
        ]);

        // Username disamakan dengan NIP
        $username = $data['nip'];

        $user = User::firstOrNew(['username' => $username]);
        $user->name = $data['name'];
        $user->email = $data['email'] ?? null;
        $user->role = User::ROLE_GUARD;
        $user->active_project_id = $data['active_project_id'] ?? null;

        if (! $user->exists || ! $user->password) {
            $user->password = bcrypt($username);
        }

        $user->save();

        $profileData = $data;
        unset($profileData['name'], $profileData['email'], $profileData['active_project_id'], $profileData['profile_photo']);

        if ($request->hasFile('profile_photo')) {
            $profileData['profile_photo_path'] = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        }
        $profileData['user_id'] = $user->id;

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return redirect()->route('pdp.form')->with('status', 'Data berhasil dikirim. Terima kasih.');
    }
}
