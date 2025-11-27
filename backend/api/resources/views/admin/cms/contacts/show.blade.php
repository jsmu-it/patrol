@extends('layouts.admin')
@section('page_title', 'Detail Pesan')
@section('content')
<div class="max-w-4xl bg-white rounded shadow-sm p-6">
    <a href="{{ route('admin.cms-contacts.index') }}" class="text-blue-600 mb-4 inline-block">&larr; Kembali</a>
    <div class="mb-4 border-b pb-4">
        <h3 class="text-xl font-bold">{{ $contactMessage->subject }}</h3>
        <div class="text-gray-500 text-sm mt-1">Dari: {{ $contactMessage->name }} ({{ $contactMessage->email }})</div>
        <div class="text-gray-400 text-xs mt-1">{{ $contactMessage->created_at->format('d M Y H:i') }}</div>
    </div>
    <div class="prose max-w-none bg-gray-50 p-4 rounded">
        {!! nl2br(e($contactMessage->message)) !!}
    </div>
</div>
@endsection
