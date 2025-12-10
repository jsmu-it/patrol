<?php

namespace App\Imports;

use App\Models\PayrollSlip;
use App\Models\PayrollSlipItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PayrollImport implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 3; // Data dimulai dari row 3 (row 1 kategori, row 2 header)
    }

    protected string $periodMonth;
    protected string $signLocation;
    protected string $signDate;

    // Column indices (0-based) => display label
    // A=0, B=1, C=2, D=3, E=4, ...
    protected array $incomeColumns = [
        4 => 'Gaji',                      // E - Gaji
        5 => 'Rapel Gaji',                // F - Rapel Gaji
        6 => 'Tunjangan Jabatan',         // G - Tunj. Jabatan
        7 => 'Tunjangan Komunikasi',      // H - Tunj. Komunikasi
        8 => 'Tunjangan Mess',            // I - Tunj. Mess
        9 => 'Tunjangan Penempatan',      // J - Tunj. Penempatan
        10 => 'Tunjangan Parkir',         // K - Tunj. Parkir
        11 => 'Tunjangan Lain',           // L - Tunj. Lain
        12 => 'Rapel Tunjangan',          // M - Rapel Tunjangan
        13 => 'Bantuan Uang Makan',       // N - Uang Makan
        14 => 'Bantuan Uang Transport',   // O - Uang Transport
        15 => 'Lembur',                   // P - Lembur
        16 => 'Backup Pengganti',         // Q - Backup Pengganti
        17 => 'Dinas Luar',               // R - Dinas Luar
        18 => 'Premi Shift',              // S - Premi Shift
        19 => 'Prorate Cuti',             // T - Prorate Cuti
        20 => 'Prorate Tali Asih',        // U - Prorate Tali Asih
        21 => 'Prorate THR',              // V - Prorate THR
        22 => 'BPJS TK-JHT',              // W - BPJS TK-JHT (income)
        23 => 'BPJS TK-JKM',              // X - BPJS TK-JKM (income)
        24 => 'BPJS TK-JKK',              // Y - BPJS TK-JKK (income)
        25 => 'BPJS TK-JP',               // Z - BPJS TK-JP (income)
        26 => 'BPJS Kesehatan',           // AA - BPJS Kesehatan (income)
        27 => 'Lain-Lain',                // AB - Lain-Lain (income)
    ];

    protected array $deductionColumns = [
        28 => 'Unpaid Gaji',              // AC - Unpaid Gaji
        29 => 'Unpaid Tunjangan',         // AD - Unpaid Tunjangan
        30 => 'BPJS TK-JHT',              // AE - BPJS TK-JHT (deduction)
        31 => 'BPJS TK-JKM',              // AF - BPJS TK-JKM (deduction)
        32 => 'BPJS TK-JKK',              // AG - BPJS TK-JKK (deduction)
        33 => 'BPJS TK-JP',               // AH - BPJS TK-JP (deduction)
        34 => 'BPJS Kesehatan',           // AI - BPJS Kesehatan (deduction)
        35 => 'Diksar',                   // AJ - Diksar
        36 => 'BPR',                      // AK - BPR
        37 => 'Seragam',                  // AL - Seragam
        38 => 'Koperasi',                 // AM - Koperasi
        39 => 'Ketidakhadiran-1',         // AN - Ketidakhadiran-1
        40 => 'Ketidakhadiran-2',         // AO - Ketidakhadiran-2
        41 => 'PPh21-Gaji',               // AP - PPh21-Gaji
        42 => 'PPh21-THR',                // AQ - PPh21-THR
        43 => 'Lain-Lain',                // AR - Lain-Lain (deduction)
    ];

    public function __construct(string $periodMonth, string $signLocation = 'Jakarta', ?string $signDate = null)
    {
        $this->periodMonth = $periodMonth;
        $this->signLocation = $signLocation;
        $this->signDate = $signDate ?? date('Y-m-d');
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            $nip = trim((string) ($rowArray[0] ?? ''));
            if (empty($nip)) {
                continue;
            }

            $name = trim((string) ($rowArray[1] ?? ''));
            $unit = trim((string) ($rowArray[2] ?? ''));
            $position = trim((string) ($rowArray[3] ?? ''));

            // Find user by NIP (from user_profiles)
            $user = User::whereHas('profile', function ($q) use ($nip) {
                $q->where('nip', $nip);
            })->first();

            // Delete existing slip for same user and period
            if ($user) {
                PayrollSlip::where('user_id', $user->id)
                    ->where('period_month', $this->periodMonth)
                    ->delete();
            }

            // Create new payroll slip
            $slip = PayrollSlip::create([
                'user_id' => $user?->id,
                'period_month' => $this->periodMonth,
                'nip' => $nip,
                'name' => $name,
                'unit' => $unit,
                'position' => $position,
                'total_income' => 0,
                'total_deduction' => 0,
                'net_income' => 0,
                'sign_location' => $this->signLocation,
                'sign_date' => $this->signDate,
            ]);

            $totalIncome = 0;
            $totalDeduction = 0;
            $sortOrder = 0;

            // Process income items
            foreach ($this->incomeColumns as $colIndex => $label) {
                $amount = $this->parseAmount($rowArray[$colIndex] ?? 0);
                if ($amount > 0) {
                    PayrollSlipItem::create([
                        'payroll_slip_id' => $slip->id,
                        'type' => 'income',
                        'label' => $label,
                        'amount' => $amount,
                        'sort_order' => $sortOrder++,
                    ]);
                    $totalIncome += $amount;
                }
            }

            // Reset sort order for deductions
            $sortOrder = 0;

            // Process deduction items
            foreach ($this->deductionColumns as $colIndex => $label) {
                $amount = $this->parseAmount($rowArray[$colIndex] ?? 0);
                if ($amount > 0) {
                    PayrollSlipItem::create([
                        'payroll_slip_id' => $slip->id,
                        'type' => 'deduction',
                        'label' => $label,
                        'amount' => $amount,
                        'sort_order' => $sortOrder++,
                    ]);
                    $totalDeduction += $amount;
                }
            }

            // Update totals
            $slip->update([
                'total_income' => $totalIncome,
                'total_deduction' => $totalDeduction,
                'net_income' => $totalIncome - $totalDeduction,
            ]);
        }
    }

    protected function parseAmount($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove currency symbols, spaces, and convert comma to dot
        $cleaned = preg_replace('/[^\d,.-]/', '', (string) $value);
        $cleaned = str_replace(',', '.', $cleaned);

        return (float) $cleaned;
    }
}
