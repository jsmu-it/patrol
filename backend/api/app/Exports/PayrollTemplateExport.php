<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollTemplateExport implements ShouldAutoSize, WithStyles, WithEvents
{
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Row 1: Category headers
                // DATA KARYAWAN (A-D) - Blue
                $sheet->setCellValue('A1', 'DATA KARYAWAN');
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1:D1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('4472C4');
                $sheet->getStyle('A1:D1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));

                // PENDAPATAN (E-AB) - Green
                $sheet->setCellValue('E1', 'PENDAPATAN');
                $sheet->mergeCells('E1:AB1');
                $sheet->getStyle('E1:AB1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('70AD47');
                $sheet->getStyle('E1:AB1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));

                // POTONGAN (AC-AR) - Red
                $sheet->setCellValue('AC1', 'POTONGAN');
                $sheet->mergeCells('AC1:AR1');
                $sheet->getStyle('AC1:AR1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C00000');
                $sheet->getStyle('AC1:AR1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));

                // Row 2: Column headers
                $headers = [
                    // Data karyawan (A-D)
                    'A2' => 'NIP',
                    'B2' => 'Nama',
                    'C2' => 'Unit/Site',
                    'D2' => 'Jabatan',
                    // Pendapatan (E-AB)
                    'E2' => 'Gaji',
                    'F2' => 'Rapel Gaji',
                    'G2' => 'Tunj. Jabatan',
                    'H2' => 'Tunj. Komunikasi',
                    'I2' => 'Tunj. Mess',
                    'J2' => 'Tunj. Penempatan',
                    'K2' => 'Tunj. Parkir',
                    'L2' => 'Tunj. Lain',
                    'M2' => 'Rapel Tunjangan',
                    'N2' => 'Uang Makan',
                    'O2' => 'Uang Transport',
                    'P2' => 'Lembur',
                    'Q2' => 'Backup Pengganti',
                    'R2' => 'Dinas Luar',
                    'S2' => 'Premi Shift',
                    'T2' => 'Prorate Cuti',
                    'U2' => 'Prorate Tali Asih',
                    'V2' => 'Prorate THR',
                    'W2' => 'BPJS TK-JHT',
                    'X2' => 'BPJS TK-JKM',
                    'Y2' => 'BPJS TK-JKK',
                    'Z2' => 'BPJS TK-JP',
                    'AA2' => 'BPJS Kesehatan',
                    'AB2' => 'Lain-Lain',
                    // Potongan (AC-AR)
                    'AC2' => 'Unpaid Gaji',
                    'AD2' => 'Unpaid Tunjangan',
                    'AE2' => 'BPJS TK-JHT',
                    'AF2' => 'BPJS TK-JKM',
                    'AG2' => 'BPJS TK-JKK',
                    'AH2' => 'BPJS TK-JP',
                    'AI2' => 'BPJS Kesehatan',
                    'AJ2' => 'Diksar',
                    'AK2' => 'BPR',
                    'AL2' => 'Seragam',
                    'AM2' => 'Koperasi',
                    'AN2' => 'Ketidakhadiran-1',
                    'AO2' => 'Ketidakhadiran-2',
                    'AP2' => 'PPh21-Gaji',
                    'AQ2' => 'PPh21-THR',
                    'AR2' => 'Lain-Lain',
                ];

                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                // Style row 2 - light backgrounds
                // Data Karyawan - light blue
                $sheet->getStyle('A2:D2')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D6DCE5');

                // Pendapatan - light green
                $sheet->getStyle('E2:AB2')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E2EFDA');

                // Potongan - light red
                $sheet->getStyle('AC2:AR2')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FCE4D6');

                // Center align row 1 and 2
                $sheet->getStyle('A1:AR2')->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Add borders
                $sheet->getStyle('A1:AR2')->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Freeze row 1 and 2
                $sheet->freezePane('A3');

                // Add sample data in row 3
                $sheet->setCellValue('A3', '0500020970');
                $sheet->setCellValue('B3', 'EDY SYAMSIR');
                $sheet->setCellValue('C3', 'PHM ONSHORE - CPA - I');
                $sheet->setCellValue('D3', 'GROUP LEADER');
                $sheet->setCellValue('E3', '5081076');
                $sheet->setCellValue('P3', '4625835');
                $sheet->setCellValue('S3', '500000');  // Premi Shift
                $sheet->setCellValue('T3', '250000');  // Prorate Cuti
                $sheet->setCellValue('U3', '300000');  // Prorate Tali Asih
                $sheet->setCellValue('V3', '1000000'); // Prorate THR
                $sheet->setCellValue('AE3', '101622');
                $sheet->setCellValue('AH3', '50811');
                $sheet->setCellValue('AI3', '50811');
                $sheet->getStyle('A3:AR3')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('808080'));
            },
        ];
    }
}
