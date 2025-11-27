<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center">
<div class="w-full max-w-md bg-white/95 rounded-xl shadow-lg p-8">
    <h1 class="text-2xl font-semibold text-slate-900 mb-1">Login Admin</h1>
    <p class="text-xs text-slate-500 mb-6">Masuk ke dashboard Sistem Absensi &amp; Patroli Satpam.</p>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2 rounded bg-red-100 text-red-800 text-sm">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" autocomplete="username" required>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Password</label>
            <input type="password" name="password" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" autocomplete="current-password" required>
        </div>
        <button type="submit" class="w-full py-2.5 rounded-md bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">Masuk</button>
    </form>
</div>
</body>
</html>
