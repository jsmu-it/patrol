@extends('layouts.admin')

@section('title', 'Edit Shift')
@section('page_title', 'Edit Shift - ' . $shift->name)

@section('content')
    <div class="max-w-xl">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <form action="{{ route('admin.shifts.update', $shift->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Shift</label>
                    <input type="text" name="name" value="{{ old('name', $shift->name) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-slate-900" required>
                    @error('name') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jam Masuk</label>
                        <input type="time" step="1" name="start_time" value="{{ old('start_time', $shift->start_time) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-slate-900" required>
                        @error('start_time') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jam Keluar</label>
                        <input type="time" step="1" name="end_time" value="{{ old('end_time', $shift->end_time) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-slate-900" required>
                        @error('end_time') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 text-xs hover:bg-gray-50">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded text-xs hover:bg-slate-800">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
