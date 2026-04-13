@extends('pwa.layout')

@section('title', 'Scan Patroli - JSMU Guard')

@push('styles')
<style>
#qr-reader {
    width: 100%;
    max-width: 350px;
    margin: 20px auto;
}
#qr-reader video {
    border-radius: 16px;
}
</style>
@endpush

@section('content')
<div class="page-header">
    <button class="back-btn" onclick="goBack()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
    </button>
    <h1 class="page-title">Scan QR Patroli</h1>
</div>

<!-- QR Scanner -->
<div id="qr-reader"></div>

<div style="padding:16px;text-align:center;">
    <p style="color:var(--text-light);font-size:14px;margin-bottom:20px;">
        Arahkan kamera ke QR code checkpoint
    </p>
    
    <div style="display:flex;gap:12px;justify-content:center;">
        <a href="/app/patrol/form?mode=sos" class="btn btn-outline" style="width:auto;padding:12px 20px;color:var(--danger);border-color:var(--danger);">
            🚨 Laporan SOS
        </a>
        <a href="/app/patrol/form?mode=incident" class="btn btn-outline" style="width:auto;padding:12px 20px;color:var(--warning);border-color:var(--warning);">
            ⚠️ Laporan Insiden
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrcodeScanner;

document.addEventListener('DOMContentLoaded', () => {
    html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader",
        { fps: 10, qrbox: { width: 250, height: 250 } },
        false
    );
    
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
});

function onScanSuccess(decodedText, decodedResult) {
    // Parse QR code
    let code = decodedText;
    try {
        const url = new URL(decodedText);
        if (url.searchParams.has('code')) {
            code = url.searchParams.get('code');
        }
    } catch (e) {
        // Not a URL, use raw value
    }
    
    html5QrcodeScanner.clear();
    window.location.href = `/app/patrol/form?mode=normal&checkpoint=${encodeURIComponent(code)}`;
}

function onScanFailure(error) {
    // Silent fail
}
</script>
@endpush
