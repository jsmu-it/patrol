<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class AttendanceReportExport implements FromView, ShouldAutoSize, WithDrawings, WithStyles
{
    public function __construct(
        private readonly \Illuminate\Support\Collection $rows,
        private readonly array $filters,
        private readonly string $projectName = 'Semua Project',
        private readonly ?string $userName = null,
        private readonly string $template = 'standard'
    ) {
    }

    public function view(): View
    {
        $viewMap = [
            'standard' => 'admin.reports.exports.attendance_excel',
            'pivot' => 'admin.reports.exports.attendance_excel_pivot',
            'ho' => 'admin.reports.exports.attendance_excel_ho',
            'summarecon_bogor' => 'admin.reports.exports.attendance_excel_summarecon_bogor',
        ];

        return view($viewMap[$this->template] ?? $viewMap['standard'], [
            'rows' => $this->rows,
            'filters' => $this->filters,
            'projectName' => $this->projectName,
            'userName' => $this->userName,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        if ($this->template === 'summarecon_bogor') {
            // Calculate header rows:
            // 1: Title, 2: Periode, 3: Karyawan (optional), 4: Headers (if Karyawan else 3), 5: Data (if Karyawan else 4)
            $headerRows = $this->userName ? 4 : 3;
            $dataStartRow = $headerRows + 1;
            
            // Set row height for rows with photos 
            $lastRow = count($this->rows) + $headerRows; 
            for ($i = $dataStartRow; $i <= $lastRow; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(60);
            }
        }
        
        return [];
    }

    public function drawings()
    {
        $drawings = [];
        
        // Main logo
        $logo = new Drawing();
        $logo->setName('Logo');
        $logo->setDescription('Logo');
        $logo->setPath(public_path('images/admin-logo.png'));
        $logo->setHeight(60);
        
        if ($this->template === 'summarecon_bogor') {
            $logo->setCoordinates('M1');
        } else {
            $logo->setCoordinates('K1');
        }
        
        $drawings[] = $logo;

        // Photo drawings for summarecon_bogor template
        if ($this->template === 'summarecon_bogor') {
            $headerRows = $this->userName ? 4 : 3;
            $dataStartRow = $headerRows + 1;

            foreach ($this->rows as $index => $row) {
                $rowNum = $index + $dataStartRow; 

                if (!empty($row['clock_in_photo_path'])) {
                    $path = storage_path('app/public/' . $row['clock_in_photo_path']);
                    if (file_exists($path)) {
                        $drawingIn = new Drawing();
                        $drawingIn->setName('Foto Masuk');
                        $drawingIn->setPath($path);
                        $drawingIn->setHeight(50);
                        $drawingIn->setCoordinates('K' . $rowNum); // Column K is Foto Masuk (Column 11)
                        $drawingIn->setOffsetX(5);
                        $drawingIn->setOffsetY(5);
                        $drawings[] = $drawingIn;
                    } else {
                        \Log::warning("Excel Export: Clock-in photo not found at " . $path);
                    }
                }

                if (!empty($row['clock_out_photo_path'])) {
                    $path = storage_path('app/public/' . $row['clock_out_photo_path']);
                    if (file_exists($path)) {
                        $drawingOut = new Drawing();
                        $drawingOut->setName('Foto Keluar');
                        $drawingOut->setPath($path);
                        $drawingOut->setHeight(50);
                        $drawingOut->setCoordinates('L' . $rowNum); // Column L is Foto Keluar (Column 12)
                        $drawingOut->setOffsetX(5);
                        $drawingOut->setOffsetY(5);
                        $drawings[] = $drawingOut;
                    } else {
                        \Log::warning("Excel Export: Clock-out photo not found at " . $path);
                    }
                }
            }
        }
        
        return $drawings;
    }
}
