<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KraepelinQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KraepelinController extends Controller
{
    public function index(): View
    {
        $questions = KraepelinQuestion::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.psikotest.kraepelin.index', compact('questions'));
    }

    public function create(): View
    {
        return view('admin.psikotest.kraepelin.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'column_count' => ['required', 'integer', 'min:10', 'max:100'],
            'rows_per_column' => ['required', 'integer', 'min:20', 'max:100'],
            'time_per_column_seconds' => ['required', 'integer', 'min:5', 'max:60'],
        ]);

        // Generate random columns
        $data['columns_data'] = KraepelinQuestion::generateRandomColumns(
            $data['column_count'],
            $data['rows_per_column']
        );

        KraepelinQuestion::create($data);

        return redirect()->route('admin.psikotest.kraepelin.index')
            ->with('status', 'Soal Kraepelin berhasil dibuat.');
    }

    public function show(KraepelinQuestion $kraepelin): View
    {
        return view('admin.psikotest.kraepelin.show', compact('kraepelin'));
    }

    public function edit(KraepelinQuestion $kraepelin): View
    {
        return view('admin.psikotest.kraepelin.edit', compact('kraepelin'));
    }

    public function update(Request $request, KraepelinQuestion $kraepelin): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'time_per_column_seconds' => ['required', 'integer', 'min:5', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $kraepelin->update($data);

        return redirect()->route('admin.psikotest.kraepelin.index')
            ->with('status', 'Soal Kraepelin berhasil diperbarui.');
    }

    public function destroy(KraepelinQuestion $kraepelin): RedirectResponse
    {
        $kraepelin->delete();

        return redirect()->route('admin.psikotest.kraepelin.index')
            ->with('status', 'Soal Kraepelin berhasil dihapus.');
    }

    public function regenerate(KraepelinQuestion $kraepelin): RedirectResponse
    {
        $kraepelin->update([
            'columns_data' => KraepelinQuestion::generateRandomColumns(
                $kraepelin->column_count,
                $kraepelin->rows_per_column
            ),
        ]);

        return redirect()->route('admin.psikotest.kraepelin.index')
            ->with('status', 'Angka soal berhasil digenerate ulang.');
    }
}
