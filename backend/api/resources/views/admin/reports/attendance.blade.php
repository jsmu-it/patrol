@extends('layouts.admin')

@section('page_title', 'Laporan Absensi')

@section('content')
<div class="bg-white rounded shadow-sm p-6 mb-6">
    <form action="{{ route('admin.reports.attendance') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ optional($filters['from'] ?? null)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ optional($filters['to'] ?? null)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-md shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
            <select name="project_id" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Semua Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ ($filters['project_id'] ?? '') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex-1">Filter</button>
            <a href="{{ route('admin.reports.attendance') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </form>
</div>

@if(isset($employees) && $employees->count() > 0)
<div class="bg-white rounded shadow-sm mb-6">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Hasil Laporan</h3>
        <div class="flex gap-2 items-center">
            <!-- Template Selector -->
            <select id="templateSelect" class="border-gray-300 rounded text-sm px-2 py-1.5">
                <option value="standard">Template Standard</option>
                <option value="pivot">Template Pivot (Matrix)</option>
                <option value="ho">Format HO</option>
                <option value="summarecon_bogor">Summarecon Bogor</option>
            </select>
            
            <!-- Excel Export with template parameter -->
            <a href="#" id="excelExport" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700 flex items-center gap-1">
                <span>Excel</span>
            </a>
            
            <a href="{{ route('admin.reports.attendance.exportPdf', request()->all()) }}" class="px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700 flex items-center gap-1">
                <span>PDF</span>
            </a>
        </div>
    </div>
    
    <script>
    const templateSelect = document.getElementById('templateSelect');
    
    function updateExcelLinks() {
        const template = templateSelect.value;
        
        // Update main excel export link
        const excelExport = document.getElementById('excelExport');
        const mainUrl = new URL(excelExport.href, window.location.origin);
        mainUrl.searchParams.set('template', template);
        excelExport.href = mainUrl.toString();
        
        // Update all individual download links
        const individualLinks = document.querySelectorAll('.download-user-btn');
        individualLinks.forEach(link => {
            const url = new URL(link.href, window.location.origin);
            url.searchParams.set('template', template);
            link.href = url.toString();
        });
    }

    templateSelect.addEventListener('change', updateExcelLinks);
    
    // Initialize links on load
    document.addEventListener('DOMContentLoaded', updateExcelLinks);

    document.getElementById('excelExport').addEventListener('click', function(e) {
        updateExcelLinks();
    });
    </script>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Project</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($employees as $employee)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">{{ $loop->iteration }}</td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $employee->name }}</td>
                    <td class="px-6 py-3">{{ $employee->activeProject?->name ?? '-' }}</td>
                    <td class="px-6 py-3">
                        <a href="{{ route('admin.reports.attendance.downloadUser', ['user_id' => $employee->id, 'from' => request('from'), 'to' => request('to'), 'project_id' => request('project_id')]) }}" 
                           class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700 download-user-btn">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif(request()->filled('from'))
<div class="bg-white rounded shadow-sm p-8 text-center text-gray-500">
    Tidak ada karyawan ditemukan untuk project ini.
</div>
@endif
@endsection

