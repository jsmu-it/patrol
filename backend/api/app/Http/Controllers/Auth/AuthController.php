<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->input('username'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function updateDeviceToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->fcm_token = $data['fcm_token'];
        $user->save();

        return response()->json([
            'message' => 'Device token updated.',
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        $data = $request->validate([
            'profile_photo' => ['nullable', 'image', 'max:10240'],
            'active_project_id' => ['nullable', 'exists:projects,id'],
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            
            if ($user->profile) {
                $user->profile->update(['profile_photo_path' => $path]);
            } else {
                $user->profile()->create(['profile_photo_path' => $path]);
            }
        }

        if (isset($data['active_project_id'])) {
            $user->active_project_id = $data['active_project_id'];
            $user->save();
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
            'new_password_confirmation' => ['required', 'string', 'same:new_password'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->password = $data['new_password'];
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ]);
    }

    public function getCV(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'cv' => [
                    'personal' => [],
                    'contact' => [],
                    'employment' => [],
                    'satpam' => [],
                    'education' => [],
                    'physical' => [],
                    'documents' => [],
                    'address' => [],
                    'domicile' => [],
                    'experience' => [],
                    'certifications' => [],
                    'social_media' => [],
                ],
            ]);
        }

        return response()->json([
            'cv' => [
                'personal' => [
                    'name' => $user->name,
                    'nip' => $profile->nip,
                    'position' => $profile->position,
                    'division' => $profile->division,
                    'ktp_number' => $profile->ktp_number,
                    'birth_city' => $profile->birth_city,
                    'birth_date' => $profile->birth_date?->format('Y-m-d'),
                    'age' => $profile->age,
                    'gender' => $profile->gender,
                    'religion' => $profile->religion,
                    'blood_type' => $profile->blood_type,
                    'marital_status' => $profile->marital_status,
                    'children_count' => $profile->children_count,
                    'mother_name' => $profile->mother_name,
                ],
                'contact' => [
                    'phone_number' => $profile->phone_number,
                    'personal_email' => $profile->personal_email,
                    'emergency_name' => $profile->emergency_name,
                    'emergency_phone' => $profile->emergency_phone,
                    'emergency_relation' => $profile->emergency_relation,
                ],
                'employment' => [
                    'join_date' => $profile->join_date?->format('Y-m-d'),
                    'contract_period' => $profile->contract_period,
                    'employment_status' => $profile->employment_status,
                    'salary' => $profile->salary,
                ],
                'satpam' => [
                    'qualification' => $profile->satpam_qualification,
                    'training_date' => $profile->satpam_training_date?->format('Y-m-d'),
                    'training_institution' => $profile->satpam_training_institution,
                    'training_location' => $profile->satpam_training_location,
                    'kta_number' => $profile->satpam_kta_number,
                    'certificate_number' => $profile->satpam_certificate_number,
                ],
                'education' => [
                    'level' => $profile->education_level,
                    'graduation_year' => $profile->education_graduation_year,
                    'school_name' => $profile->education_school_name,
                    'city' => $profile->education_city,
                    'major' => $profile->education_major,
                ],
                'physical' => [
                    'height_cm' => $profile->height_cm,
                    'weight_kg' => $profile->weight_kg,
                    'uniform_shirt_size' => $profile->uniform_shirt_size,
                    'uniform_pants_size' => $profile->uniform_pants_size,
                    'uniform_shoes_size' => $profile->uniform_shoes_size,
                ],
                'documents' => [
                    'npwp' => $profile->npwp,
                    'sim_c_number' => $profile->sim_c_number,
                    'sim_a_number' => $profile->sim_a_number,
                    'bpjs_tk_number' => $profile->bpjs_tk_number,
                    'bpjs_kes_number' => $profile->bpjs_kes_number,
                    'kk_number' => $profile->kk_number,
                ],
                'address' => [
                    'province' => $profile->address_province,
                    'regency' => $profile->address_regency,
                    'district' => $profile->address_district,
                    'subdistrict' => $profile->address_subdistrict,
                    'street' => $profile->address_street,
                    'rt' => $profile->address_rt,
                    'rw' => $profile->address_rw,
                    'postal_code' => $profile->address_postal_code,
                ],
                'domicile' => [
                    'province' => $profile->domicile_province,
                    'regency' => $profile->domicile_regency,
                    'district' => $profile->domicile_district,
                    'subdistrict' => $profile->domicile_subdistrict,
                    'street' => $profile->domicile_street,
                    'rt' => $profile->domicile_rt,
                    'rw' => $profile->domicile_rw,
                    'postal_code' => $profile->domicile_postal_code,
                ],
                'experience' => [
                    [
                        'year' => $profile->exp1_year,
                        'position' => $profile->exp1_position,
                        'company' => $profile->exp1_company,
                        'city' => $profile->exp1_city,
                    ],
                    [
                        'year' => $profile->exp2_year,
                        'position' => $profile->exp2_position,
                        'company' => $profile->exp2_company,
                        'city' => $profile->exp2_city,
                    ],
                    [
                        'year' => $profile->exp3_year,
                        'position' => $profile->exp3_position,
                        'company' => $profile->exp3_company,
                        'city' => $profile->exp3_city,
                    ],
                ],
                'certifications' => [
                    [
                        'date' => $profile->cert1_date?->format('Y-m-d'),
                        'training' => $profile->cert1_training,
                        'organizer' => $profile->cert1_organizer,
                        'city' => $profile->cert1_city,
                    ],
                    [
                        'date' => $profile->cert2_date?->format('Y-m-d'),
                        'training' => $profile->cert2_training,
                        'organizer' => $profile->cert2_organizer,
                        'city' => $profile->cert2_city,
                    ],
                    [
                        'date' => $profile->cert3_date?->format('Y-m-d'),
                        'training' => $profile->cert3_training,
                        'organizer' => $profile->cert3_organizer,
                        'city' => $profile->cert3_city,
                    ],
                ],
                'social_media' => [
                    'instagram' => $profile->instagram,
                    'facebook' => $profile->facebook,
                    'twitter' => $profile->twitter,
                    'tiktok' => $profile->tiktok,
                    'linkedin' => $profile->linkedin,
                    'youtube' => $profile->youtube,
                ],
            ],
        ]);
    }

    /**
     * Get list of users who can be supervisors (admin roles)
     */
    public function getSupervisorOptions(): JsonResponse
    {
        $supervisors = User::whereIn('role', [
            User::ROLE_ADMIN,
            User::ROLE_SUPERADMIN,
            User::ROLE_PROJECT_ADMIN,
        ])
        ->select('id', 'name', 'role')
        ->orderBy('name')
        ->get();

        return response()->json($supervisors);
    }

    /**
     * Update user's supervisor
     */
    public function updateSupervisor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'supervisor_id' => ['nullable', 'exists:users,id'],
        ]);

        /** @var User $user */
        $user = $request->user();

        // Prevent self-supervision
        if (isset($data['supervisor_id']) && $data['supervisor_id'] == $user->id) {
            return response()->json([
                'message' => 'Tidak bisa memilih diri sendiri sebagai atasan.',
            ], 422);
        }

        $user->supervisor_id = $data['supervisor_id'];
        $user->save();

        return response()->json([
            'message' => 'Atasan langsung berhasil diperbarui.',
            'user' => new UserResource($user->fresh(['supervisor'])),
        ]);
    }
}
