<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Semua QR Checkpoint</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; padding: 16px; }
        .page { max-width: 480px; margin: 0 auto 24px auto; text-align: center; }
        .meta { margin-bottom: 16px; font-size: 12px; }
        .meta div { margin: 2px 0; }
        .qr { margin: 16px auto; width: 256px; height: 256px; }
        @media print {
            button { display: none; }
            body { padding: 0; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
<button type="button" onclick="window.print()" style="margin-bottom:16px; padding:6px 12px;">Print</button>

@foreach($checkpoints as $checkpoint)
    <div class="page">
        <h1 style="font-size:18px; margin-bottom:4px;">Checkpoint Patroli</h1>
        <div class="meta">
            <div><strong>Project:</strong> {{ $checkpoint->project?->name }}</div>
            <div><strong>Nama Titik:</strong> {{ $checkpoint->title }}</div>
            <div><strong>Pos:</strong> {{ $checkpoint->post_name }}</div>
            <div><strong>Kode:</strong> <span style="font-family:monospace;">{{ $checkpoint->code }}</span></div>
        </div>
        <div class="qr">
            @php
                $payload = 'satpamapp://checkpoint?code=' . urlencode($checkpoint->code);
            @endphp
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(256)->generate($payload) !!}
        </div>
        <p style="font-size:11px; color:#555; max-width:360px; margin:0 auto;">
            QR ini hanya digunakan oleh aplikasi Satpam Mobile. Pastikan aplikasi resmi terpasang sebelum melakukan scan.
        </p>
    </div>
    @if (! $loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 300);
    });
</script>
</body>
</html>
