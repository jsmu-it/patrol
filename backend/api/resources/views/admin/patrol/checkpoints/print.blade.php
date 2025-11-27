<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print QR Checkpoint {{ $checkpoint->code }}</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; padding: 16px; }
        .wrapper { max-width: 480px; margin: 0 auto; text-align: center; }
        .meta { margin-bottom: 16px; font-size: 12px; }
        .meta div { margin: 2px 0; }
        #qr-container { margin: 16px auto; width: 256px; height: 256px; }
        @media print {
            button { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <h1 style="font-size:18px; margin-bottom:4px;">Checkpoint Patroli</h1>
    <div class="meta">
        <div><strong>Project:</strong> {{ $checkpoint->project?->name }}</div>
        <div><strong>Nama Titik:</strong> {{ $checkpoint->title }}</div>
        <div><strong>Pos:</strong> {{ $checkpoint->post_name }}</div>
        <div><strong>Kode:</strong> <span style="font-family:monospace;">{{ $checkpoint->code }}</span></div>
    </div>
    <div id="qr-container">
        @php
            $payload = 'satpamapp://checkpoint?code=' . urlencode($checkpoint->code);
        @endphp
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(256)->generate($payload) !!}
    </div>
    <p style="font-size:11px; color:#555; max-width:360px; margin:0 auto;">
        QR ini hanya digunakan oleh aplikasi Satpam Mobile. Pastikan aplikasi resmi terpasang sebelum melakukan scan.
    </p>
    <button type="button" onclick="window.print()" style="margin-top:16px; padding:6px 12px;">Print</button>
</div>
<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 300);
    });
</script>
</body>
</html>
