@extends('layouts.admin')

@section('title', 'Detail Soal Papikostik')
@section('page_title', 'Detail Soal Papikostik')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.psikotest.papikostik.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <a href="{{ route('admin.psikotest.papikostik.edit', $papikostik) }}" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm">Edit</a>
            <form action="{{ route('admin.psikotest.papikostik.destroy', $papikostik) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus soal ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Hapus</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $papikostik->name }}</h2>
                <p class="text-sm text-gray-500">{{ count($papikostik->pairs ?? []) }} pasangan soal</p>
            </div>
            <div>
                @if($papikostik->is_active)
                    <span class="px-3 py-1 text-sm font-medium bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                @else
                    <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                @endif
            </div>
        </div>

        {{-- Dimensions Legend --}}
        <div class="bg-purple-50 border border-purple-100 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-medium text-purple-800 mb-2">Legenda Dimensi</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                @foreach($dimensions as $code => $info)
                    <div class="flex items-center gap-1">
                        <span class="font-mono font-bold text-purple-600 bg-purple-100 px-1.5 py-0.5 rounded">{{ $code }}</span>
                        <span class="text-gray-600">{{ $info['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Questions List --}}
        <div class="space-y-3">
            <h4 class="text-sm font-medium text-gray-800">Daftar Soal</h4>
            @forelse($papikostik->pairs ?? [] as $index => $pair)
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-medium text-gray-400">Pasangan #{{ $index + 1 }}</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-start gap-2">
                        <span class="font-mono font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded text-sm">A</span>
                        <div>
                            <p class="text-sm text-gray-700">{{ $pair['statement_a'] ?? '-' }}</p>
                            <span class="text-xs text-gray-500">Dimensi: <strong class="text-purple-600">{{ $pair['dimension_a'] ?? '-' }}</strong></span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="font-mono font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded text-sm">B</span>
                        <div>
                            <p class="text-sm text-gray-700">{{ $pair['statement_b'] ?? '-' }}</p>
                            <span class="text-xs text-gray-500">Dimensi: <strong class="text-purple-600">{{ $pair['dimension_b'] ?? '-' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-8">Tidak ada soal.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
