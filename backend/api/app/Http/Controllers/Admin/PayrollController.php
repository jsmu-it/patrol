<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PayrollTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PayrollImport;
use App\Models\PayrollSlip;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollSlip::query();

        // Filter by period
        if ($request->filled('period')) {
            $query->where('period_month', $request->period);
        }

        // Filter by unit
        if ($request->filled('unit')) {
            $query->where('unit', 'like', '%' . $request->unit . '%');
        }

        // Search by name or NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('nip', 'like', '%' . $search . '%');
            });
        }

        // Get all IDs matching current filter (for select all feature)
        $allIds = (clone $query)->pluck('id')->toArray();

        $slips = $query->orderBy('name')->paginate(20)->withQueryString();

        // Get unique periods for filter
        $periods = PayrollSlip::distinct()->pluck('period_month')->sort()->reverse();

        // Get unique units for filter
        $units = PayrollSlip::distinct()->whereNotNull('unit')->pluck('unit')->sort();

        return view('admin.payroll.index', compact('slips', 'periods', 'units', 'allIds'));
    }

    public function showImportForm()
    {
        return view('admin.payroll.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'period_month' => 'required|string|max:50',
            'sign_location' => 'required|string|max:100',
            'sign_date' => 'required|date',
        ]);

        try {
            Excel::import(
                new PayrollImport(
                    $request->period_month,
                    $request->sign_location,
                    $request->sign_date
                ),
                $request->file('file')
            );

            return redirect()->route('admin.payroll.index', ['period' => $request->period_month])
                ->with('status', 'Data payroll berhasil diimport.');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal import: ' . $e->getMessage()]);
        }
    }

    public function show(PayrollSlip $slip)
    {
        $slip->load(['incomeItems', 'deductionItems']);

        return view('admin.payroll.show', compact('slip'));
    }

    public function print(PayrollSlip $slip)
    {
        $slip->load(['incomeItems', 'deductionItems']);

        return view('admin.payroll.print', compact('slip'));
    }

    public function printBulk(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->withErrors(['ids' => 'Pilih minimal satu slip gaji.']);
        }

        $slips = PayrollSlip::whereIn('id', $ids)
            ->with(['incomeItems', 'deductionItems'])
            ->orderBy('name')
            ->get();

        return view('admin.payroll.print-bulk', compact('slips'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new PayrollTemplateExport(), 'payroll_template.xlsx');
    }

    public function destroy(PayrollSlip $slip)
    {
        $slip->delete();

        return back()->with('status', 'Slip gaji berhasil dihapus.');
    }

    public function destroyPeriod(Request $request)
    {
        $request->validate([
            'period' => 'required|string',
        ]);

        PayrollSlip::where('period_month', $request->period)->delete();

        return redirect()->route('admin.payroll.index')
            ->with('status', 'Semua slip gaji periode ' . $request->period . ' berhasil dihapus.');
    }

    public function send(PayrollSlip $slip, PushNotificationService $pushService)
    {
        $profile = UserProfile::where('nip', $slip->nip)->first();

        if (! $profile) {
            return back()->with('error', 'Karyawan dengan NIP ' . $slip->nip . ' belum masuk database.');
        }

        $user = $profile->user;

        if (! $user) {
            return back()->with('error', 'User untuk NIP ' . $slip->nip . ' tidak ditemukan.');
        }

        // Link payroll slip to user if not already linked
        if (! $slip->user_id) {
            $slip->update(['user_id' => $user->id]);
        }

        // Send push notification
        $pushService->notifyUser(
            $user,
            'Slip Gaji ' . $slip->period_month,
            'Slip gaji Anda untuk periode ' . $slip->period_month . ' sudah tersedia.',
            [
                'type' => 'payroll',
                'payroll_slip_id' => (string) $slip->id,
                'period' => $slip->period_month,
            ]
        );

        return back()->with('status', 'Slip gaji berhasil dikirim ke ' . $user->name . '.');
    }

    public function sendBulk(Request $request, PushNotificationService $pushService)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu slip gaji.');
        }

        $slips = PayrollSlip::whereIn('id', $ids)->get();

        $sent = 0;
        $notFound = [];

        foreach ($slips as $slip) {
            $profile = UserProfile::where('nip', $slip->nip)->first();

            if (! $profile || ! $profile->user) {
                $notFound[] = $slip->nip . ' (' . $slip->name . ')';
                continue;
            }

            $user = $profile->user;

            // Link payroll slip to user if not already linked
            if (! $slip->user_id) {
                $slip->update(['user_id' => $user->id]);
            }

            // Send push notification
            $pushService->notifyUser(
                $user,
                'Slip Gaji ' . $slip->period_month,
                'Slip gaji Anda untuk periode ' . $slip->period_month . ' sudah tersedia.',
                [
                    'type' => 'payroll',
                    'payroll_slip_id' => (string) $slip->id,
                    'period' => $slip->period_month,
                ]
            );

            $sent++;
        }

        $message = $sent . ' slip gaji berhasil dikirim.';

        if (! empty($notFound)) {
            $message .= ' ' . count($notFound) . ' karyawan belum masuk database: ' . implode(', ', array_slice($notFound, 0, 5));
            if (count($notFound) > 5) {
                $message .= ', dan ' . (count($notFound) - 5) . ' lainnya.';
            }
        }

        return back()->with($sent > 0 ? 'status' : 'error', $message);
    }
}
