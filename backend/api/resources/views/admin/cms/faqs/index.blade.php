@extends('layouts.admin')

@section('title', 'FAQ')
@section('page_title', 'Manajemen FAQ')

@section('content')
<div class="bg-white rounded shadow">
    <div class="p-4 border-b flex items-center justify-between">
        <a href="{{ route('admin.cms-faqs.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            + Tambah FAQ
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left w-12">Order</th>
                    <th class="px-4 py-3 text-left">Pertanyaan</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($faqs as $faq)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-center text-gray-500">{{ $faq->order }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $faq->question }}</div>
                        <div class="text-gray-500 text-xs mt-1 line-clamp-2">{{ Str::limit(strip_tags($faq->answer), 100) }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($faq->category)
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">{{ $faq->category }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($faq->is_active)
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Aktif</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.cms-faqs.edit', $faq) }}" class="text-blue-600 hover:text-blue-800" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.cms-faqs.destroy', $faq) }}" onsubmit="return confirm('Hapus FAQ ini?')" class="inline">
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
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada FAQ.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($faqs->hasPages())
    <div class="px-4 py-3 border-t">
        {{ $faqs->links() }}
    </div>
    @endif
</div>
@endsection
