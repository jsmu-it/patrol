@extends('layouts.admin')
@section('page_title', 'Edit Klien')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <form action="{{ route('admin.cms-clients.update', $client) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="mb-4"><label class="block mb-1">Nama Klien</label><input type="text" name="name" value="{{ $client->name }}" class="w-full border rounded p-2" required></div>
        <div class="mb-4"><label class="block mb-1">Website</label><input type="url" name="website" value="{{ $client->website }}" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Order</label><input type="number" name="order" value="{{ $client->order }}" class="w-full border rounded p-2"></div>
        <div class="mb-4"><label class="block mb-1">Logo</label>@if($client->logo)<img src="{{ asset('storage/'.$client->logo) }}" class="h-10 mb-2">@endif<input type="file" name="logo" class="w-full"></div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</div>
@endsection
