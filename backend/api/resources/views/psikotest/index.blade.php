<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psikotest - Masukkan Token</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Psikotest Online</h1>
                <p class="text-gray-500 mt-2">Masukkan kode akses yang telah diberikan</p>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('psikotest.validate') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="token" class="block text-sm font-medium text-gray-700 mb-2">Kode Akses</label>
                    <input type="text" name="token" id="token" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center text-lg font-mono tracking-wider uppercase focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="XXXXXXXX" required autofocus>
                </div>
                <button type="submit" 
                    class="w-full py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition focus:ring-4 focus:ring-purple-200">
                    Mulai Test
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
                <p>Pastikan koneksi internet stabil sebelum memulai test.</p>
            </div>
        </div>
    </div>
</body>
</html>
