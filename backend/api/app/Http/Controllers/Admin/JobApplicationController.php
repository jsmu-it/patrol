<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsCareer;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JobApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->routeIs('admin.hrd.rejected') ? 'rejected' : ['pending', 'interview'];
        
        $query = JobApplication::with('career')->latest();

        if (is_array($status)) {
            $query->whereIn('status', $status);
        } else {
            $query->where('status', $status);
        }

        // Filter by position (career_id)
        if ($request->filled('career_id')) {
            $query->where('career_id', $request->career_id);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->whereHas('career', function ($q) use ($request) {
                $q->where('location', $request->location);
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        $pageTitle = $status === 'rejected' ? 'Karyawan Ditolak' : 'Data Pelamar';

        // Get careers and locations for filter dropdowns
        $careers = CmsCareer::orderBy('title')->get();
        $locations = CmsCareer::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location');

        return view('admin.hrd.applications.index', compact('applications', 'pageTitle', 'status', 'careers', 'locations'));
    }

    public function show(JobApplication $application): View
    {
        $projects = Project::orderBy('name')->get();
        return view('admin.hrd.applications.show', compact('application', 'projects'));
    }

    public function updateStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $rules = [
            'status' => 'required|in:pending,interview,accepted,rejected',
            'notes' => 'nullable|string'
        ];

        if ($request->status === 'accepted') {
            $rules['project_id'] = 'required|exists:projects,id';
            $rules['salary'] = 'required|numeric|min:0';
            $rules['nip'] = 'required|string|unique:user_profiles,nip|unique:users,username';
            $rules['join_date'] = 'required|date';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $application) {
            if ($request->status === 'accepted') {
                // Create User
                $user = User::create([
                    'name' => $application->name,
                    'username' => $request->nip,
                    'email' => $application->email,
                    'password' => Hash::make($request->nip), // Default password = NIP
                    'role' => 'GUARD',
                    'active_project_id' => $request->project_id,
                ]);

                // Create User Profile
                UserProfile::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'salary' => $request->salary,
                    'join_date' => $request->join_date,
                    'personal_email' => $application->email,
                    'phone_number' => $application->phone,
                    
                    // Map from application
                    'position' => 'Security Guard', // Default
                    'ktp_number' => $application->ktp_number,
                    'kk_number' => $application->kk_number,
                    'birth_city' => $application->birth_city,
                    'birth_date' => $application->birth_date,
                    'age' => $application->age,
                    'gender' => $application->gender,
                    'religion' => $application->religion,
                    'blood_type' => $application->blood_type,
                    'mother_name' => $application->mother_name,
                    'marital_status' => $application->marital_status,
                    'height_cm' => $application->height_cm,
                    'weight_kg' => $application->weight_kg,
                    
                    // Addresses
                    'address_street' => $application->address_street,
                    'address_rt' => $application->address_rt,
                    'address_rw' => $application->address_rw,
                    'address_subdistrict' => $application->address_subdistrict,
                    'address_district' => $application->address_district,
                    'address_regency' => $application->address_regency,
                    'address_province' => $application->address_province,
                    'address_postal_code' => $application->address_postal_code,
                    
                    'domicile_street' => $application->domicile_street,
                    'domicile_rt' => $application->domicile_rt,
                    'domicile_rw' => $application->domicile_rw,
                    'domicile_subdistrict' => $application->domicile_subdistrict,
                    'domicile_district' => $application->domicile_district,
                    'domicile_regency' => $application->domicile_regency,
                    'domicile_province' => $application->domicile_province,
                    'domicile_postal_code' => $application->domicile_postal_code,

                    // Education & Certs
                    'education_level' => $application->education_level,
                    'education_school_name' => $application->education_school_name,
                    'education_major' => $application->education_major,
                    'education_graduation_year' => $application->education_graduation_year,
                    'education_city' => $application->education_city,
                    
                    'satpam_qualification' => $application->satpam_qualification,
                    'satpam_kta_number' => $application->satpam_kta_number,
                    'satpam_certificate_number' => $application->satpam_certificate_number,
                    'satpam_training_date' => $application->satpam_training_date,
                    'satpam_training_institution' => $application->satpam_training_institution,
                    'satpam_training_location' => $application->satpam_training_location,

                    // Others
                    'npwp' => $application->npwp,
                    'sim_a_number' => $application->sim_a_number,
                    'sim_c_number' => $application->sim_c_number,
                    'bpjs_tk_number' => $application->bpjs_tk_number,
                    'bpjs_kes_number' => $application->bpjs_kes_number,
                    
                    'uniform_shirt_size' => $application->uniform_shirt_size,
                    'uniform_pants_size' => $application->uniform_pants_size,
                    'uniform_shoes_size' => $application->uniform_shoes_size,
                    
                    'emergency_name' => $application->emergency_name,
                    'emergency_phone' => $application->emergency_phone,
                    'emergency_relation' => $application->emergency_relation,
                    'children_count' => $application->children_count,

                    // Experience & Socials
                    'exp1_company' => $application->exp1_company,
                    'exp1_position' => $application->exp1_position,
                    'exp1_year' => $application->exp1_year,
                    'exp1_city' => $application->exp1_city,
                    'exp2_company' => $application->exp2_company,
                    'exp2_position' => $application->exp2_position,
                    'exp2_year' => $application->exp2_year,
                    'exp2_city' => $application->exp2_city,
                    
                    'cert1_training' => $application->cert1_training,
                    'cert1_organizer' => $application->cert1_organizer,
                    'cert1_date' => $application->cert1_date,
                    'cert1_city' => $application->cert1_city,
                    'cert2_training' => $application->cert2_training,
                    'cert2_organizer' => $application->cert2_organizer,
                    'cert2_date' => $application->cert2_date,
                    'cert2_city' => $application->cert2_city,

                    'instagram' => $application->instagram,
                    'facebook' => $application->facebook,
                    'twitter' => $application->twitter,
                    'tiktok' => $application->tiktok,
                    'linkedin' => $application->linkedin,
                ]);

                // Copy Profile Photo if exists (assuming resume_path is just a file, usually photo is separate but here we don't have it yet)
                // For now, leave profile_photo_path empty or set default
            }

            $application->update([
                'status' => $request->status,
                'notes' => $request->notes
            ]);
        });

        $redirectRoute = $application->status === 'rejected' ? 'admin.hrd.rejected' : 'admin.hrd.applications';

        return redirect()->route($redirectRoute)->with('status', 'Status lamaran berhasil diperbarui.');
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        if ($application->resume_path) {
            Storage::disk('public')->delete($application->resume_path);
        }

        $application->delete();

        return back()->with('status', 'Data pelamar berhasil dihapus.');
    }
}
