<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    private const MAKS_SEKALIGUS = 500;

    // -------------------------------------------------- absensi dinas

    public function attendance(Request $request): View
    {
        $user      = $request->user();
        $projectId = $request->integer('project_id') ?: null;

        $query = AttendanceLog::with('user', 'project', 'shift')
            ->where('attendance_logs.mode', AttendanceLog::MODE_DINAS)
            ->where('attendance_logs.status_dinas', AttendanceLog::STATUS_DINAS_PENDING);

        $this->batasiProject($query, $user, 'attendance_logs.project_id');

        if ($projectId) {
            $query->where('attendance_logs.project_id', $projectId);
        }

        // Dikelompokkan per project dulu supaya satu lokasi bisa diperiksa
        // sekaligus, baru diurutkan dari yang terbaru di dalamnya.
        $logs = $query
            ->leftJoin('projects', 'projects.id', '=', 'attendance_logs.project_id')
            ->orderBy('projects.name')
            ->orderByDesc('attendance_logs.occurred_at')
            ->select('attendance_logs.*')
            ->paginate(30)
            ->appends($request->only('project_id'));

        return view('admin.approvals.attendance', [
            'logs'       => $logs,
            'projects'   => $this->daftarProject($user),
            'projectId'  => $projectId,
        ]);
    }

    public function approveAttendance(AttendanceLog $attendanceLog): RedirectResponse
    {
        $attendanceLog->update(['status_dinas' => AttendanceLog::STATUS_DINAS_APPROVED]);

        return back()->with('status', 'Absensi dinas disetujui.');
    }

    public function rejectAttendance(AttendanceLog $attendanceLog): RedirectResponse
    {
        $attendanceLog->update(['status_dinas' => AttendanceLog::STATUS_DINAS_REJECTED]);

        return back()->with('status', 'Absensi dinas ditolak.');
    }

    /** Menyetujui atau menolak banyak absensi dinas sekaligus. */
    public function bulkAttendance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['approve', 'reject'])],
            'id'        => ['required', 'array', 'min:1', 'max:' . self::MAKS_SEKALIGUS],
            'id.*'      => ['integer'],
        ], [
            'id.required' => 'Pilih dulu baris yang mau diproses.',
        ]);

        $query = AttendanceLog::whereIn('id', $data['id'])
            ->where('mode', AttendanceLog::MODE_DINAS)
            ->where('status_dinas', AttendanceLog::STATUS_DINAS_PENDING);

        // Penyaring hak akses diulang di sini: daftar id datang dari formulir,
        // jadi tidak boleh dipercaya begitu saja.
        $this->batasiProject($query, $request->user(), 'attendance_logs.project_id');

        $jumlah = $query->update([
            'status_dinas' => $data['keputusan'] === 'approve'
                ? AttendanceLog::STATUS_DINAS_APPROVED
                : AttendanceLog::STATUS_DINAS_REJECTED,
        ]);

        return back()->with('status', $jumlah . ' absensi dinas '
            . ($data['keputusan'] === 'approve' ? 'disetujui.' : 'ditolak.'));
    }

    // -------------------------------------------------- cuti & izin

    public function leave(Request $request): View
    {
        $user      = $request->user();
        $status    = $request->get('status', 'pending');
        $projectId = $request->integer('project_id') ?: null;

        $query = LeaveRequest::with(['user.activeProject', 'approvedBy', 'leaveType']);

        if ($status && $status !== 'all') {
            $query->where('leave_requests.status', $status);
        }

        $this->batasiProjectLewatUser($query, $user);

        if ($projectId) {
            $query->whereHas('user', fn ($q) => $q->where('active_project_id', $projectId));
        }

        // Diurutkan per project seperti halaman absensi dinas.
        $requests = $query
            ->leftJoin('users', 'users.id', '=', 'leave_requests.user_id')
            ->leftJoin('projects', 'projects.id', '=', 'users.active_project_id')
            ->orderBy('projects.name')
            ->orderByDesc('leave_requests.created_at')
            ->select('leave_requests.*')
            ->paginate(30)
            ->appends($request->only('status', 'project_id'));

        return view('admin.approvals.leave', [
            'requests'  => $requests,
            'status'    => $status,
            'projects'  => $this->daftarProject($user),
            'projectId' => $projectId,
        ]);
    }

    public function approveLeave(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $sebelumnya = $leaveRequest->status;
        $leaveRequest->update($this->jejakKeputusan($request->user(), LeaveRequest::STATUS_APPROVED));
        $this->sesuaikanSaldo($leaveRequest, $sebelumnya, LeaveRequest::STATUS_APPROVED);

        return back()->with('status', 'Pengajuan cuti disetujui.');
    }

    public function rejectLeave(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $sebelumnya = $leaveRequest->status;
        $leaveRequest->update($this->jejakKeputusan($request->user(), LeaveRequest::STATUS_REJECTED));
        $this->sesuaikanSaldo($leaveRequest, $sebelumnya, LeaveRequest::STATUS_REJECTED);

        return back()->with('status', 'Pengajuan cuti ditolak.');
    }

    /** Menyetujui atau menolak banyak pengajuan cuti sekaligus. */
    public function bulkLeave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['approve', 'reject'])],
            'id'        => ['required', 'array', 'min:1', 'max:' . self::MAKS_SEKALIGUS],
            'id.*'      => ['integer'],
        ], [
            'id.required' => 'Pilih dulu baris yang mau diproses.',
        ]);

        $query = LeaveRequest::whereIn('id', $data['id'])
            ->where('status', LeaveRequest::STATUS_PENDING);

        $this->batasiProjectLewatUser($query, $request->user());

        $status = $data['keputusan'] === 'approve'
            ? LeaveRequest::STATUS_APPROVED
            : LeaveRequest::STATUS_REJECTED;

        // Diambil dulu: setelah update massal, status lamanya sudah tidak
        // terbaca, padahal saldo hanya boleh bergerak sekali per pengajuan.
        $pengajuan = $query->get();
        $jumlah = $query->update($this->jejakKeputusan($request->user(), $status));

        foreach ($pengajuan as $satu) {
            $this->sesuaikanSaldo($satu->fresh(), LeaveRequest::STATUS_PENDING, $status);
        }

        return back()->with('status', $jumlah . ' pengajuan cuti '
            . ($data['keputusan'] === 'approve' ? 'disetujui.' : 'ditolak.'));
    }

    // -------------------------------------------------------- bantuan

    /**
     * Status beserta jejak siapa yang memutuskan dan kapan. Kolomnya sudah ada
     * dan ditampilkan di kolom "Diproses Oleh", tetapi sebelumnya tidak pernah
     * diisi sehingga selalu tampak kosong.
     */
    private function jejakKeputusan(User $user, string $status): array
    {
        return [
            'status'      => $status,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ];
    }

    /**
     * Menggerakkan saldo cuti mengikuti perubahan status, sekali saja per
     * pengajuan: berkurang saat disetujui, kembali bila persetujuannya dicabut.
     */
    private function sesuaikanSaldo(LeaveRequest $pengajuan, ?string $sebelumnya, string $sesudah): void
    {
        if ($sebelumnya === $sesudah) {
            return;
        }

        // Pengaju diberi tahu hasilnya lewat kotak masuk portal.
        $pengajuan->user?->notify(new \App\Notifications\CutiDiajukan(
            $pengajuan->loadMissing('leaveType'), 'hasil',
        ));

        if ($sesudah === LeaveRequest::STATUS_APPROVED) {
            \App\Services\SaldoCuti::pakai($pengajuan);
        } elseif ($sebelumnya === LeaveRequest::STATUS_APPROVED) {
            \App\Services\SaldoCuti::kembalikan($pengajuan);
        }
    }

    /** Project yang boleh dilihat pengguna ini, untuk isi dropdown penyaring. */
    private function daftarProject(User $user)
    {
        $query = Project::orderBy('name');

        $ids = $user->getAccessibleProjectIds();
        if (! $user->isSuperAdmin() && ! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        return $query->get(['id', 'name']);
    }

    /** @param string $kolom nama kolom lengkap dengan tabelnya, karena query ini di-join */
    private function batasiProject(Builder $query, User $user, string $kolom): void
    {
        $ids = $user->getAccessibleProjectIds();

        if (! $user->isSuperAdmin() && ! empty($ids)) {
            $query->whereIn($kolom, $ids);
        }
    }

    /** Pembatasan untuk data yang project-nya menempel pada penggunanya. */
    private function batasiProjectLewatUser(Builder $query, User $user): void
    {
        $ids = $user->getAccessibleProjectIds();

        if (! $user->isSuperAdmin() && ! empty($ids)) {
            $query->whereHas('user', fn ($q) => $q->whereIn('active_project_id', $ids));
        }
    }
}
