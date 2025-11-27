@extends('layouts.admin')

@section('title', 'Edit Lokasi Patroli')
@section('page_title', 'Edit Lokasi Patroli')

@section('content')
    <form method="POST" action="{{ route('admin.patrol.checkpoints.update', $checkpoint) }}" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 space-y-4 text-xs max-w-2xl">
        @csrf
        @method('PUT')
        @include('admin.patrol.checkpoints.form')
        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('admin.patrol.checkpoints.index') }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Batal</a>
            <button type="submit" class="px-3 py-1.5 rounded bg-slate-900 text-white">Simpan</button>
        </div>
    </form>
@endsection
