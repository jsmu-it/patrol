@extends('layouts.admin')

@section('title', 'Approval Cuti')
@section('page_title', 'Approval Cuti')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto text-xs">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left font-semibold">Tanggal Pengajuan</th>
                <th class="px-3 py-2 text-left font-semibold">Nama</th>
                <th class="px-3 py-2 text-left font-semibold">Periode</th>
                <th class="px-3 py-2 text-left font-semibold">Tipe</th>
                <th class="px-3 py-2 text-left font-semibold">Alasan</th>
                <th class="px-3 py-2 text-left font-semibold">Surat Dokter</th>
                <th class="px-3 py-2 text-right font-semibold">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($requests as $req)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">{{ $req->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2">{{ $req->user?->name }}</td>
                    <td class="px-3 py-2">{{ $req->date_from?->format('Y-m-d') }} &mdash; {{ $req->date_to?->format('Y-m-d') }}</td>
                    <td class="px-3 py-2">{{ $req->type }}</td>
                    <td class="px-3 py-2">{{ $req->reason }}</td>
                    <td class="px-3 py-2">{{ $req->doctor_note }}</td>
                    <td class="px-3 py-2 text-right space-x-2">
                        <form action="{{ route('admin.approvals.leave.approve', $req) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-emerald-600 text-white">Approve</button>
                        </form>
                        <form action="{{ route('admin.approvals.leave.reject', $req) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded bg-red-600 text-white">Reject</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">Tidak ada pengajuan cuti yang perlu di-approve.</td>
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
