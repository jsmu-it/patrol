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
            'email' => ['nullable', 'email', 'max:255'],
            'active_project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],

            // Data karyawan & profil
            'position' => ['nullable', 'string'],
            'division' => ['nullable', 'string'],
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
            'education_level' => ['nullable', 'string'],
            'education_graduation_year' => ['nullable', 'string'],
            'education_school_name' => ['nullable', 'string'],
            'education_city' => ['nullable', 'string'],
            'education_major' => ['nullable', 'string'],
            'birth_city' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer'],
            'gender' => ['nullable', 'string'],
            'mother_name' => ['nullable', 'string'],
            'religion' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'height_cm' => ['nullable', 'integer'],
            'weight_kg' => ['nullable', 'integer'],
            'uniform_shirt_size' => ['nullable', 'string'],
            'uniform_pants_size' => ['nullable', 'string'],
            'uniform_shoes_size' => ['nullable', 'string'],
            'emergency_phone' => ['nullable', 'string'],
            'emergency_name' => ['nullable', 'string'],
            'emergency_relation' => ['nullable', 'string'],
            'npwp' => ['nullable', 'string'],
            'sim_c_number' => ['nullable', 'string'],
            'sim_a_number' => ['nullable', 'string'],
            'bpjs_tk_number' => ['nullable', 'string'],
            'bpjs_kes_number' => ['nullable', 'string'],
            'kk_number' => ['nullable', 'string'],
            'address_province' => ['nullable', 'string'],
            'address_regency' => ['nullable', 'string'],
            'address_district' => ['nullable', 'string'],
            'address_subdistrict' => ['nullable', 'string'],
            'address_street' => ['nullable', 'string'],
            'address_rt' => ['nullable', 'string'],
            'address_rw' => ['nullable', 'string'],
            'address_postal_code' => ['nullable', 'string'],
            'domicile_province' => ['nullable', 'string'],
            'domicile_regency' => ['nullable', 'string'],
            'domicile_district' => ['nullable', 'string'],
            'domicile_subdistrict' => ['nullable', 'string'],
            'domicile_street' => ['nullable', 'string'],
            'domicile_rt' => ['nullable', 'string'],
            'domicile_rw' => ['nullable', 'string'],
            'domicile_postal_code' => ['nullable', 'string'],
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
