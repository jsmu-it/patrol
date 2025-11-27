<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f4; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; }
        .header-table td { border: none; padding: 5px; vertical-align: top; }
        .logo { width: 80px; }
        .report-title { font-size: 14pt; font-weight: bold; margin-bottom: 5px; }
        .status-complete { color: green; }
        .status-missing { color: red; }
        .status-over { color: blue; }
        .status-under { color: orange; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="20%"></td>
            <td width="60%" class="text-center">
                <div class="report-title">LAPORAN ABSENSI</div>
                <div>Periode: {{ $filters['from']->format('d M Y') }} - {{ $filters['to']->format('d M Y') }}</div>
                <div>Project: {{ $filters['project_id'] ? \App\Models\Project::find($filters['project_id'])?->name : 'Semua Project' }}</div>
            </td>
            <td width="20%" class="text-right">
                <img src="{{ public_path('images/admin-logo.png') }}" class="logo">
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Project</th>
                <th>Shift</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ \Carbon\Carbon::parse($record['date'])->format('d/m/Y') }}</td>
                <td>{{ $record['user_name'] }}</td>
                <td>{{ $record['project_name'] }}</td>
                <td>{{ $record['shift_name'] }}</td>
                <td>{{ $record['clock_in_time'] }}</td>
                <td>{{ $record['clock_out_time'] }}</td>
                <td>
                    @if($record['status'] === 'Sesuai Jam Kerja')
                        <span class="status-complete">Sesuai</span>
                    @elseif($record['status'] === 'Lebih Jam Kerja')
                        <span class="status-over">Lebih Jam Kerja</span>
                    @elseif($record['status'] === 'Kurang Jam Kerja')
                        <span class="status-under">Kurang Jam Kerja</span>
                    @else
                        <span class="status-missing">{{ $record['status'] }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
