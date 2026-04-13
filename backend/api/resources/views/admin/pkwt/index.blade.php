@extends('layouts.admin')

@section('title', 'PKWT')
@section('page_title', 'Data PKWT')

@section('content')
    {{-- Flash Messages --}}
    @if(session('status'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('status') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter & Search --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
        <form action="{{ route('admin.pkwt.index') }}" method="GET" class="flex flex-wrap gap-4 items-center">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Cari nama, KTP, email..." 
                class="border border-gray-300 rounded px-3 py-2 text-sm w-64">
            
            <select name="project_id" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">-- Semua Project --</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
            
            <select name="status" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">-- Semua Status --</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
                @endforeach
            </select>
            
            <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded text-sm hover:bg-slate-800">Filter</button>
            <a href="{{ route('admin.pkwt.index') }}" class="text-gray-600 hover:text-gray-800 text-sm">Reset</a>
        </form>
    </div>

    {{-- Bulk Actions --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4 hidden" id="bulkActions">
        <form action="{{ route('admin.pkwt.bulkDelete') }}" method="POST" id="bulkDeleteForm" class="flex items-center gap-4">
            @csrf
            @method('DELETE')
            <span class="text-sm text-gray-600"><strong id="selectedCount">0</strong> item dipilih</span>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700" 
                onclick="return confirm('Hapus semua PKWT yang dipilih?')">Hapus Terpilih</button>
        </form>
    </div>

    {{-- PKWT Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs" id="pkwtTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded">
                        </th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Nomor PKWT</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">KTP</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">JK</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">TTL</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Mulai</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Akhir</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Potongan</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cuti</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Template</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pkwtRecords as $index => $pkwt)
                        <tr class="hover:bg-gray-50" data-pkwt-id="{{ $pkwt->id }}">
                            <td class="px-3 py-3">
                                <input type="checkbox" class="pkwt-checkbox rounded" name="ids[]" value="{{ $pkwt->id }}" form="bulkDeleteForm">
                            </td>
                            <td class="px-3 py-3">{{ $pkwtRecords->firstItem() + $index }}</td>
                            <td class="px-3 py-3 font-mono font-semibold">{{ $pkwt->pkwt_number }}</td>
                            <td class="px-3 py-3">{{ $pkwt->ktp_number }}</td>
                            <td class="px-3 py-3 font-medium">{{ $pkwt->name }}</td>
                            <td class="px-3 py-3">{{ $pkwt->gender }}</td>
                            <td class="px-3 py-3 whitespace-nowrap">{{ $pkwt->ttl }}</td>
                            <td class="px-3 py-3 max-w-xs truncate" title="{{ $pkwt->address }}">{{ Str::limit($pkwt->address, 30) }}</td>
                            
                            {{-- Jabatan Dropdown --}}
                            <td class="px-3 py-3">
                                <form action="{{ route('admin.pkwt.update', $pkwt) }}" method="POST" class="inline-update-form">
                                    @csrf
                                    @method('PUT')
                                    <select name="position_id" class="inline-select border-gray-200 rounded px-2 py-1 text-xs" onchange="this.form.submit()">
                                        <option value="">-- Pilih --</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" @selected($pkwt->position_id == $position->id)>{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <button type="button" class="text-blue-600 text-xs ml-1" onclick="openModal('positionModal')">+</button>
                            </td>
                            
                            {{-- Unit Dropdown --}}
                            <td class="px-3 py-3">
                                <form action="{{ route('admin.pkwt.update', $pkwt) }}" method="POST" class="inline-update-form">
                                    @csrf
                                    @method('PUT')
                                    <select name="project_id" class="inline-select border-gray-200 rounded px-2 py-1 text-xs" onchange="this.form.submit()">
                                        <option value="">-- Pilih --</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" @selected($pkwt->project_id == $project->id)>{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            
                            <td class="px-3 py-3">{{ $pkwt->email }}</td>
                            
                            {{-- Contract Start --}}
                            <td class="px-3 py-3">
                                <form action="{{ route('admin.pkwt.update', $pkwt) }}" method="POST" class="inline-update-form">
                                    @csrf
                                    @method('PUT')
                                    <input type="date" name="contract_start" value="{{ $pkwt->contract_start?->format('Y-m-d') }}" 
                                        class="inline-input border-gray-200 rounded px-2 py-1 text-xs w-28" onchange="this.form.submit()">
                                </form>
                            </td>
                            
                            {{-- Contract End --}}
                            <td class="px-3 py-3">
                                <form action="{{ route('admin.pkwt.update', $pkwt) }}" method="POST" class="inline-update-form">
                                    @csrf
                                    @method('PUT')
                                    <input type="date" name="contract_end" value="{{ $pkwt->contract_end?->format('Y-m-d') }}" 
                                        class="inline-input border-gray-200 rounded px-2 py-1 text-xs w-28" onchange="this.form.submit()">
                                </form>
                            </td>
                            
                            {{-- Pendapatan --}}
                            <td class="px-3 py-3">
                                <button type="button" class="text-blue-600 hover:underline text-xs" 
                                    onclick="openIncomeModal({{ $pkwt->id }}, {{ json_encode($pkwt->incomes->pluck('amount', 'pkwt_income_type_id')) }})">
                                    Rp {{ number_format($pkwt->total_income, 0, ',', '.') }}
                                </button>
                            </td>
                            
                            {{-- Potongan --}}
                            <td class="px-3 py-3">
                                <button type="button" class="text-red-600 hover:underline text-xs" 
                                    onclick="openDeductionModal({{ $pkwt->id }}, {{ json_encode($pkwt->deductions->pluck('amount', 'pkwt_deduction_type_id')) }})">
                                    Rp {{ number_format($pkwt->total_deduction, 0, ',', '.') }}
                                </button>
                            </td>
                            
                            {{-- Cuti Dropdown --}}
                            <td class="px-3 py-3">
                                <form action="{{ route('admin.pkwt.update', $pkwt) }}" method="POST" class="inline-update-form">
                                    @csrf
                                    @method('PUT')
                                    <select name="leave_type_id" class="inline-select border-gray-200 rounded px-2 py-1 text-xs" onchange="this.form.submit()">
                                        <option value="">-- Pilih --</option>
                                        @foreach($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}" @selected($pkwt->leave_type_id == $leaveType->id)>{{ $leaveType->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            
                            {{-- Status --}}
                            <td class="px-3 py-3">
                                <form action="{{ route('admin.pkwt.update', $pkwt) }}" method="POST" class="inline-update-form">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="inline-select border-gray-200 rounded px-2 py-1 text-xs status-{{ $pkwt->status_color }}" onchange="this.form.submit()">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" @selected($pkwt->status == $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            
                            {{-- Template --}}
                            <td class="px-3 py-3">
                                @if($pkwt->project && $pkwt->project->pkwt_template)
                                    <span class="text-green-600 text-xs">✓ Ada</span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            
                            {{-- Actions --}}
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="flex gap-1">
                                    <a href="{{ route('admin.pkwt.preview', $pkwt) }}" 
                                        class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200" title="Preview">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    
                                    <form action="{{ route('admin.pkwt.send', $pkwt) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200" title="Kirim">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </form>
                                    
                                    <a href="{{ route('admin.pkwt.print', $pkwt) }}" target="_blank"
                                        class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200" title="Cetak">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </a>
                                    
                                    @if($pkwt->status === 'draft')
                                        <form action="{{ route('admin.pkwt.activate', $pkwt) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs hover:bg-indigo-200" 
                                                title="Aktifkan" onclick="return confirm('Aktifkan PKWT ini? User karyawan akan dibuat.')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(!$pkwt->user_id)
                                        <form action="{{ route('admin.pkwt.destroy', $pkwt) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200" 
                                                title="Hapus" onclick="return confirm('Hapus PKWT ini?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="px-6 py-12 text-center text-gray-500">
                                Belum ada data PKWT. Data akan muncul ketika pelamar diterima.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="p-4 border-t">
            {{ $pkwtRecords->links() }}
        </div>
    </div>

    {{-- Income Modal --}}
    <div id="incomeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Detail Pendapatan</h3>
                <button type="button" onclick="closeModal('incomeModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="incomeForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4 mb-4">
                    @foreach($incomeTypes as $type)
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">{{ $type->name }}</label>
                            <input type="number" name="incomes[{{ $type->id }}]" class="income-input w-full border rounded px-3 py-2 text-sm" 
                                data-type-id="{{ $type->id }}" placeholder="0" min="0">
                        </div>
                    @endforeach
                </div>
                <div class="border-t pt-4 mt-4">
                    <button type="button" onclick="openModal('addIncomeTypeModal')" class="text-blue-600 text-sm hover:underline">+ Tambah Jenis Pendapatan</button>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('incomeModal')" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Deduction Modal --}}
    <div id="deductionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Detail Potongan</h3>
                <button type="button" onclick="closeModal('deductionModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form id="deductionForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4 mb-4">
                    @foreach($deductionTypes as $type)
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">{{ $type->name }}</label>
                            <input type="number" name="deductions[{{ $type->id }}]" class="deduction-input w-full border rounded px-3 py-2 text-sm" 
                                data-type-id="{{ $type->id }}" placeholder="0" min="0">
                        </div>
                    @endforeach
                </div>
                <div class="border-t pt-4 mt-4">
                    <button type="button" onclick="openModal('addDeductionTypeModal')" class="text-blue-600 text-sm hover:underline">+ Tambah Jenis Potongan</button>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('deductionModal')" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Position Modal --}}
    <div id="positionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Tambah Jabatan</h3>
                <button type="button" onclick="closeModal('positionModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form action="{{ route('admin.pkwt.positions.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="Contoh: Security Guard" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('positionModal')" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Income Type Modal --}}
    <div id="addIncomeTypeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Tambah Jenis Pendapatan</h3>
                <button type="button" onclick="closeModal('addIncomeTypeModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form action="{{ route('admin.pkwt.income-types.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Pendapatan</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="Contoh: Tunjangan Khusus" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('addIncomeTypeModal')" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Deduction Type Modal --}}
    <div id="addDeductionTypeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Tambah Jenis Potongan</h3>
                <button type="button" onclick="closeModal('addDeductionTypeModal')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form action="{{ route('admin.pkwt.deduction-types.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Potongan</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="Contoh: Potongan Koperasi" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('addDeductionTypeModal')" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.getElementById(modalId).classList.add('flex');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.getElementById(modalId).classList.remove('flex');
    }

    // Income modal
    function openIncomeModal(pkwtId, incomes) {
        const form = document.getElementById('incomeForm');
        form.action = `/admin/pkwt/${pkwtId}`;
        
        // Reset all inputs
        document.querySelectorAll('.income-input').forEach(input => {
            input.value = '';
        });
        
        // Set values from existing data
        if (incomes) {
            Object.entries(incomes).forEach(([typeId, amount]) => {
                const input = document.querySelector(`.income-input[data-type-id="${typeId}"]`);
                if (input) input.value = amount;
            });
        }
        
        openModal('incomeModal');
    }

    // Deduction modal
    function openDeductionModal(pkwtId, deductions) {
        const form = document.getElementById('deductionForm');
        form.action = `/admin/pkwt/${pkwtId}`;
        
        // Reset all inputs
        document.querySelectorAll('.deduction-input').forEach(input => {
            input.value = '';
        });
        
        // Set values from existing data
        if (deductions) {
            Object.entries(deductions).forEach(([typeId, amount]) => {
                const input = document.querySelector(`.deduction-input[data-type-id="${typeId}"]`);
                if (input) input.value = amount;
            });
        }
        
        openModal('deductionModal');
    }

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.pkwt-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActions();
    });

    // Individual checkbox
    document.querySelectorAll('.pkwt-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checked = document.querySelectorAll('.pkwt-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (checked.length > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.textContent = checked.length;
        } else {
            bulkActions.classList.add('hidden');
        }
    }

    // Close modal on outside click
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
</script>

<style>
    .inline-select, .inline-input {
        background: transparent;
        border: 1px solid transparent;
    }
    .inline-select:hover, .inline-input:hover,
    .inline-select:focus, .inline-input:focus {
        border-color: #d1d5db;
        background: white;
    }
    .status-green { background-color: #dcfce7; color: #166534; }
    .status-blue { background-color: #dbeafe; color: #1e40af; }
    .status-gray { background-color: #f3f4f6; color: #374151; }
    .status-yellow { background-color: #fef9c3; color: #854d0e; }
    .status-red { background-color: #fee2e2; color: #991b1b; }
    .status-indigo { background-color: #e0e7ff; color: #3730a3; }
</style>
@endpush
