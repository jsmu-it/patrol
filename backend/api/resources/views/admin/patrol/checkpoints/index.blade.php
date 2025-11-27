@extends('layouts.admin')

@section('title', 'Lokasi Patroli')
@section('page_title', 'Lokasi Patroli')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-600">Daftar titik patroli berdasarkan project.</div>
        <div class="flex gap-2">
            <a href="{{ route('admin.patrol.checkpoints.printAll', request()->only('project_id')) }}" target="_blank" class="px-3 py-2 rounded-md bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-500">Print Semua</a>
            <a href="{{ route('admin.patrol.checkpoints.create') }}" class="px-3 py-2 rounded-md bg-slate-900 text-white text-xs font-medium hover:bg-slate-800">Tambah Lokasi</a>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end text-xs">
        <div>
            <label class="block text-gray-600 mb-1">Project</label>
            <select name="project_id" class="border border-gray-300 rounded px-2 py-1 text-xs">
                <option value="">Semua</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected((string)request('project_id') === (string)$project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-3 py-1.5 rounded bg-gray-800 text-white">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto text-xs">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left font-semibold">Project</th>
                <th class="px-3 py-2 text-left font-semibold">Nama Titik</th>
                <th class="px-3 py-2 text-left font-semibold">Pos</th>
                <th class="px-3 py-2 text-left font-semibold">Kode</th>
                <th class="px-3 py-2 text-left font-semibold">Koordinat</th>
                <th class="px-3 py-2 text-left font-semibold">Barcode</th>
                <th class="px-3 py-2 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($checkpoints as $checkpoint)
                <tr class="hover:bg-gray-50 align-top">
                    <td class="px-3 py-2">{{ $checkpoint->project?->name }}</td>
                    <td class="px-3 py-2">{{ $checkpoint->title }}</td>
                    <td class="px-3 py-2">{{ $checkpoint->post_name }}</td>
                    <td class="px-3 py-2 font-mono text-[11px]">{{ $checkpoint->code }}</td>
                    <td class="px-3 py-2">
                        @if($checkpoint->latitude && $checkpoint->longitude)
                            {{ $checkpoint->latitude }}, {{ $checkpoint->longitude }}
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-center">
                        @php
                            $payload = 'satpamapp://checkpoint?code=' . urlencode($checkpoint->code);
                        @endphp
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->generate($payload) !!}
                        <p class="mt-1 text-[10px] text-gray-500">QR untuk aplikasi Satpam Mobile.</p>
                    </td>
                    <td class="px-3 py-2 text-right space-x-2">
                        <a href="{{ route('admin.patrol.checkpoints.edit', $checkpoint) }}" class="text-xs text-slate-700 hover:underline">Edit</a>
                        <a href="{{ route('admin.patrol.checkpoints.print', $checkpoint) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Print</a>
                        <form action="{{ route('admin.patrol.checkpoints.destroy', $checkpoint) }}" method="POST" class="inline" onsubmit="return confirm('Hapus lokasi patroli ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">Belum ada lokasi patroli.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-3 py-2 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <div>Menampilkan {{ $checkpoints->firstItem() ?? 0 }}-{{ $checkpoints->lastItem() ?? 0 }} dari {{ $checkpoints->total() }} data</div>
            <div>{{ $checkpoints->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
