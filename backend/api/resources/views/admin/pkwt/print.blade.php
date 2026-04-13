<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKWT - {{ $pkwt->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            padding: 2cm;
            max-width: 21cm;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2em;
            border-bottom: 2px solid #000;
            padding-bottom: 1em;
        }
        
        .header h1 {
            font-size: 16pt;
            margin-bottom: 0.5em;
        }
        
        .header h2 {
            font-size: 14pt;
            font-weight: normal;
        }
        
        .pkwt-number {
            text-align: center;
            margin-bottom: 2em;
        }
        
        .pkwt-content {
            text-align: justify;
        }
        
        .employee-info {
            margin: 2em 0;
        }
        
        .employee-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .employee-info td {
            padding: 0.3em 0;
            vertical-align: top;
        }
        
        .employee-info td:first-child {
            width: 30%;
        }
        
        .employee-info td:nth-child(2) {
            width: 2%;
        }
        
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1em 0;
        }
        
        .salary-table th, .salary-table td {
            border: 1px solid #000;
            padding: 0.5em;
            text-align: left;
        }
        
        .salary-table th {
            background-color: #f0f0f0;
        }
        
        .salary-table .amount {
            text-align: right;
        }
        
        .total-row {
            font-weight: bold;
        }
        
        .signature-section {
            margin-top: 3em;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 40%;
            text-align: center;
        }
        
        .signature-line {
            margin-top: 4em;
            border-top: 1px solid #000;
            padding-top: 0.5em;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #1e293b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #334155;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Cetak</button>

    @if($template)
        <div class="pkwt-content">
            {!! $template !!}
        </div>
    @else
        {{-- Default Template --}}
        <div class="header">
            <h1>PERJANJIAN KERJA WAKTU TERTENTU (PKWT)</h1>
            <h2>{{ $pkwt->project?->name ?? 'PT. JSMU INDONESIA' }}</h2>
        </div>

        <div class="pkwt-number">
            <strong>Nomor: {{ $pkwt->pkwt_number }}</strong>
        </div>

        <div class="pkwt-content">
            <p>Pada hari ini, yang bertanda tangan di bawah ini:</p>
            
            <div class="employee-info">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>:</td>
                        <td><strong>{{ $pkwt->name }}</strong></td>
                    </tr>
                    <tr>
                        <td>No. KTP</td>
                        <td>:</td>
                        <td>{{ $pkwt->ktp_number }}</td>
                    </tr>
                    <tr>
                        <td>Tempat, Tanggal Lahir</td>
                        <td>:</td>
                        <td>{{ $pkwt->ttl }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>:</td>
                        <td>{{ $pkwt->address }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>:</td>
                        <td>{{ $pkwt->email }}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td><strong>{{ $pkwt->position?->name ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Unit/Project</td>
                        <td>:</td>
                        <td><strong>{{ $pkwt->project?->name ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Masa Kontrak</td>
                        <td>:</td>
                        <td>{{ $pkwt->contract_start?->format('d F Y') ?? '-' }} s/d {{ $pkwt->contract_end?->format('d F Y') ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <h3 style="margin: 1.5em 0 0.5em;">Rincian Pendapatan:</h3>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Pendapatan</th>
                        <th class="amount">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($pkwt->incomes as $income)
                        @if($income->amount > 0)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $income->incomeType->name }}</td>
                                <td class="amount">{{ number_format($income->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">Total Pendapatan</td>
                        <td class="amount">{{ number_format($pkwt->total_income, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <h3 style="margin: 1.5em 0 0.5em;">Rincian Potongan:</h3>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Potongan</th>
                        <th class="amount">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($pkwt->deductions as $deduction)
                        @if($deduction->amount > 0)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $deduction->deductionType->name }}</td>
                                <td class="amount">{{ number_format($deduction->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="total-row">
                        <td colspan="2">Total Potongan</td>
                        <td class="amount">{{ number_format($pkwt->total_deduction, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="salary-table" style="margin-top: 1em;">
                <tr class="total-row">
                    <td style="width: 70%;">GAJI BERSIH (Take Home Pay)</td>
                    <td class="amount">Rp {{ number_format($pkwt->net_salary, 0, ',', '.') }}</td>
                </tr>
            </table>

            <div class="signature-section">
                <div class="signature-box">
                    <p>Pihak Pertama</p>
                    <p>{{ $pkwt->project?->name ?? 'PT. JSMU INDONESIA' }}</p>
                    <div class="signature-line">
                        <p>(_____________________)</p>
                        <p>Direktur</p>
                    </div>
                </div>
                <div class="signature-box">
                    <p>Pihak Kedua</p>
                    <p>Karyawan</p>
                    <div class="signature-line">
                        <p>{{ $pkwt->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
