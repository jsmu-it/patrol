@extends('layouts.sharing')

@section('title', 'File Sharing')
@section('page_title', 'File Sharing')

@section('content')
<div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <nav class="flex text-gray-700 overflow-x-auto whitespace-nowrap pb-2" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('sharing.index') }}" class="inline-flex items-center text-sm font-medium hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Root
                </a>
            </li>
            @foreach($breadcrumbs as $bc)
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('sharing.index', ['folder' => $bc->id]) }}" class="ml-1 text-sm font-medium hover:text-blue-600 md:ml-2">{{ $bc->name }}</a>
                </div>
            </li>
            @endforeach
        </ol>
    </nav>

    <div class="flex items-center space-x-2 shrink-0">
        <button onclick="openModal('folderModal')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
            New Folder
        </button>
        <button onclick="openModal('uploadModal')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Upload
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($item->type === 'folder')
                            <svg class="w-8 h-8 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                            <a href="{{ route('sharing.index', ['folder' => $item->id]) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600">{{ $item->name }}</a>
                            @if($item->password)
                            <svg class="w-4 h-4 text-amber-500 ml-2" fill="currentColor" viewBox="0 0 20 20" title="Password protected"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                            @endif
                            @else
                            <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="text-sm text-gray-900">{{ $item->name }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->human_size }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->uploader?->name ?? 'Anonymous' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            @if($item->type === 'file')
                                <a href="{{ route('sharing.download', $item) }}" class="text-blue-600 hover:text-blue-900" title="Download">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4-4v12"></path></svg>
                                </a>
                            @endif
                            @auth
                            <form action="{{ route('sharing.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endauth
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 whitespace-nowrap text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            <p class="text-lg font-medium">Folder ini kosong</p>
                            <p class="text-sm">Silakan buat folder baru atau upload file.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Folder Modal -->
<div id="folderModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg font-medium text-gray-900">New Folder</h3>
            <button onclick="closeModal('folderModal')" class="text-gray-400 hover:text-gray-500 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('sharing.folder') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
            <div class="mb-4">
                <label for="folder_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Folder</label>
                <input type="text" name="name" id="folder_name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required autofocus placeholder="Masukkan nama folder">
            </div>
            <div class="mb-4">
                <label for="folder_password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="folder_password" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required placeholder="Buat password untuk folder ini" minlength="4">
                <p class="mt-1 text-xs text-gray-500">Minimal 4 karakter. Password ini diperlukan untuk membuka dan download isi folder.</p>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('folderModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg font-medium text-gray-900">Upload File</h3>
            <button onclick="closeModal('uploadModal')" class="text-gray-400 hover:text-gray-500 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('sharing.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
            <div class="mb-4">
                <label for="file_input" class="block text-sm font-medium text-gray-700 mb-1">Select File</label>
                <input type="file" name="file" id="file_input" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition" required>
                <p class="mt-1 text-xs text-gray-500">Max size: 20MB</p>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('uploadModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Unlock Folder Modal (auto-opens when a locked folder is clicked) -->
<div id="unlockModal" class="@if(!$unlockFolderId) hidden @endif fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg font-medium text-gray-900">🔒 Password Required</h3>
            <button onclick="closeModal('unlockModal')" class="text-gray-400 hover:text-gray-500 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        @if($unlockFolderId)
        <form action="{{ route('sharing.unlock', $unlockFolderId) }}" method="POST">
            @csrf
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-3">Masukkan password untuk membuka folder <strong>"{{ $unlockFolderName }}"</strong></p>
                <input type="password" name="password" id="unlock_password" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required placeholder="Password folder" autofocus>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('unlockModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Buka Folder</button>
            </div>
        </form>
        @endif
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    // Close modal on escape key
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('folderModal');
            closeModal('uploadModal');
            closeModal('unlockModal');
        }
    });
    // Auto-focus unlock password if modal is open
    @if($unlockFolderId)
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => document.getElementById('unlock_password')?.focus(), 100);
    });
    @endif
</script>
@endsection
