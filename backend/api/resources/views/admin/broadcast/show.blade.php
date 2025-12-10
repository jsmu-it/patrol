@extends('layouts.admin')

@section('title', 'Detail Broadcast')
@section('page_title', 'Detail Broadcast Notifikasi')

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('admin.broadcast.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Kembali ke Daftar</a>
    </div>

    <div class="bg-white rounded shadow">
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-500">Waktu Kirim</label>
                    <p class="font-medium">{{ $broadcast->sent_at ? $broadcast->sent_at->format('d M Y H:i:s') : '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Pengirim</label>
                    <p class="font-medium">{{ $broadcast->sender?->name ?? '-' }}</p>
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-500">Judul</label>
                <p class="font-medium text-lg">{{ $broadcast->title }}</p>
            </div>

            <div>
                <label class="text-sm text-gray-500">Pesan</label>
                <p class="bg-gray-50 rounded p-3 mt-1">{{ $broadcast->message }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-500">Target</label>
                    <p class="font-medium">
                        @if($broadcast->target == 'all')
                            Semua User
                        @elseif($broadcast->target == 'project')
                            Project: {{ $broadcast->targetProject?->name ?? '-' }}
                        @else
                            Role: {{ $broadcast->target_role }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 pt-4 border-t">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-700">{{ $broadcast->recipients_count }}</p>
                    <p class="text-sm text-gray-500">Total Penerima</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $broadcast->success_count }}</p>
                    <p class="text-sm text-gray-500">Berhasil</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $broadcast->failed_count }}</p>
                    <p class="text-sm text-gray-500">Gagal</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
