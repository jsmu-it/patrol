<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\PkwtDeduction;
use App\Models\PkwtDeductionType;
use App\Models\PkwtIncome;
use App\Models\PkwtIncomeType;
use App\Models\PkwtRecord;
use App\Models\Position;
use App\Models\Project;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PkwtController extends Controller
{
    public function index(Request $request): View
    {
        $query = PkwtRecord::with([
            'position',
            'project',
            'leaveType',
            'incomes.incomeType',
            'deductions.deductionType',
        ])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('pkwt_number', 'like', "%{$search}%")
                  ->orWhere('ktp_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $pkwtRecords = $query->paginate(20)->withQueryString();

        // Get dropdown data
        $positions = Position::active()->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $incomeTypes = PkwtIncomeType::ordered()->get();
        $deductionTypes = PkwtDeductionType::ordered()->get();
        $statuses = PkwtRecord::getStatuses();

        return view('admin.pkwt.index', compact(
            'pkwtRecords',
            'positions',
            'projects',
            'leaveTypes',
            'incomeTypes',
            'deductionTypes',
            'statuses'
        ));
    }

    public function update(Request $request, PkwtRecord $pkwt): RedirectResponse
    {
        $request->validate([
            'position_id' => 'nullable|exists:positions,id',
            'project_id' => 'nullable|exists:projects,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after_or_equal:contract_start',
            'status' => 'nullable|in:' . implode(',', array_keys(PkwtRecord::getStatuses())),
            'incomes' => 'nullable|array',
            'incomes.*' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|array',
            'deductions.*' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $pkwt) {
            // Update main record
            $pkwt->update($request->only([
                'position_id',
                'project_id',
                'leave_type_id',
                'contract_start',
                'contract_end',
                'status',
            ]));

            // Update incomes
            if ($request->has('incomes')) {
                foreach ($request->incomes as $typeId => $amount) {
                    PkwtIncome::updateOrCreate(
                        ['pkwt_record_id' => $pkwt->id, 'pkwt_income_type_id' => $typeId],
                        ['amount' => $amount ?? 0]
                    );
                }
            }

            // Update deductions
            if ($request->has('deductions')) {
                foreach ($request->deductions as $typeId => $amount) {
                    PkwtDeduction::updateOrCreate(
                        ['pkwt_record_id' => $pkwt->id, 'pkwt_deduction_type_id' => $typeId],
                        ['amount' => $amount ?? 0]
                    );
                }
            }

            // If status changed to active, create user
            if ($request->status === PkwtRecord::STATUS_ACTIVE && !$pkwt->user_id) {
                $this->createUserFromPkwt($pkwt);
            }
        });

        return back()->with('status', 'Data PKWT berhasil diperbarui.');
    }

    public function activate(PkwtRecord $pkwt): RedirectResponse
    {
        if ($pkwt->status === PkwtRecord::STATUS_ACTIVE) {
            return back()->with('error', 'PKWT sudah aktif.');
        }

        DB::transaction(function () use ($pkwt) {
            $this->createUserFromPkwt($pkwt);
            
            $pkwt->update([
                'status' => PkwtRecord::STATUS_ACTIVE,
                'activated_at' => now(),
            ]);
        });

        return back()->with('status', 'PKWT berhasil diaktifkan. User karyawan telah dibuat.');
    }

    public function preview(PkwtRecord $pkwt): View
    {
        $pkwt->load(['position', 'project', 'leaveType', 'incomes.incomeType', 'deductions.deductionType']);
        
        // Get PKWT template from project
        $template = $pkwt->project?->pkwt_template ?? '';
        
        // Replace placeholders
        $template = $this->replacePlaceholders($template, $pkwt);

        return view('admin.pkwt.preview', compact('pkwt', 'template'));
    }

    public function send(PkwtRecord $pkwt): RedirectResponse
    {
        // TODO: Send email/notification to employee
        $pkwt->update([
            'status' => PkwtRecord::STATUS_SENT,
            'sent_at' => now(),
        ]);

        return back()->with('status', 'PKWT berhasil dikirim ke karyawan.');
    }

    public function print(PkwtRecord $pkwt): View
    {
        $pkwt->load(['position', 'project', 'leaveType', 'incomes.incomeType', 'deductions.deductionType']);
        
        $template = $pkwt->project?->pkwt_template ?? '';
        $template = $this->replacePlaceholders($template, $pkwt);

        return view('admin.pkwt.print', compact('pkwt', 'template'));
    }

    public function destroy(PkwtRecord $pkwt): RedirectResponse
    {
        if ($pkwt->user_id) {
            return back()->with('error', 'Tidak dapat menghapus PKWT yang sudah memiliki user aktif.');
        }

        $pkwt->delete();

        return back()->with('status', 'Data PKWT berhasil dihapus.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pkwt_records,id',
        ]);

        $deleted = PkwtRecord::whereIn('id', $request->ids)
            ->whereNull('user_id')
            ->delete();

        return back()->with('status', "{$deleted} data PKWT berhasil dihapus.");
    }

    // Store new income type
    public function storeIncomeType(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $code = \Str::slug($request->name, '_');
        $maxOrder = PkwtIncomeType::max('sort_order') ?? 0;

        PkwtIncomeType::create([
            'name' => $request->name,
            'code' => $code,
            'is_default' => false,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('status', 'Jenis pendapatan berhasil ditambahkan.');
    }

    // Store new deduction type
    public function storeDeductionType(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $code = \Str::slug($request->name, '_');
        $maxOrder = PkwtDeductionType::max('sort_order') ?? 0;

        PkwtDeductionType::create([
            'name' => $request->name,
            'code' => $code,
            'is_default' => false,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('status', 'Jenis potongan berhasil ditambahkan.');
    }

    // Store new position
    public function storePosition(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Position::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return back()->with('status', 'Jabatan berhasil ditambahkan.');
    }

    private function createUserFromPkwt(PkwtRecord $pkwt): void
    {
        // Load job application for additional data
        $application = $pkwt->jobApplication;
        
        // Generate NIP from PKWT number
        $nip = 'EMP' . $pkwt->pkwt_number;

        // Create User
        $user = User::create([
            'name' => $pkwt->name,
            'username' => $nip,
            'email' => $pkwt->email,
            'password' => $nip, // Default password = NIP (model cast handles hashing)
            'role' => 'GUARD',
            'active_project_id' => $pkwt->project_id,
        ]);

        // Create User Profile
        $profileData = [
            'user_id' => $user->id,
            'nip' => $nip,
            'salary' => $pkwt->total_income,
            'join_date' => $pkwt->contract_start,
            'personal_email' => $pkwt->email,
            'phone_number' => $pkwt->phone,
            'position' => $pkwt->position?->name ?? 'Security Guard',
            'ktp_number' => $pkwt->ktp_number,
            'birth_city' => $pkwt->birth_place,
            'birth_date' => $pkwt->birth_date,
            'gender' => $pkwt->gender,
        ];

        // Copy additional fields from job application if available
        if ($application) {
            $additionalFields = [
                'kk_number', 'age', 'religion', 'blood_type', 'mother_name',
                'marital_status', 'height_cm', 'weight_kg',
                'address_street', 'address_rt', 'address_rw', 'address_subdistrict',
                'address_district', 'address_regency', 'address_province', 'address_postal_code',
                'domicile_street', 'domicile_rt', 'domicile_rw', 'domicile_subdistrict',
                'domicile_district', 'domicile_regency', 'domicile_province', 'domicile_postal_code',
                'education_level', 'education_school_name', 'education_major', 'education_graduation_year',
                'education_city', 'satpam_qualification', 'satpam_kta_number', 'satpam_certificate_number',
                'satpam_training_date', 'satpam_training_institution', 'satpam_training_location',
                'npwp', 'sim_a_number', 'sim_c_number', 'bpjs_tk_number', 'bpjs_kes_number',
                'uniform_shirt_size', 'uniform_pants_size', 'uniform_shoes_size',
                'emergency_name', 'emergency_phone', 'emergency_relation', 'children_count',
                'exp1_company', 'exp1_position', 'exp1_year', 'exp1_city',
                'exp2_company', 'exp2_position', 'exp2_year', 'exp2_city',
                'cert1_training', 'cert1_organizer', 'cert1_date', 'cert1_city',
                'cert2_training', 'cert2_organizer', 'cert2_date', 'cert2_city',
                'instagram', 'facebook', 'twitter', 'tiktok', 'linkedin',
            ];

            foreach ($additionalFields as $field) {
                if (!empty($application->$field)) {
                    $profileData[$field] = $application->$field;
                }
            }
        }

        UserProfile::create($profileData);

        // Link user to PKWT
        $pkwt->update(['user_id' => $user->id]);
    }

    private function replacePlaceholders(string $template, PkwtRecord $pkwt): string
    {
        $replacements = [
            '{{nama}}' => $pkwt->name,
            '{{ktp}}' => $pkwt->ktp_number,
            '{{alamat}}' => $pkwt->address,
            '{{email}}' => $pkwt->email,
            '{{jabatan}}' => $pkwt->position?->name ?? '-',
            '{{unit}}' => $pkwt->project?->name ?? '-',
            '{{tanggal_mulai}}' => $pkwt->contract_start?->format('d F Y') ?? '-',
            '{{tanggal_akhir}}' => $pkwt->contract_end?->format('d F Y') ?? '-',
            '{{gaji_pokok}}' => number_format($pkwt->total_income, 0, ',', '.'),
            '{{tanggal_lahir}}' => $pkwt->birth_date?->format('d F Y') ?? '-',
            '{{tempat_lahir}}' => $pkwt->birth_place ?? '-',
            '{{pkwt_number}}' => $pkwt->pkwt_number,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
