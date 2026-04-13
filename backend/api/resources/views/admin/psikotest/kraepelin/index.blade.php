@extends('layouts.admin')

@section('title', 'Soal Kraepelin')
@section('page_title', 'Soal Kraepelin')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.psikotest.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <a href="{{ route('admin.psikotest.kraepelin.create') }}" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Soal Baru
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kolom</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Baris/Kolom</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu/Kolom</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($questions as $question)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $question->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $question->column_count }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $question->rows_per_column }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $question->time_per_column_seconds }} detik</td>
                    <td class="px-4 py-3">
                        @if($question->is_active)
                            <span class="px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.psikotest.kraepelin.show', $question) }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat</a>
                            <a href="{{ route('admin.psikotest.kraepelin.edit', $question) }}" class="text-gray-600 hover:text-gray-800 text-sm">Edit</a>
                            <form action="{{ route('admin.psikotest.kraepelin.regenerate', $question) }}" method="POST" class="inline" onsubmit="return confirm('Yakin regenerate angka? Ini akan mengacak ulang semua angka.')">
                                @csrf
                                <button type="submit" class="text-yellow-600 hover:text-yellow-800 text-sm">Regenerate</button>
                            </form>
                            <form action="{{ route('admin.psikotest.kraepelin.destroy', $question) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus soal ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Belum ada soal Kraepelin. <a href="{{ route('admin.psikotest.kraepelin.create') }}" class="text-blue-600 hover:underline">Buat soal baru</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $questions->links() }}
</div>
@endsection
