<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $item->name }} — Portal Kerja JSMU</title>
    <link rel="stylesheet" href="{{ asset('assets/css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
    <style>
        html, body { height: 100%; margin: 0; background: #F7F8FA; }
        .sunting-kepala { height: 52px; display: flex; align-items: center; gap: 12px; padding: 0 16px; background: #fff; border-bottom: 1px solid #E9EBEF; }
        .sunting-bingkai { width: 100%; height: calc(100vh - 52px); border: 0; display: block; }
    </style>
</head>
<body>
    <div class="sunting-kepala">
        <a href="{{ $kembali }}" class="pt-btn pt-btn-garis pt-btn-kecil">&larr; Kembali</a>
        <div class="min-w-0 flex-1">
            <div class="text-sm font-semibold text-gray-900 truncate">{{ $item->name }}</div>
            <div class="text-xs text-gray-500">
                @if($bolehTulis)
                    Perubahan tersimpan sendiri ke Penyimpanan Data
                @else
                    Hanya bisa dibaca &mdash; Anda bukan pemilik berkas ini
                @endif
            </div>
        </div>
        <a href="{{ $item->user_id ? route('portal.storage.unduh', $item) : '#' }}"
           class="pt-btn pt-btn-garis pt-btn-kecil {{ $item->user_id ? '' : 'hidden' }}">Unduh</a>
    </div>

    {{--
        Editor dimuat lewat POST supaya tiket aksesnya tidak ikut tercatat di
        alamat, riwayat peramban, maupun log server.
    --}}
    <form id="form-editor" action="{{ $urlEditor }}WOPISrc={{ urlencode($wopiSrc) }}&lang=id-ID"
          method="post" target="bingkai-editor">
        <input type="hidden" name="access_token" value="{{ $token }}">
        <input type="hidden" name="access_token_ttl" value="{{ $kedaluwarsa }}">
    </form>

    <iframe id="bingkai-editor" name="bingkai-editor" class="sunting-bingkai"
            allow="clipboard-read; clipboard-write; fullscreen"></iframe>

    <script>
        document.getElementById('form-editor').submit();
    </script>
</body>
</html>
