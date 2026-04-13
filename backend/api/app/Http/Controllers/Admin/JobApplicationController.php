<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsCareer;
use App\Models\JobApplication;
use App\Models\PkwtRecord;
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
        return view('admin.hrd.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,interview,accepted,rejected',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request, $application) {
            if ($request->status === 'accepted') {
                // Create PKWT Record instead of User directly
                // User will be created when PKWT status becomes active
                $address = $this->formatAddress($application);
                
                PkwtRecord::create([
                    'job_application_id' => $application->id,
                    'ktp_number' => $application->ktp_number,
                    'name' => $application->name,
                    'gender' => $application->gender,
                    'birth_place' => $application->birth_city,
                    'birth_date' => $application->birth_date,
                    'address' => $address,
                    'email' => $application->email,
                    'phone' => $application->phone,
                    'status' => PkwtRecord::STATUS_DRAFT,
                ]);
            }

            $application->update([
                'status' => $request->status,
                'notes' => $request->notes
            ]);
        });

        // If accepted, redirect to PKWT page
        if ($request->status === 'accepted') {
            return redirect()->route('admin.pkwt.index')
                ->with('status', 'Pelamar diterima. Silakan lengkapi data PKWT.');
        }

        $redirectRoute = $application->status === 'rejected' ? 'admin.hrd.rejected' : 'admin.hrd.applications';

        return redirect()->route($redirectRoute)->with('status', 'Status lamaran berhasil diperbarui.');
    }

    private function formatAddress(JobApplication $application): string
    {
        $parts = array_filter([
            $application->domicile_street,
            $application->domicile_rt ? 'RT ' . $application->domicile_rt : null,
            $application->domicile_rw ? 'RW ' . $application->domicile_rw : null,
            $application->domicile_subdistrict ? 'Kel. ' . $application->domicile_subdistrict : null,
            $application->domicile_district ? 'Kec. ' . $application->domicile_district : null,
            $application->domicile_regency,
            $application->domicile_province,
        ]);

        return implode(', ', $parts);
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
