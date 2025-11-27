<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Patroli</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; font-size: 9pt; }
        th { background-color: #eee; }
        .header { text-align: center; margin-bottom: 20px; position: relative; }
        .header img { position: absolute; left: 0; top: 0; width: 60px; height: auto; }
        .header h2 { margin: 0; padding-top: 10px; }
        .header p { margin: 5px 0; }
        .photo { width: 60px; height: auto; display: block; margin: 0 auto; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/admin-logo.png');
        $projectName = 'Semua Project';
        if ($filters['project_id']) {
            $project = \App\Models\Project::find($filters['project_id']);
            if ($project) $projectName = $project->name;
        }
    @endphp

    <div class="header">
        @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="Logo">
        @endif
        <h2>LAPORAN PATROLI</h2>
        <p>Project: {{ $projectName }}</p>
        <p>Periode: {{ $filters['from']->format('d-m-Y') }} s/d {{ $filters['to']->format('d-m-Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Nama</th>
                <th>Project</th>
                <th>Checkpoint</th>
                <th>Tipe</th>
                <th>Judul</th>
                <th>Lokasi/Pos</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['time'] }}</td>
                    <td>{{ $row['user'] }}</td>
                    <td>{{ $row['project'] }}</td>
                    <td>{{ $row['checkpoint'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['post_name'] }}</td>
                    <td>
                        @if(!empty($row['photo_url']))
                            @php
                                // Convert full URL back to relative path for local access if possible, or use public_path
                                $photoPath = str_replace(asset('storage/'), '', $row['photo_url']);
                                $localPath = storage_path('app/public/' . $photoPath);
                            @endphp
                            @if(file_exists($localPath))
                                <img class="photo" src="{{ $localPath }}">
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
