@extends('layouts.admin')

@section('title', 'Soal Papikostik')
@section('page_title', 'Soal Papikostik')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.psikotest.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <form action="{{ route('admin.psikotest.papikostik.generate') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Generate Soal Standar
                </button>
            </form>
            <a href="{{ route('admin.psikotest.papikostik.create') }}" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Manual
            </a>
        </div>
    </div>

    {{-- Dimensions Info --}}
    <div class="bg-purple-50 border border-purple-100 rounded-lg p-4">
        <h4 class="text-sm font-medium text-purple-800 mb-2">20 Dimensi PAPIKOSTIK</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
            @foreach($dimensions as $code => $info)
                <div class="flex items-center gap-1">
                    <span class="font-mono font-bold text-purple-600">{{ $code }}</span>
                    <span class="text-gray-600">{{ $info['name'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Pasangan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dibuat</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($questions as $question)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $question->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ count($question->pairs ?? []) }} pasangan</td>
                    <td class="px-4 py-3">
                        @if($question->is_active)
                            <span class="px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $question->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.psikotest.papikostik.show', $question) }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat</a>
                            <a href="{{ route('admin.psikotest.papikostik.edit', $question) }}" class="text-gray-600 hover:text-gray-800 text-sm">Edit</a>
                            <form action="{{ route('admin.psikotest.papikostik.destroy', $question) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus soal ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        Belum ada soal Papikostik. 
                        <form action="{{ route('admin.psikotest.papikostik.generate') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-purple-600 hover:underline">Klik untuk generate soal standar</button>
                        </form>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $questions->links() }}
</div>
@endsection
