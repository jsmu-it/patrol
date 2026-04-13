@extends('layouts.admin')

@section('title', 'Saldo Cuti')
@section('page_title', 'Saldo Cuti Karyawan')

@section('content')
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6 p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <form method="GET" class="flex flex-wrap gap-4 items-end flex-1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @for($y = now()->year + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select name="project_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ ($projectId ?? '') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Role</option>
                        @foreach($roles as $roleKey => $roleName)
                            <option value="{{ $roleKey }}" {{ ($role ?? '') == $roleKey ? 'selected' : '' }}>
                                {{ $roleName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            <form action="{{ route('admin.leave-balance.reset-all') }}" method="POST" onsubmit="return confirm('Reset semua saldo cuti ke default untuk tahun {{ $year }}?')">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition">
                    Reset Semua Saldo {{ $year }}
                </button>
            </form>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulk-actions" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4" style="display:none;">
        <div class="flex flex-wrap gap-3 items-center">
            <span class="text-sm font-medium text-gray-700">
                <span id="selected-count">0</span> karyawan dipilih
            </span>
            <button type="button" onclick="showBulkUpdateModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                Update Saldo
            </button>
            <button type="button" onclick="bulkReset()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition">
                Reset Saldo
            </button>
            <button type="button" onclick="showBulkAssignModal()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                Assign Tipe Cuti
            </button>
            <button type="button" onclick="clearSelection()" class="px-3 py-2 text-gray-600 hover:text-gray-800 text-sm">
                Batal
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left font-semibold w-12">
                    <input type="checkbox" id="select-all" class="rounded border-gray-300">
                </th>
                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                <th class="px-4 py-3 text-left font-semibold">NIP</th>
                <th class="px-4 py-3 text-left font-semibold">Role</th>
                <th class="px-4 py-3 text-left font-semibold">Project</th>
                @foreach($leaveTypes as $lt)
                <th class="px-4 py-3 text-center font-semibold">{{ $lt->name }}</th>
                @endforeach
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300">
                    </td>
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->profile?->nip ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($user->role === 'SUPERADMIN') bg-purple-100 text-purple-700
                            @elseif($user->role === 'ADMIN') bg-blue-100 text-blue-700
                            @elseif($user->role === 'PROJECT_ADMIN') bg-cyan-100 text-cyan-700
                            @elseif($user->role === 'HRD') bg-green-100 text-green-700
                            @elseif($user->role === 'PAYROLL') bg-yellow-100 text-yellow-700
                            @elseif($user->role === 'CMS') bg-pink-100 text-pink-700
                            @else bg-gray-100 text-gray-700
                            @endif">
                            {{ $roles[$user->role] ?? $user->role }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->activeProject?->name ?? '-' }}</td>
                    @foreach($leaveTypes as $lt)
                        @php
                            $balance = $user->leaveBalances->firstWhere('leave_type_id', $lt->id);
                            $quota = $balance?->quota ?? 0;
                            $used = $balance?->used ?? 0;
                            $remaining = max(0, $quota - $used);
                        @endphp
                        <td class="px-4 py-3 text-center">
                            @if($balance)
                                <span class="text-sm {{ $remaining > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $remaining }}/{{ $quota }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.leave-balance.show', ['user' => $user, 'year' => $year]) }}" class="text-blue-600 hover:text-blue-800 text-sm">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + count($leaveTypes) }}" class="px-4 py-6 text-center text-gray-500">Belum ada data karyawan.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->appends(['year' => $year, 'project_id' => $projectId ?? '', 'role' => $role ?? ''])->links() }}
        </div>
    </div>

    <!-- Bulk Update Modal -->
    <div id="bulk-update-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <form id="bulk-update-form" method="POST" action="{{ route('admin.leave-balance.bulk-update') }}">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Update Saldo Massal</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Cuti</label>
                        <select name="leave_type_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kuota Baru</label>
                        <input type="number" name="quota" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="user_ids" id="bulk-update-user-ids">
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex gap-3 justify-end">
                    <button type="button" onclick="closeBulkUpdateModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div id="bulk-assign-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <form id="bulk-assign-form" method="POST" action="{{ route('admin.leave-balance.bulk-assign') }}">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Assign Tipe Cuti Massal</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tipe Cuti</label>
                        @foreach($leaveTypes as $lt)
                            <label class="flex items-center gap-2 mb-2">
                                <input type="checkbox" name="leave_type_ids[]" value="{{ $lt->id }}" class="rounded border-gray-300">
                                <span>{{ $lt->name }} ({{ $lt->default_quota }} hari)</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="user_ids" id="bulk-assign-user-ids">
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex gap-3 justify-end">
                    <button type="button" onclick="closeBulkAssignModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Assign</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');

        // Select all functionality
        selectAll?.addEventListener('change', function() {
            userCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        // Individual checkbox change
        userCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const checked = document.querySelectorAll('.user-checkbox:checked');
            selectedCount.textContent = checked.length;
            
            if (checked.length > 0) {
                bulkActions.style.display = 'block';
            } else {
                bulkActions.style.display = 'none';
            }

            // Update select-all indeterminate state
            if (selectAll) {
                selectAll.indeterminate = checked.length > 0 && checked.length < userCheckboxes.length;
                selectAll.checked = checked.length === userCheckboxes.length;
            }
        }
    });

    function getSelectedUserIds() {
        return Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    }

    function showBulkUpdateModal() {
        const userIds = getSelectedUserIds();
        if (userIds.length === 0) return;
        
        document.getElementById('bulk-update-user-ids').value = userIds.join(',');
        document.getElementById('bulk-update-modal').classList.remove('hidden');
    }

    function closeBulkUpdateModal() {
        document.getElementById('bulk-update-modal').classList.add('hidden');
    }

    function showBulkAssignModal() {
        const userIds = getSelectedUserIds();
        if (userIds.length === 0) return;
        
        document.getElementById('bulk-assign-user-ids').value = userIds.join(',');
        document.getElementById('bulk-assign-modal').classList.remove('hidden');
    }

    function closeBulkAssignModal() {
        document.getElementById('bulk-assign-modal').classList.add('hidden');
    }

    function bulkReset() {
        const userIds = getSelectedUserIds();
        if (userIds.length === 0) return;
        
        if (confirm(`Reset saldo cuti untuk ${userIds.length} karyawan terpilih?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.leave-balance.bulk-reset") }}';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            const year = document.createElement('input');
            year.type = 'hidden';
            year.name = 'year';
            year.value = '{{ $year }}';
            form.appendChild(year);
            
            const users = document.createElement('input');
            users.type = 'hidden';
            users.name = 'user_ids';
            users.value = userIds.join(',');
            form.appendChild(users);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function clearSelection() {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        if (document.getElementById('select-all')) {
            document.getElementById('select-all').checked = false;
        }
        document.getElementById('bulk-actions').style.display = 'none';
    }
    </script>
@endsection
