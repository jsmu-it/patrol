@extends('layouts.admin')

@section('title', 'Pemberitahuan')
@section('page_title', 'Pemberitahuan')

@section('content')
<div class="bg-white rounded shadow">
    <div class="p-4 border-b flex items-center justify-between">
        <a href="{{ route('admin.broadcast.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            + Kirim Pemberitahuan Baru
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Gambar</th>
                    <th class="px-4 py-3 text-left">Judul</th>
                    <th class="px-4 py-3 text-left">Target</th>
                    <th class="px-4 py-3 text-center">Penerima</th>
                    <th class="px-4 py-3 text-center">Berhasil</th>
                    <th class="px-4 py-3 text-center">Gagal</th>
                    <th class="px-4 py-3 text-left">Pengirim</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($notifications as $notif)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                        {{ $notif->sent_at ? $notif->sent_at->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($notif->image_url)
                            <img src="{{ $notif->image_url }}" alt="Gambar" class="h-10 w-10 object-cover rounded">
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $notif->title }}</div>
                        <div class="text-gray-500 text-xs mt-1 line-clamp-1">{{ Str::limit($notif->message, 50) }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($notif->target == 'all')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Semua User</span>
                        @elseif($notif->target == 'project')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">{{ $notif->targetProject?->name ?? 'Project' }}</span>
                        @else
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">{{ $notif->target_role }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono">{{ $notif->recipients_count }}</td>
                    <td class="px-4 py-3 text-center font-mono text-green-600">{{ $notif->success_count }}</td>
                    <td class="px-4 py-3 text-center font-mono text-red-600">{{ $notif->failed_count }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $notif->sender?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.broadcast.show', $notif) }}" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.broadcast.destroy', $notif) }}" class="inline" onsubmit="return confirm('Hapus pemberitahuan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">Belum ada pemberitahuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($notifications->hasPages())
    <div class="px-4 py-3 border-t">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
