@extends('layouts.admin')

@section('title', 'Edit Admin')
@section('page_title', 'Edit Admin')

@section('content')
    <div class="max-w-3xl">
        <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $permission->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username', $permission->username) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 @enderror">
                    @error('username')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password. Minimal 6 karakter jika diisi.</p>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jabatan/Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror">
                        <option value="">Pilih Jabatan</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role', $permission->role) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Otoritas Project -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Otoritas Project</label>
                    <p class="text-xs text-gray-500 mb-3">Pilih project mana saja yang bisa diakses oleh admin ini. Kosongkan untuk akses semua project (untuk role SUPERADMIN).</p>
                    
                    <div class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50">
                        @if($projects->count() > 0)
                            <div class="space-y-2">
                                @foreach($projects as $project)
                                    <label class="flex items-center gap-2 hover:bg-white p-2 rounded cursor-pointer">
                                        <input type="checkbox" name="project_ids[]" value="{{ $project->id }}"
                                               {{ (is_array(old('project_ids')) && in_array($project->id, old('project_ids'))) || (!old('project_ids') && in_array($project->id, $selectedProjects)) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ $project->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Belum ada project tersedia</p>
                        @endif
                    </div>
                    @error('project_ids')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    Update
                </button>
                <a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
