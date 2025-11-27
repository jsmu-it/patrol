<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class AttendanceReportExport implements FromView, ShouldAutoSize, WithDrawings
{
    public function __construct(
        private readonly \Illuminate\Support\Collection $rows,
        private readonly array $filters,
        private readonly string $projectName = 'Semua Project'
    ) {
    }

    public function view(): View
    {
        return view('admin.reports.exports.attendance_excel', [
            'rows' => $this->rows,
            'filters' => $this->filters,
            'projectName' => $this->projectName,
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('images/admin-logo.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('J1'); // Position at top right (column J)
        
        return [$drawing];
    }
}
