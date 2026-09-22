@extends('layouts.admin')

@section('title', 'Tambah Karyawan')
@section('page_title', 'Tambah Karyawan')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 space-y-4 text-xs">
        @csrf
        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-red-800">
                <div class="font-semibold mb-1">Data belum tersimpan:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $galat)<li>{{ $galat }}</li>@endforeach
                </ul>
            </div>
        @endif
        @include('admin.users.form')
        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Batal</a>
            <button type="submit" class="px-3 py-1.5 rounded bg-slate-900 text-white">Simpan</button>
        </div>
    </form>
@endsection
