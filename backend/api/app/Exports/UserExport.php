<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class UserExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::query()->with(['activeProject', 'profile']);

        $user = $this->request ? $this->request->user() : auth()->user();
        
        if ($user) {
            $accessibleProjectIds = $user->getAccessibleProjectIds();
            $isSuperAdmin = $user->isSuperAdmin();

            if (!$isSuperAdmin) {
                if (!empty($accessibleProjectIds)) {
                    $query->whereIn('active_project_id', $accessibleProjectIds);
                } else {
                    $query->whereRaw('1 = 0'); // No access
                }
            }
        }

        // Apply filters from request if available
        if ($this->request) {
            if ($this->request->filled('role')) {
                $query->where('role', $this->request->role);
            }

            if ($this->request->filled('project_id')) {
                $query->where('active_project_id', $this->request->project_id);
            }

            if ($this->request->filled('search')) {
                $search = $this->request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                      ->orWhere('username', 'like', '%'.$search.'%');
                });
            }

            if ($this->request->boolean('sort_by_project')) {
                $query->orderBy('active_project_id');
            }
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Username',
            'Email',
            'Role',
            'Project',
            'NIP',
            'Posisi',
            'Divisi',
            'Tanggal Bergabung',
            'Status Karyawan',
            'No. KTP',
            'Kualifikasi Satpam',
            'Tanggal Pelatihan Satpam',
            'No. KTA',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'No. Telepon',
            'Email Pribadi',
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->username,
            $user->email,
            $user->role,
            $user->activeProject?->name ?? '-',
            $user->profile?->nip ?? '-',
            $user->profile?->position ?? '-',
            $user->profile?->division ?? '-',
            $user->profile?->join_date ?? '-',
            $user->profile?->employment_status ?? '-',
            $user->profile?->ktp_number ?? '-',
            $user->profile?->satpam_qualification ?? '-',
            $user->profile?->satpam_training_date ?? '-',
            $user->profile?->satpam_kta_number ?? '-',
            $user->profile?->birth_city ?? '-',
            $user->profile?->birth_date ?? '-',
            $user->profile?->gender ?? '-',
            $user->profile?->phone_number ?? '-',
            $user->profile?->personal_email ?? '-',
        ];
    }
}
