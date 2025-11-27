<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UserImport;
use App\Exports\UserImportTemplateExport;
use App\Models\Project;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $current = $request->user();

        $query = User::query()->with('activeProject');

        if ($current->isProjectAdmin() && $current->active_project_id) {
            $query->where('active_project_id', $current->active_project_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('project_id')) {
            $query->where('active_project_id', $request->integer('project_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%');
            });
        }

        if ($request->boolean('sort_by_project')) {
            $query->leftJoin('projects', 'users.active_project_id', '=', 'projects.id')
                ->select('users.*')
                ->orderBy('projects.name')
                ->orderBy('users.name');
        } else {
            $query->orderBy('name');
        }

        // Super Admin sees all users (and filters by project_id if provided)
        // Project Admin only sees users in their project (enforced above)
        
        $users = $query->paginate(20)->withQueryString();
        
        // For the filter dropdown:
        // Super Admin sees all projects.
        // Project Admin sees ONLY their project.
        $projectsQuery = Project::orderBy('name');
        if ($current->isProjectAdmin() && $current->active_project_id) {
            $projectsQuery->where('id', $current->active_project_id);
        }
        $projects = $projectsQuery->get();

        return view('admin.users.index', compact('users', 'projects'));
    }

    public function create(Request $request): View
    {
        $current = $request->user();

        $projectsQuery = Project::orderBy('name');
        if ($current->isProjectAdmin() && $current->active_project_id) {
            $projectsQuery->where('id', $current->active_project_id);
        }

        $projects = $projectsQuery->get();

        return view('admin.users.create', compact('projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $current = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:ADMIN,GUARD'],
            'active_project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            // Profil dasar
            'nip' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'join_date' => ['nullable', 'date'],
            'contract_period' => ['nullable', 'string', 'max:255'],
            'employment_status' => ['nullable', 'string', 'max:255'],
            'ktp_number' => ['nullable', 'string', 'max:255'],
            // Pendidikan Satpam
            'satpam_qualification' => ['nullable', 'string', 'max:255'],
            'satpam_training_date' => ['nullable', 'date'],
            'satpam_training_institution' => ['nullable', 'string', 'max:255'],
            'satpam_training_location' => ['nullable', 'string', 'max:255'],
            'satpam_kta_number' => ['nullable', 'string', 'max:255'],
            'satpam_certificate_number' => ['nullable', 'string', 'max:255'],
            // Pendidikan akademis
            'education_level' => ['nullable', 'string', 'max:255'],
            'education_graduation_year' => ['nullable', 'string', 'max:10'],
            'education_school_name' => ['nullable', 'string', 'max:255'],
            'education_city' => ['nullable', 'string', 'max:255'],
            'education_major' => ['nullable', 'string', 'max:255'],
            // BOD
            'birth_city' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer'],
            'gender' => ['nullable', 'string', 'max:50'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            // Postur & seragam
            'height_cm' => ['nullable', 'integer'],
            'weight_kg' => ['nullable', 'integer'],
            'uniform_shirt_size' => ['nullable', 'string', 'max:50'],
            'uniform_pants_size' => ['nullable', 'string', 'max:50'],
            'uniform_shoes_size' => ['nullable', 'string', 'max:50'],
            // Kontak darurat
            'emergency_phone' => ['nullable', 'string', 'max:50'],
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_relation' => ['nullable', 'string', 'max:255'],
            // Identitas
            'npwp' => ['nullable', 'string', 'max:50'],
            'sim_c_number' => ['nullable', 'string', 'max:50'],
            'sim_a_number' => ['nullable', 'string', 'max:50'],
            'bpjs_tk_number' => ['nullable', 'string', 'max:50'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:50'],
            'kk_number' => ['nullable', 'string', 'max:50'],
            // Alamat KTP
            'address_province' => ['nullable', 'string', 'max:255'],
            'address_regency' => ['nullable', 'string', 'max:255'],
            'address_district' => ['nullable', 'string', 'max:255'],
            'address_subdistrict' => ['nullable', 'string', 'max:255'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_rt' => ['nullable', 'string', 'max:10'],
            'address_rw' => ['nullable', 'string', 'max:10'],
            'address_postal_code' => ['nullable', 'string', 'max:20'],
            // Domisili
            'domicile_province' => ['nullable', 'string', 'max:255'],
            'domicile_regency' => ['nullable', 'string', 'max:255'],
            'domicile_district' => ['nullable', 'string', 'max:255'],
            'domicile_subdistrict' => ['nullable', 'string', 'max:255'],
            'domicile_street' => ['nullable', 'string', 'max:255'],
            'domicile_rt' => ['nullable', 'string', 'max:10'],
            'domicile_rw' => ['nullable', 'string', 'max:10'],
            'domicile_postal_code' => ['nullable', 'string', 'max:20'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'children_count' => ['nullable', 'integer'],
            // Pengalaman kerja & sertifikasi
            'exp1_year' => ['nullable', 'string', 'max:10'],
            'exp1_position' => ['nullable', 'string', 'max:255'],
            'exp1_company' => ['nullable', 'string', 'max:255'],
            'exp1_city' => ['nullable', 'string', 'max:255'],
            'exp2_year' => ['nullable', 'string', 'max:10'],
            'exp2_position' => ['nullable', 'string', 'max:255'],
            'exp2_company' => ['nullable', 'string', 'max:255'],
            'exp2_city' => ['nullable', 'string', 'max:255'],
            'exp3_year' => ['nullable', 'string', 'max:10'],
            'exp3_position' => ['nullable', 'string', 'max:255'],
            'exp3_company' => ['nullable', 'string', 'max:255'],
            'exp3_city' => ['nullable', 'string', 'max:255'],
            'cert1_date' => ['nullable', 'date'],
            'cert1_training' => ['nullable', 'string', 'max:255'],
            'cert1_organizer' => ['nullable', 'string', 'max:255'],
            'cert1_city' => ['nullable', 'string', 'max:255'],
            'cert2_date' => ['nullable', 'date'],
            'cert2_training' => ['nullable', 'string', 'max:255'],
            'cert2_organizer' => ['nullable', 'string', 'max:255'],
            'cert2_city' => ['nullable', 'string', 'max:255'],
            'cert3_date' => ['nullable', 'date'],
            'cert3_training' => ['nullable', 'string', 'max:255'],
            'cert3_organizer' => ['nullable', 'string', 'max:255'],
            'cert3_city' => ['nullable', 'string', 'max:255'],
            // Sosial media
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
        ]);

        if ($current->isProjectAdmin() && $current->active_project_id) {
            $data['active_project_id'] = $current->active_project_id;
        }

        $userData = Arr::only($data, [
            'name',
            'username',
            'email',
            'password',
            'role',
            'active_project_id',
        ]);
        $profileData = Arr::except($data, array_keys($userData));

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        }

        $userData['password'] = bcrypt($userData['password']);

        $user = User::create($userData);

        if (! empty($profileData)) {
            $profileData['user_id'] = $user->id;
            if ($profilePhotoPath !== null) {
                $profileData['profile_photo_path'] = $profilePhotoPath;
            }
            UserProfile::create($profileData);
        }

        return redirect()->route('admin.users.index')->with('status', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): View
    {
        $current = $request->user();

        if ($current->isProjectAdmin() && $current->active_project_id && $user->active_project_id !== $current->active_project_id) {
            abort(403);
        }

        $projectsQuery = Project::orderBy('name');
        if ($current->isProjectAdmin() && $current->active_project_id) {
            $projectsQuery->where('id', $current->active_project_id);
        }

        $projects = $projectsQuery->get();

        return view('admin.users.edit', compact('user', 'projects'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $current = $request->user();

        if ($current->isProjectAdmin() && $current->active_project_id && $user->active_project_id !== $current->active_project_id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:ADMIN,GUARD'],
            'active_project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            // Profil dasar
            'nip' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'join_date' => ['nullable', 'date'],
            'contract_period' => ['nullable', 'string', 'max:255'],
            'employment_status' => ['nullable', 'string', 'max:255'],
            'ktp_number' => ['nullable', 'string', 'max:255'],
            // Pendidikan Satpam
            'satpam_qualification' => ['nullable', 'string', 'max:255'],
            'satpam_training_date' => ['nullable', 'date'],
            'satpam_training_institution' => ['nullable', 'string', 'max:255'],
            'satpam_training_location' => ['nullable', 'string', 'max:255'],
            'satpam_kta_number' => ['nullable', 'string', 'max:255'],
            'satpam_certificate_number' => ['nullable', 'string', 'max:255'],
            // Pendidikan akademis
            'education_level' => ['nullable', 'string', 'max:255'],
            'education_graduation_year' => ['nullable', 'string', 'max:10'],
            'education_school_name' => ['nullable', 'string', 'max:255'],
            'education_city' => ['nullable', 'string', 'max:255'],
            'education_major' => ['nullable', 'string', 'max:255'],
            // BOD
            'birth_city' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer'],
            'gender' => ['nullable', 'string', 'max:50'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            // Postur & seragam
            'height_cm' => ['nullable', 'integer'],
            'weight_kg' => ['nullable', 'integer'],
            'uniform_shirt_size' => ['nullable', 'string', 'max:50'],
            'uniform_pants_size' => ['nullable', 'string', 'max:50'],
            'uniform_shoes_size' => ['nullable', 'string', 'max:50'],
            // Kontak darurat
            'emergency_phone' => ['nullable', 'string', 'max:50'],
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_relation' => ['nullable', 'string', 'max:255'],
            // Identitas
            'npwp' => ['nullable', 'string', 'max:50'],
            'sim_c_number' => ['nullable', 'string', 'max:50'],
            'sim_a_number' => ['nullable', 'string', 'max:50'],
            'bpjs_tk_number' => ['nullable', 'string', 'max:50'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:50'],
            'kk_number' => ['nullable', 'string', 'max:50'],
            // Alamat KTP
            'address_province' => ['nullable', 'string', 'max:255'],
            'address_regency' => ['nullable', 'string', 'max:255'],
            'address_district' => ['nullable', 'string', 'max:255'],
            'address_subdistrict' => ['nullable', 'string', 'max:255'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_rt' => ['nullable', 'string', 'max:10'],
            'address_rw' => ['nullable', 'string', 'max:10'],
            'address_postal_code' => ['nullable', 'string', 'max:20'],
            // Domisili
            'domicile_province' => ['nullable', 'string', 'max:255'],
            'domicile_regency' => ['nullable', 'string', 'max:255'],
            'domicile_district' => ['nullable', 'string', 'max:255'],
            'domicile_subdistrict' => ['nullable', 'string', 'max:255'],
            'domicile_street' => ['nullable', 'string', 'max:255'],
            'domicile_rt' => ['nullable', 'string', 'max:10'],
            'domicile_rw' => ['nullable', 'string', 'max:10'],
            'domicile_postal_code' => ['nullable', 'string', 'max:20'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'children_count' => ['nullable', 'integer'],
            // Pengalaman kerja & sertifikasi
            'exp1_year' => ['nullable', 'string', 'max:10'],
            'exp1_position' => ['nullable', 'string', 'max:255'],
            'exp1_company' => ['nullable', 'string', 'max:255'],
            'exp1_city' => ['nullable', 'string', 'max:255'],
            'exp2_year' => ['nullable', 'string', 'max:10'],
            'exp2_position' => ['nullable', 'string', 'max:255'],
            'exp2_company' => ['nullable', 'string', 'max:255'],
            'exp2_city' => ['nullable', 'string', 'max:255'],
            'exp3_year' => ['nullable', 'string', 'max:10'],
            'exp3_position' => ['nullable', 'string', 'max:255'],
            'exp3_company' => ['nullable', 'string', 'max:255'],
            'exp3_city' => ['nullable', 'string', 'max:255'],
            'cert1_date' => ['nullable', 'date'],
            'cert1_training' => ['nullable', 'string', 'max:255'],
            'cert1_organizer' => ['nullable', 'string', 'max:255'],
            'cert1_city' => ['nullable', 'string', 'max:255'],
            'cert2_date' => ['nullable', 'date'],
            'cert2_training' => ['nullable', 'string', 'max:255'],
            'cert2_organizer' => ['nullable', 'string', 'max:255'],
            'cert2_city' => ['nullable', 'string', 'max:255'],
            'cert3_date' => ['nullable', 'date'],
            'cert3_training' => ['nullable', 'string', 'max:255'],
            'cert3_organizer' => ['nullable', 'string', 'max:255'],
            'cert3_city' => ['nullable', 'string', 'max:255'],
            // Sosial media
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
        ]);

        if ($current->isProjectAdmin() && $current->active_project_id) {
            $data['active_project_id'] = $current->active_project_id;
        }

        $userData = Arr::only($data, [
            'name',
            'username',
            'email',
            'password',
            'role',
            'active_project_id',
        ]);
        $profileData = Arr::except($data, array_keys($userData));

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        }

        if (! empty($userData['password'])) {
            $userData['password'] = bcrypt($userData['password']);
        } else {
            unset($userData['password']);
        }

        $user->update($userData);

        if (! empty($profileData) || $profilePhotoPath !== null) {
            if ($profilePhotoPath !== null) {
                $profileData['profile_photo_path'] = $profilePhotoPath;
            }

            $user->profile()->updateOrCreate([], $profileData);
        }

        return redirect()->route('admin.users.index')->with('status', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $current = auth()->user();

        if ($current && $current->isProjectAdmin() && $current->active_project_id && $user->active_project_id !== $current->active_project_id) {
            abort(403);
        }

        if (auth()->id() === $user->id) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus akun yang sedang digunakan.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Karyawan berhasil dihapus.');
    }

    public function showImportForm(Request $request): View
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.users.import');
    }

    public function import(Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new UserImport(), $data['file']);

        return redirect()->route('admin.users.index')->with('status', 'Import karyawan berhasil diproses.');
    }

    public function downloadImportTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new UserImportTemplateExport(), 'user_import_template.xlsx');
    }
}
