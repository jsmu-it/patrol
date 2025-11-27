@extends('layouts.admin')
@section('page_title', 'Pesan Masuk (Contact Us)')
@section('content')
<div class="bg-white rounded shadow-sm">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 border-b"><tr><th class="px-6 py-3">Nama</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Subjek</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Aksi</th></tr></thead>
        <tbody class="divide-y">
            @foreach($messages as $msg)
            <tr class="hover:bg-gray-50 {{ $msg->is_read ? '' : 'font-semibold bg-blue-50' }}">
                <td class="px-6 py-3">{{ $msg->name }}</td>
                <td class="px-6 py-3">{{ $msg->email }}</td>
                <td class="px-6 py-3">{{ $msg->subject }}</td>
                <td class="px-6 py-3">{{ $msg->is_read ? 'Dibaca' : 'Baru' }}</td>
                <td class="px-6 py-3 flex gap-3">
                    <a href="{{ route('admin.cms-contacts.show', $msg) }}" class="text-blue-600">Lihat</a>
                    <form action="{{ route('admin.cms-contacts.destroy', $msg) }}" method="POST" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button class="text-red-600">Hapus</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $messages->links() }}</div>
</div>
@endsection
