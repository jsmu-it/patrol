@extends('layouts.admin')

@section('page_title', 'Audit Logs')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500">
                <tr>
                    <th class="px-6 py-4">Time</th>
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Action</th>
                    <th class="px-6 py-4">Description</th>
                    <th class="px-6 py-4 text-center">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $log->created_at->format('d M Y H:i:s') }}
                        <div class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</div>
                        <div class="text-xs text-gray-400">{{ $log->ip_address }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $badgeColor = match($log->event) {
                                'created' => 'bg-emerald-100 text-emerald-700',
                                'updated' => 'bg-blue-100 text-blue-700',
                                'deleted' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                            {{ ucfirst($log->event) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-800">
                        {{ $log->description }}
                        <div class="text-xs text-gray-400 mt-1">{{ $log->user_agent }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('admin.activity-logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        No activity logs found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
