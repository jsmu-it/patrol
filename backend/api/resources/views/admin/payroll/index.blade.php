@extends('layouts.admin')

@section('title', 'Payroll - Slip Gaji')
@section('page_title', 'Payroll - Slip Gaji')

@section('content')
<div class="bg-white rounded shadow">
    <div class="p-4 border-b flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.payroll.import.form') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                Upload Excel
            </a>
            <a href="{{ route('admin.payroll.template') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm">
                Download Template
            </a>
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="period" class="border rounded px-3 py-2 text-sm">
                <option value="">-- Semua Periode --</option>
                @foreach($periods as $period)
                    <option value="{{ $period }}" @selected(request('period') == $period)>{{ $period }}</option>
                @endforeach
            </select>
            <select name="unit" class="border rounded px-3 py-2 text-sm">
                <option value="">-- Semua Unit --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit }}" @selected(request('unit') == $unit)>{{ $unit }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/NIP..." class="border rounded px-3 py-2 text-sm w-48">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-900 text-sm">Filter</button>
            @if(request()->hasAny(['period', 'unit', 'search']))
                <a href="{{ route('admin.payroll.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 text-sm">Reset</a>
            @endif
        </form>
    </div>

    @if(request('period'))
    <div class="px-4 py-2 bg-yellow-50 border-b flex items-center justify-between">
        <span class="text-sm text-yellow-800">Periode: <strong>{{ request('period') }}</strong></span>
        <form method="POST" action="{{ route('admin.payroll.destroy-period') }}" onsubmit="return confirm('Hapus semua slip gaji periode {{ request('period') }}?')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="period" value="{{ request('period') }}">
            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Hapus Semua Periode Ini</button>
        </form>
    </div>
    @endif

    <form id="bulk-form" method="POST" action="{{ route('admin.payroll.print-bulk') }}" target="_blank">
        @csrf
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" id="select-all" class="rounded">
                        </th>
                        <th class="px-4 py-3 text-left">NIP</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Unit / Site</th>
                        <th class="px-4 py-3 text-left">Jabatan</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-right">Pendapatan</th>
                        <th class="px-4 py-3 text-right">Potongan</th>
                        <th class="px-4 py-3 text-right">Diterima</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($slips as $slip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $slip->id }}" class="slip-checkbox rounded">
                        </td>
                        <td class="px-4 py-3 font-mono">{{ $slip->nip }}</td>
                        <td class="px-4 py-3 font-medium">{{ $slip->name }}</td>
                        <td class="px-4 py-3">{{ $slip->unit ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $slip->position ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $slip->period_month }}</td>
                        <td class="px-4 py-3 text-right font-mono">{{ number_format($slip->total_income, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-red-600">{{ number_format($slip->total_deduction, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono font-semibold text-blue-600">{{ number_format($slip->net_income, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.payroll.show', $slip) }}" class="text-blue-600 hover:text-blue-800" title="Lihat">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.payroll.print', $slip) }}" target="_blank" class="text-green-600 hover:text-green-800" title="Print">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.payroll.destroy', $slip) }}" onsubmit="return confirm('Hapus slip gaji ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            Belum ada data slip gaji. <a href="{{ route('admin.payroll.import.form') }}" class="text-blue-600 hover:underline">Upload Excel</a> untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($slips->count() > 0)
        <div class="px-4 py-3 border-t flex items-center justify-between">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm disabled:opacity-50" id="btn-print-bulk" disabled>
                Print Terpilih (<span id="selected-count">0</span>)
            </button>
            <div>
                {{ $slips->links() }}
            </div>
        </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.slip-checkbox');
    const btnPrint = document.getElementById('btn-print-bulk');
    const countSpan = document.getElementById('selected-count');

    function updateCount() {
        const checked = document.querySelectorAll('.slip-checkbox:checked').length;
        countSpan.textContent = checked;
        btnPrint.disabled = checked === 0;
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateCount();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });
});
</script>
@endpush
@endsection
