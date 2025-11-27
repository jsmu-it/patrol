<table>
    <thead>
    <tr>
        <td colspan="11" style="font-weight: bold; text-align: center; font-size: 14pt;">LAPORAN PATROLI</td>
    </tr>
    <tr>
        <td colspan="11" style="text-align: center;">Project: {{ $projectName }}</td>
    </tr>
    <tr>
        <td colspan="11" style="text-align: center;">Periode: {{ $filters['from']->format('d-m-Y') }} s/d {{ $filters['to']->format('d-m-Y') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Tanggal</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Jam</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Nama</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Username</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Project</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Checkpoint</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Tipe</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Judul</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Lokasi/Pos</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Deskripsi</th>
        <th style="font-weight: bold; background-color: #eeeeee; border: 1px solid #000000;">Foto (Link)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td style="border: 1px solid #000000;">{{ $row['date'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['time'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['user'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['username'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['project'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['checkpoint'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['type'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['title'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['post_name'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['description'] }}</td>
            <td style="border: 1px solid #000000;">
                @if($row['photo_url'])
                    <a href="{{ $row['photo_url'] }}">Lihat Foto</a>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
