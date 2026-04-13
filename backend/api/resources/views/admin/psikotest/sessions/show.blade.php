@extends('layouts.admin')

@section('title', 'Detail Sesi Test')
@section('page_title', 'Detail Sesi Test')

@section('content')
<div class="max-w-2xl space-y-4">
    <a href="{{ route('admin.psikotest.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    {{-- Session Info --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $session->participant_name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $session->participant_email ?? 'Tidak ada email' }} |
                    {{ $session->participant_phone ?? 'Tidak ada HP' }}
                </p>
            </div>
            <div>
                @if($session->status === 'completed')
                    <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">Selesai</span>
                @elseif($session->status === 'in_progress')
                    <span class="px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full">Sedang Dikerjakan</span>
                @elseif($session->isExpired())
                    <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">Kadaluarsa</span>
                @else
                    <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">Menunggu</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <p class="text-gray-500">Jenis Test</p>
                <p class="font-medium capitalize">{{ $session->test_type }}</p>
            </div>
            <div>
                <p class="text-gray-500">Berlaku Sampai</p>
                <p class="font-medium">{{ $session->expires_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Dibuat</p>
                <p class="font-medium">{{ $session->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Selesai</p>
                <p class="font-medium">{{ $session->completed_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
        </div>

        {{-- Test Link --}}
        @if(!$session->isCompleted())
        <div class="bg-purple-50 border border-purple-100 rounded-lg p-4">
            <h4 class="text-sm font-medium text-purple-800 mb-2">Link Test untuk Peserta</h4>
            <div class="flex gap-2">
                <input type="text" id="test-url" value="{{ $testUrl }}" readonly
                    class="flex-1 px-3 py-2 bg-white border border-purple-200 rounded text-sm font-mono">
                <button onclick="copyUrl()" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                    Copy
                </button>
            </div>
            <p class="text-xs text-purple-600 mt-2">
                Token akses: <strong class="font-mono">{{ $session->access_token }}</strong>
            </p>
        </div>
        @endif

        {{-- Results --}}
        @if($session->result)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-800 mb-4">Hasil Test</h4>
            
            @if($session->test_type === 'kraepelin')
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $session->result->speed_score }}</div>
                    <div class="text-xs text-gray-500">Kecepatan</div>
                </div>
                <div class="bg-green-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $session->result->accuracy_percentage }}%</div>
                    <div class="text-xs text-gray-500">Ketelitian</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $session->result->consistency_score }}</div>
                    <div class="text-xs text-gray-500">Konsistensi</div>
                </div>
                <div class="bg-orange-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $session->result->endurance_score }}</div>
                    <div class="text-xs text-gray-500">Ketahanan</div>
                </div>
            </div>
            @else
            <div class="text-sm text-gray-600 mb-2">20 dimensi kepribadian dinilai</div>
            <a href="{{ route('admin.psikotest.result.show', $session) }}" class="text-purple-600 hover:underline text-sm">
                Lihat detail hasil →
            </a>
            @endif
        </div>
        @endif
    </div>
</div>

<script>
    function copyUrl() {
        const input = document.getElementById('test-url');
        input.select();
        document.execCommand('copy');
        alert('Link berhasil disalin!');
    }
</script>
@endsection
