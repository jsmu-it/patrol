@extends('layouts.admin')

@section('title', 'Approval Absensi Dinas')
@section('page_title', 'Approval Absensi Dinas')

@section('content')
    {{-- Penyaring project --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4 p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select name="project_id" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ (int) $projectId === $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($projectId)
                <a href="{{ route('admin.approvals.attendance') }}" class="text-sm text-gray-500 underline pb-2">tampilkan semua</a>
            @endif
            <div class="ml-auto text-sm text-gray-500 pb-2">
                Daftar dikelompokkan per project, terbaru di atas.
            </div>
        </form>
    </div>

    {{-- Formulir tindakan massal; centangnya ada di tabel dan disalin saat dikirim --}}
    <form id="form-bulk" action="{{ route('admin.approvals.attendance.bulk') }}" method="POST">@csrf</form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto text-xs">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="px-3 py-2 text-left">
                    <input type="checkbox" id="centang-semua" class="h-4 w-4 rounded border-gray-300" title="Pilih semua di halaman ini">
                </th>
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
            @php($projectSebelumnya = null)
            @forelse($logs as $log)
                @if($log->project?->name !== $projectSebelumnya)
                    @php($projectSebelumnya = $log->project?->name)
                    <tr class="bg-gray-50">
                        <td colspan="8" class="px-3 py-1.5 font-semibold text-gray-600">{{ $projectSebelumnya ?? 'Tanpa project' }}</td>
                    </tr>
                @endif
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                        <input type="checkbox" class="centang-baris h-4 w-4 rounded border-gray-300" value="{{ $log->id }}">
                    </td>
                    <td class="px-3 py-2">{{ $log->occurred_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2">{{ $log->user?->name }}</td>
                    <td class="px-3 py-2">{{ $log->project?->name }}</td>
                    <td class="px-3 py-2">{{ $log->shift?->name }}</td>
                    <td class="px-3 py-2">{{ $log->type }}</td>
                    <td class="px-3 py-2">{{ $log->note }}</td>
                    <td class="px-3 py-2 text-right space-x-2 whitespace-nowrap">
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
                    <td colspan="8" class="px-3 py-4 text-center text-gray-500">Tidak ada pengajuan dinas yang perlu di-approve.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-3 py-2 border-t border-gray-100 text-xs text-gray-500 flex justify-between items-center">
            <div>Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data</div>
            <div>{{ $logs->links() }}</div>
        </div>
    </div>

    @include('admin.approvals._bulk', ['label' => 'absensi dinas'])
@endsection
