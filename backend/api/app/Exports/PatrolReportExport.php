<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PatrolReportExport implements FromView, ShouldAutoSize
{
    public function __construct(
        private readonly \Illuminate\Support\Collection $rows,
        private readonly array $filters,
        private readonly string $projectName = 'Semua Project'
    ) {
    }

    public function view(): View
    {
        return view('admin.reports.exports.patrol_excel', [
            'rows' => $this->rows,
            'filters' => $this->filters,
            'projectName' => $this->projectName,
        ]);
    }
}
