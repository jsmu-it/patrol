@extends('layouts.admin')

@section('title', 'Tambah Admin')
@section('page_title', 'Tambah Admin Baru')

@section('content')
    <div class="max-w-3xl">
        <form action="{{ route('admin.permissions.store') }}" method="POST"
              class="bg-white rounded-lg shadow-sm border border-gray-100 p-6"
              x-data="formAdmin()">
            @csrf
            <input type="hidden" name="mode" :value="mode">

            <div class="space-y-6">
                {{-- Pilih cara: ambil dari karyawan yang sudah terdaftar, atau buat akun baru --}}
                <div class="flex gap-2 p-1 bg-gray-100 rounded-lg">
                    <button type="button" @click="mode='karyawan'"
                            :class="mode==='karyawan' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600'"
                            class="flex-1 px-3 py-2 rounded-md text-sm font-medium transition">
                        Ambil dari Karyawan
                    </button>
                    <button type="button" @click="mode='baru'"
                            :class="mode==='baru' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600'"
                            class="flex-1 px-3 py-2 rounded-md text-sm font-medium transition">
                        Buat Akun Baru
                    </button>
                </div>

                {{-- ============ Mode: ambil dari karyawan ============ --}}
                <template x-if="mode==='karyawan'">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Saring per Project</label>
                            <select x-model="filterProject"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                                <option value="none">Belum punya project</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hanya untuk mempersempit daftar nama di bawah.</p>
                        </div>

                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Karyawan <span class="text-red-500">*</span></label>
                            <select name="user_id" id="user_id" x-model="userId"
                                    :required="mode==='karyawan'"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('user_id') border-red-500 @enderror">
                                <option value="">Pilih karyawan</option>
                                <template x-for="k in karyawanTersaring" :key="k.id">
                                    <option :value="k.id" x-text="labelKaryawan(k)"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-gray-500" x-show="karyawanTersaring.length === 0">
                                Tidak ada karyawan pada saringan ini.
                            </p>
                            @error('user_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800" x-show="userId">
                            Data diri, username, dan password karyawan ini dipakai apa adanya — tidak perlu diisi ulang.
                            Yang berubah hanya jabatan dan otoritas project-nya.
                        </div>
                    </div>
                </template>

                {{-- ============ Mode: akun baru ============ --}}
                <template x-if="mode==='baru'">
                <div class="space-y-6">
                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" :required="mode==='baru'"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" :required="mode==='baru'"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" :required="mode==='baru'"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 @enderror">
                    @error('username')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" :required="mode==='baru'"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter</p>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                </div>
                </template>

                <!-- Jabatan/Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror">
                        <option value="">Pilih Jabatan</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                                               {{ is_array(old('project_ids')) && in_array($project->id, old('project_ids')) ? 'checked' : '' }}
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
                    Simpan
                </button>
                <a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function formAdmin() {
        return {
            mode: '{{ old('mode', 'karyawan') }}',
            filterProject: '',
            userId: '{{ old('user_id') }}',
            karyawan: @json($karyawan),

            get karyawanTersaring() {
                if (!this.filterProject) return this.karyawan;
                if (this.filterProject === 'none') return this.karyawan.filter(k => !k.project_id);
                return this.karyawan.filter(k => String(k.project_id) === String(this.filterProject));
            },

            labelKaryawan(k) {
                const bagian = [k.name];
                if (k.nip) bagian.push('NIP ' + k.nip);
                if (k.role !== 'GUARD') bagian.push(k.role);
                return bagian.join(' — ');
            },
        }
    }
</script>
@endpush
