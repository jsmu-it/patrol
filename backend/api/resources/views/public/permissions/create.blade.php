<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Akses Admin - JSMUGuard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-4">
                <img src="{{ asset('images/admin-logo.png') }}" alt="JSMUGuard" class="h-12 w-auto">
                <h1 class="text-3xl font-bold text-slate-900">JSMUGuard</h1>
            </div>
            <p class="text-slate-600 text-lg">Permintaan Akses Admin</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 px-6 py-4 rounded-xl bg-emerald-50 border-2 border-emerald-200 text-emerald-800">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
            <form action="{{ route('public.permissions.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 @enderror"
                           placeholder="Masukkan nama lengkap Anda">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('email') border-red-500 @enderror"
                           placeholder="nama@email.com">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('username') border-red-500 @enderror"
                           placeholder="Pilih username untuk login">
                    @error('username')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('password') border-red-500 @enderror"
                           placeholder="Minimal 6 karakter">
                    <p class="mt-2 text-xs text-slate-500">Password minimal 6 karakter</p>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jabatan -->
                <div>
                    <label for="position" class="block text-sm font-semibold text-slate-700 mb-2">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="position" id="position" value="{{ old('position') }}" required
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('position') border-red-500 @enderror"
                           placeholder="Contoh: Manager HRD, Staff IT, dll">
                    @error('position')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Otoritas Project -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Otoritas Project <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-slate-500 mb-3">Pilih project mana saja yang ingin Anda akses</p>
                    
                    <div class="border-2 border-slate-200 rounded-xl p-4 max-h-80 overflow-y-auto bg-slate-50">
                        @if($projects->count() > 0)
                            <div class="space-y-2">
                                @foreach($projects as $project)
                                    <label class="flex items-start gap-3 hover:bg-white p-3 rounded-lg cursor-pointer transition">
                                        <input type="checkbox" name="project_ids[]" value="{{ $project->id }}"
                                               {{ is_array(old('project_ids')) && in_array($project->id, old('project_ids')) ? 'checked' : '' }}
                                               class="w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500 mt-0.5 flex-shrink-0">
                                        <span class="text-sm text-slate-700">{{ $project->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-500 text-center py-6">Belum ada project tersedia</p>
                        @endif
                    </div>
                    @error('project_ids')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Catatan Penting:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Permintaan akses akan diproses oleh tim IT</li>
                                <li>Akun akan diaktifkan setelah verifikasi</li>
                                <li>Anda akan menerima notifikasi via email</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all shadow-lg hover:shadow-xl">
                        Kirim Permintaan Akses
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-slate-500">
            <p>Sudah punya akses? <a href="{{ route('admin.login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
