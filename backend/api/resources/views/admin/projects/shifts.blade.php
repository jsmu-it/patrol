@extends('layouts.admin')

@section('title', 'Shift Project')
@section('page_title', 'Shift Project - '.$project->name)

@section('content')
    <form method="POST" action="{{ route('admin.projects.shifts.update', $project) }}" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 space-y-4 text-xs max-w-xl">
        @csrf
        <div class="flex justify-between items-start mb-2">
             <p class="text-gray-600">Pilih shift yang berlaku untuk project ini.</p>
             <a href="{{ route('admin.shifts.index') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">Kelola Data Shift</a>
        </div>
        <div class="space-y-2">
            @foreach($shifts as $shift)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="shift_ids[]" value="{{ $shift->id }}" @checked(in_array($shift->id, old('shift_ids', $activeShiftIds)))>
                    <span>{{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})</span>
                </label>
            @endforeach
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('admin.projects.index') }}" class="px-3 py-1.5 rounded border border-gray-300 text-gray-700">Batal</a>
            <button type="submit" class="px-3 py-1.5 rounded bg-slate-900 text-white">Simpan</button>
        </div>
    </form>
@endsection
