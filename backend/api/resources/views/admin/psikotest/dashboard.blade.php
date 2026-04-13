@extends('layouts.admin')

@section('title', 'Dashboard Hasil Test')
@section('page_title', 'Dashboard Hasil Test')

@section('content')
<div class="space-y-4">
    <a href="{{ route('admin.psikotest.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <form action="" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama peserta..."
                    class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Test</label>
                <select name="test_type" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="kraepelin" {{ request('test_type') === 'kraepelin' ? 'selected' : '' }}>Kraepelin</option>
                    <option value="papikostik" {{ request('test_type') === 'papikostik' ? 'selected' : '' }}>Papikostik</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-md hover:bg-slate-800 text-sm">Filter</button>
            @if(request()->hasAny(['search', 'test_type']))
                <a href="{{ route('admin.psikotest.dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm">Reset</a>
            @endif
        </form>
    </div>

    {{-- Results Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peserta</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Test</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hasil</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($sessions as $session)
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ $session->participant_name }}</div>
                        @if($session->participant_email)
                            <div class="text-xs text-gray-500">{{ $session->participant_email }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($session->test_type === 'kraepelin')
                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Kraepelin</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">Papikostik</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $session->completed_at?->format('d M Y H:i') ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($session->result)
                            @if($session->test_type === 'kraepelin')
                                <div class="text-sm">
                                    <span class="text-gray-600">Akurasi:</span>
                                    <span class="font-medium {{ $session->result->accuracy_percentage >= 80 ? 'text-emerald-600' : ($session->result->accuracy_percentage >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $session->result->accuracy_percentage }}%
                                    </span>
                                </div>
                            @else
                                <span class="text-sm text-gray-600">20 dimensi dinilai</span>
                            @endif
                        @else
                            <span class="text-sm text-gray-400">Tidak ada hasil</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.psikotest.result.show', $session) }}" class="text-blue-600 hover:text-blue-800 text-sm">Detail</a>
                            <a href="{{ route('admin.psikotest.result.pdf', $session) }}" class="text-emerald-600 hover:text-emerald-800 text-sm">PDF</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        Belum ada hasil test.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sessions->links() }}
</div>
@endsection
