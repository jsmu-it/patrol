@extends('layouts.admin')

@section('title', 'Approval Cuti')
@section('page_title', 'Approval Cuti')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4 p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-center">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pending" {{ ($status ?? 'pending') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ ($status ?? '') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ ($status ?? '') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="cancelled" {{ ($status ?? '') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="all" {{ ($status ?? '') == 'all' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto text-xs">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left font-semibold">Tanggal Pengajuan</th>
                <th class="px-3 py-2 text-left font-semibold">Nama</th>
                <th class="px-3 py-2 text-left font-semibold">Tipe</th>
                <th class="px-3 py-2 text-left font-semibold">Periode</th>
                <th class="px-3 py-2 text-left font-semibold">Alasan</th>
                <th class="px-3 py-2 text-center font-semibold">Status</th>
                <th class="px-3 py-2 text-left font-semibold">Diproses Oleh</th>
                <th class="px-3 py-2 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($requests as $req)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">{{ $req->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2 font-medium">{{ $req->user?->name }}</td>
                    <td class="px-3 py-2">{{ $req->leaveType?->name ?? $req->type }}</td>
                    <td class="px-3 py-2">{{ $req->date_from?->format('Y-m-d') }} &mdash; {{ $req->date_to?->format('Y-m-d') }}</td>
                    <td class="px-3 py-2 max-w-xs truncate" title="{{ $req->reason }}">{{ $req->reason }}</td>
                    <td class="px-3 py-2 text-center">
                        @php
                            $statusClass = match($req->status) {
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-100 text-gray-600',
                                default => 'bg-yellow-100 text-yellow-700',
                            };
                            $statusLabel = match($req->status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                                default => 'Menunggu',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">
                        @if($req->approvedBy)
                            {{ $req->approvedBy->name }}
                            <div class="text-xs text-gray-400">{{ $req->approved_at?->format('d/m/Y H:i') }}</div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right space-x-2">
                        @if($req->status === 'pending')
                        <form action="{{ route('admin.approvals.leave.approve', $req) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white">Approve</button>
                        </form>
                        <form action="{{ route('admin.approvals.leave.reject', $req) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-red-600 hover:bg-red-700 text-white">Reject</button>
                        </form>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-3 py-4 text-center text-gray-500">Tidak ada pengajuan cuti.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-3 py-2 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <div>Menampilkan {{ $requests->firstItem() ?? 0 }}-{{ $requests->lastItem() ?? 0 }} dari {{ $requests->total() }} data</div>
            <div>{{ $requests->links() }}</div>
        </div>
    </div>
@endsection
