@extends('layouts.admin')

@section('title', 'Assign Tipe Cuti')
@section('page_title', 'Assign Tipe Cuti: {{ $leaveType->name }}')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.leave-types.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            &larr; Kembali ke Tipe Cuti
        </a>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4 p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select name="project_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[180px]">
                    <option value="">Semua</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama / Username</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-56"
                    placeholder="Ketik nama atau username">
            </div>
            <div class="flex items-center h-10">
                <label class="flex items-center text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" name="sort_project" value="1" {{ request('sort_project') ? 'checked' : '' }} class="rounded border-gray-300 mr-2">
                    Urutkan berdasarkan project
                </label>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium transition">
                    Filter
                </button>
                @if(request()->hasAny(['project_id', 'search', 'sort_project']))
                <a href="{{ route('admin.leave-types.assign', $leaveType) }}" class="ml-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Pilih Karyawan</h3>
            <p class="text-sm text-gray-500">Pilih karyawan yang dapat menggunakan tipe cuti "{{ $leaveType->name }}" (Quota default: {{ $leaveType->default_quota }} hari)</p>
        </div>
        <form action="{{ route('admin.leave-types.assign', $leaveType) }}" method="POST" class="p-4">
            @csrf
            <div class="flex flex-wrap gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quota (kosongkan untuk menggunakan default)</label>
                    <input type="number" name="quota" min="0" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-40" placeholder="{{ $leaveType->default_quota }}">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center text-sm font-medium text-gray-700 h-10">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 mr-2">
                        Pilih Semua ({{ $users->count() }} karyawan)
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-[400px] overflow-y-auto border border-gray-200 rounded-lg p-4">
                @forelse($users as $user)
                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300 mr-3" {{ in_array($user->id, $assignedUserIds) ? 'checked' : '' }}>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium truncate">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ $user->profile?->nip ?? '-' }}
                            @if($user->activeProject)
                                • <span class="text-blue-600">{{ $user->activeProject->name }}</span>
                            @endif
                        </div>
                    </div>
                </label>
                @empty
                <div class="col-span-full text-center text-gray-500 py-4">Tidak ada karyawan ditemukan.</div>
                @endforelse
            </div>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                    Simpan Assignment
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
