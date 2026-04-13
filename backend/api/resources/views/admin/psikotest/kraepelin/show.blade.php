@extends('layouts.admin')

@section('title', 'Detail Soal Kraepelin')
@section('page_title', 'Detail Soal Kraepelin')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.psikotest.kraepelin.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <form action="{{ route('admin.psikotest.kraepelin.regenerate', $kraepelin) }}" method="POST" class="inline" onsubmit="return confirm('Yakin regenerate angka? Ini akan mengacak ulang semua angka.')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm">Regenerate Angka</button>
            </form>
            <a href="{{ route('admin.psikotest.kraepelin.edit', $kraepelin) }}" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm">Edit</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $kraepelin->name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $kraepelin->column_count }} kolom × {{ $kraepelin->rows_per_column }} baris | 
                    {{ $kraepelin->time_per_column_seconds }} detik/kolom
                </p>
            </div>
            <div>
                @if($kraepelin->is_active)
                    <span class="px-3 py-1 text-sm font-medium bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                @else
                    <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                @endif
            </div>
        </div>

        {{-- Preview of columns --}}
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-medium text-blue-800 mb-2">Preview Kolom (10 kolom pertama, 10 baris pertama)</h4>
            <p class="text-xs text-blue-600 mb-4">Peserta test akan menjumlahkan dua angka yang berdekatan vertikal dan menulis hasilnya (digit satuan jika >= 10)</p>
        </div>

        <div class="overflow-x-auto">
            <div class="flex gap-2">
                @php $columns = $kraepelin->columns_data ?? []; @endphp
                @for($col = 0; $col < min(10, count($columns)); $col++)
                <div class="flex flex-col items-center">
                    <div class="text-xs font-medium text-gray-400 mb-2">K{{ $col + 1 }}</div>
                    @for($row = 0; $row < min(10, count($columns[$col] ?? [])); $row++)
                    <div class="w-8 h-8 flex items-center justify-center text-lg font-mono font-bold 
                        {{ $row % 2 === 0 ? 'bg-gray-100' : 'bg-white' }} border border-gray-200">
                        {{ $columns[$col][$row] ?? '-' }}
                    </div>
                    @endfor
                    <div class="text-xs text-gray-400 mt-1">...</div>
                </div>
                @endfor
                @if(count($columns) > 10)
                <div class="flex items-center text-gray-400 text-2xl px-4">...</div>
                @endif
            </div>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            Total: {{ count($columns) }} kolom × {{ count($columns[0] ?? []) }} baris = {{ count($columns) * count($columns[0] ?? []) }} angka
        </div>
    </div>
</div>
@endsection
