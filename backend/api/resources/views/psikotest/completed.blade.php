<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Selesai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Test Sudah Selesai</h1>
            <p class="text-gray-600 mb-6">Terima kasih telah menyelesaikan test.</p>
            
            <div class="bg-gray-50 rounded-lg p-4 text-left text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <div class="text-gray-500">Nama:</div>
                    <div class="font-medium">{{ $session->participant_name }}</div>
                    
                    <div class="text-gray-500">Jenis Test:</div>
                    <div class="font-medium capitalize">{{ $session->test_type }}</div>
                    
                    <div class="text-gray-500">Waktu Selesai:</div>
                    <div class="font-medium">{{ $session->completed_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
            </div>

            <p class="mt-6 text-sm text-gray-500">
                Hasil test akan diproses dan dihubungi oleh tim HRD.
            </p>
        </div>
    </div>
</body>
</html>
