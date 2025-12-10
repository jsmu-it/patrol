@extends('layouts.admin')

@section('title', 'Testimoni')
@section('page_title', 'Manajemen Testimoni')

@section('content')
<div class="bg-white rounded shadow">
    <div class="p-4 border-b flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.cms-testimonials.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                + Tambah Testimoni
            </a>
            <form method="POST" action="{{ route('admin.cms-testimonials.generate-link') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Generate Link Form
                </button>
            </form>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <select name="status" class="border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                <option value="approved" @selected(request('status') == 'approved')>Approved</option>
                <option value="rejected" @selected(request('status') == 'rejected')>Rejected</option>
            </select>
        </form>
    </div>

    @if(session('testimonial_link'))
    <div class="px-4 py-3 bg-green-50 border-b">
        <p class="text-sm text-green-800">Link form testimoni:</p>
        <div class="flex items-center gap-2 mt-1">
            <input type="text" value="{{ session('testimonial_link') }}" readonly class="flex-1 border rounded px-3 py-2 text-sm bg-white" id="testimonial-link">
            <button onclick="navigator.clipboard.writeText(document.getElementById('testimonial-link').value); alert('Link copied!')" class="px-3 py-2 bg-gray-600 text-white rounded text-sm">Copy</button>
        </div>
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-left">Perusahaan</th>
                    <th class="px-4 py-3 text-left">Testimoni</th>
                    <th class="px-4 py-3 text-center">Rating</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Featured</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($testimonials as $testimonial)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($testimonial->client_photo)
                            <img src="{{ asset('storage/' . $testimonial->client_photo) }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                            </div>
                            @endif
                            <div>
                                <div class="font-medium">{{ $testimonial->client_name ?: '(Belum diisi)' }}</div>
                                <div class="text-xs text-gray-500">{{ $testimonial->client_position }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $testimonial->client_company ?: '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="max-w-xs truncate">{{ $testimonial->content ?: '(Belum diisi)' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $testimonial->rating)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endif
                            @endfor
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($testimonial->status == 'approved')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Approved</span>
                        @elseif($testimonial->status == 'rejected')
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Rejected</span>
                        @else
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($testimonial->is_featured)
                            <span class="text-yellow-500">&#9733;</span>
                        @else
                            <span class="text-gray-300">&#9733;</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            @if($testimonial->status == 'pending')
                            <form method="POST" action="{{ route('admin.cms-testimonials.approve', $testimonial) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800" title="Approve">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.cms-testimonials.reject', $testimonial) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Reject">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('admin.cms-testimonials.edit', $testimonial) }}" class="text-blue-600 hover:text-blue-800" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.cms-testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Hapus testimoni ini?')" class="inline">
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
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada testimoni.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($testimonials->hasPages())
    <div class="px-4 py-3 border-t">
        {{ $testimonials->links() }}
    </div>
    @endif
</div>
@endsection
