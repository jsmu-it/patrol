<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="12" style="font-weight: bold; text-align: center; font-size: 14pt;">LAPORAN ABSENSI</th>
            </tr>
            <tr>
                <td colspan="12" style="text-align: center;">Periode: {{ $filters['from']->format('d M Y') }} - {{ $filters['to']->format('d M Y') }}</td>
            </tr>
            @if($userName)
            <tr>
                <td colspan="12" style="text-align: center; font-weight: bold;">Karyawan: {{ $userName }}</td>
            </tr>
            @endif
            <tr>
                <th style="font-weight: bold;">Tanggal</th>
                <th style="font-weight: bold;">Nama</th>
                <th style="font-weight: bold;">NIP</th>
                <th style="font-weight: bold;">Shift</th>
                <th style="font-weight: bold;">Masuk</th>
                <th style="font-weight: bold;">Keluar</th>
                <th style="font-weight: bold;">Status</th>
                <th style="font-weight: bold;">Jam Lebih</th>
                <th style="font-weight: bold;">Keterangan Masuk</th>
                <th style="font-weight: bold;">Keterangan Keluar</th>
                <th style="font-weight: bold;">Foto Masuk</th>
                <th style="font-weight: bold;">Foto Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td>{{ $row['nip'] }}</td>
                <td>{{ $row['shift_name'] }}</td>
                <td>{{ $row['clock_in_time'] }}</td>
                <td>{{ $row['clock_out_time'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['jam_lebih'] }}</td>
                <td>{{ $row['clock_in_note'] }}</td>
                <td>{{ $row['clock_out_note'] }}</td>
                <td></td> <!-- Placeholder for Foto Masuk (Embedded via Drawings) -->
                <td></td> <!-- Placeholder for Foto Keluar (Embedded via Drawings) -->
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
