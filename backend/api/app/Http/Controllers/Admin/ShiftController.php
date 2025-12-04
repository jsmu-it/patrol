<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::orderBy('name')->paginate(20);
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Ensure start_time and end_time have proper format if needed, or just save as is
        // Assuming database uses TIME format which handles H:i:s

        $validated['code'] = strtoupper(Str::slug($validated['name']) . '-' . Str::random(4));

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')->with('status', 'Shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift): View
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')->with('status', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        // Optional: Check if shift is used in projects or logs before deleting
        if ($shift->projects()->exists() || $shift->attendanceLogs()->exists()) {
            return back()->withErrors(['Shift sedang digunakan dan tidak dapat dihapus.']);
        }

        $shift->delete();

        return redirect()->route('admin.shifts.index')->with('status', 'Shift berhasil dihapus.');
    }
}
