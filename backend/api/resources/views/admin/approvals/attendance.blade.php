@extends('layouts.admin')

@section('title', 'Approval Absensi Dinas')
@section('page_title', 'Approval Absensi Dinas')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto text-xs">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left font-semibold">Tanggal</th>
                <th class="px-3 py-2 text-left font-semibold">Nama</th>
                <th class="px-3 py-2 text-left font-semibold">Project</th>
                <th class="px-3 py-2 text-left font-semibold">Shift</th>
                <th class="px-3 py-2 text-left font-semibold">Tipe</th>
                <th class="px-3 py-2 text-left font-semibold">Catatan</th>
                <th class="px-3 py-2 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">{{ $log->occurred_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2">{{ $log->user?->name }}</td>
                    <td class="px-3 py-2">{{ $log->project?->name }}</td>
                    <td class="px-3 py-2">{{ $log->shift?->name }}</td>
                    <td class="px-3 py-2">{{ $log->type }}</td>
                    <td class="px-3 py-2">{{ $log->note }}</td>
                    <td class="px-3 py-2 text-right space-x-2">
                        <form action="{{ route('admin.approvals.attendance.approve', $log) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-emerald-600 text-white">Approve</button>
                        </form>
                        <form action="{{ route('admin.approvals.attendance.reject', $log) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-red-600 text-white">Reject</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">Tidak ada pengajuan dinas yang perlu di-approve.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-3 py-2 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <div>Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data</div>
            <div>{{ $logs->links() }}</div>
        </div>
    </div>
@endsection
