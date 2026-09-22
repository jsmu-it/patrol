@extends('layouts.admin')

@section('title', 'Konten Profil Perusahaan')
@section('page_title', 'Konten Profil Perusahaan')

@section('content')
<p class="text-sm text-gray-600 mb-4">
    Isi bagian-bagian halaman <a href="{{ route('profile') }}" target="_blank" class="text-blue-600 hover:underline">Profil Perusahaan</a> di situs jsmu.co.id.
    Menu HSSE dan Archipelago pada situs mengarah ke bagian di halaman yang sama.
</p>

<div class="bg-white rounded shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                <tr>
                    <th class="px-6 py-3">Bagian</th>
                    <th class="px-6 py-3">Letak di situs</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Gambar</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($contents as $content)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <div class="font-semibold text-gray-900">{{ $content->label() }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $content->key }}</div>
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $content->letak() }}</td>
                    <td class="px-6 py-3">
                        @if($content->sudahDiisi())
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Sudah diisi</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Masih kosong</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        @if($content->image)
                            <img src="{{ asset('storage/' . $content->image) }}" class="h-10 w-auto rounded">
                        @else
                            <span class="text-gray-400">&mdash;</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right">
                        <a href="{{ route('admin.cms-contents.edit', $content->key) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
