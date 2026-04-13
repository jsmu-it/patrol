@extends('layouts.admin')

@section('title', 'Meeting')
@section('page_title', 'Meeting')

@section('content')
<div class="bg-white rounded shadow">
    <div class="p-4 border-b flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.meetings.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                + Buat Meeting Baru
            </a>
        </div>
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.meetings.index') }}" class="px-3 py-1.5 rounded {{ !request('status') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Semua
            </a>
            <a href="{{ route('admin.meetings.index', ['status' => 'scheduled']) }}" class="px-3 py-1.5 rounded {{ request('status') === 'scheduled' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Dijadwalkan
            </a>
            <a href="{{ route('admin.meetings.index', ['status' => 'active']) }}" class="px-3 py-1.5 rounded {{ request('status') === 'active' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Aktif
            </a>
            <a href="{{ route('admin.meetings.index', ['status' => 'ended']) }}" class="px-3 py-1.5 rounded {{ request('status') === 'ended' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Selesai
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Judul</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Maks. Peserta</th>
                    <th class="px-4 py-3 text-left">Dijadwalkan</th>
                    <th class="px-4 py-3 text-left">Host</th>
                    <th class="px-4 py-3 text-left">Link</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($meetings as $meeting)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $meeting->title }}</div>
                        @if($meeting->description)
                            <div class="text-gray-500 text-xs mt-1 line-clamp-1">{{ Str::limit($meeting->description, 60) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($meeting->status === 'active')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium inline-flex items-center gap-1">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                Aktif
                            </span>
                        @elseif($meeting->status === 'scheduled')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Dijadwalkan</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">Selesai</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono">{{ $meeting->max_participants }}</td>
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                        {{ $meeting->scheduled_at ? $meeting->scheduled_at->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $meeting->host?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <button onclick="copyLink('{{ $meeting->join_url }}')" class="text-blue-600 hover:text-blue-800 text-xs underline" title="Copy link meeting">
                            Copy Link
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($meeting->status !== 'ended')
                                <a href="{{ route('admin.meetings.room', $meeting) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700" title="Masuk Meeting">
                                    Masuk
                                </a>
                            @endif

                            <form method="POST" action="{{ route('admin.meetings.toggle-status', $meeting) }}" class="inline">
                                @csrf
                                @if($meeting->status === 'active')
                                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded text-xs hover:bg-red-700" onclick="return confirm('Akhiri meeting ini?')">
                                        Akhiri
                                    </button>
                                @else
                                    <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded text-xs hover:bg-green-700">
                                        Mulai
                                    </button>
                                @endif
                            </form>

                            <a href="{{ route('admin.meetings.edit', $meeting) }}" class="text-blue-600 hover:text-blue-800" title="Edit">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>

                            <form method="POST" action="{{ route('admin.meetings.destroy', $meeting) }}" class="inline" onsubmit="return confirm('Hapus meeting ini?')">
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
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada meeting.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($meetings->hasPages())
    <div class="px-4 py-3 border-t">
        {{ $meetings->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link meeting berhasil disalin!');
    }).catch(() => {
        prompt('Copy link ini:', url);
    });
}
</script>
@endpush
