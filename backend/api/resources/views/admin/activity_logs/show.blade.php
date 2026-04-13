@extends('layouts.admin')

@section('page_title', 'Log Detail')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('admin.activity-logs.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Logs
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Activity #{{ $activityLog->id }}</h2>
                <p class="text-sm text-gray-500">{{ $activityLog->created_at->format('d F Y, H:i:s') }}</p>
            </div>
            @php
                $badgeColor = match($activityLog->event) {
                    'created' => 'bg-emerald-100 text-emerald-700',
                    'updated' => 'bg-blue-100 text-blue-700',
                    'deleted' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700'
                };
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badgeColor }}">
                {{ ucfirst($activityLog->event) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Actor</h3>
                <div class="font-medium text-gray-900">{{ $activityLog->user->name ?? 'System' }}</div>
                <div class="text-sm text-gray-500">{{ $activityLog->user->email ?? '-' }}</div>
                <div class="text-xs text-gray-400 mt-2">{{ $activityLog->ip_address }}</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Subject</h3>
                <div class="font-medium text-gray-900">{{ $activityLog->subject_type }}</div>
                <div class="text-sm text-gray-500">ID: {{ $activityLog->subject_id }}</div>
                <div class="text-sm text-gray-600 mt-2">{{ $activityLog->description }}</div>
            </div>
        </div>

        @if($activityLog->properties)
        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Changeset</h3>
            
            @if(isset($activityLog->properties['old']) && isset($activityLog->properties['new']))
                {{-- Update Event --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3">Field</th>
                                <th class="px-4 py-3 text-red-600">Old Value</th>
                                <th class="px-4 py-3 text-emerald-600">New Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($activityLog->properties['new'] as $key => $newValue)
                                {{-- Only show changed fields --}}
                                @if(array_key_exists($key, $activityLog->properties['old']) && $activityLog->properties['old'][$key] != $newValue)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-700">{{ $key }}</td>
                                    <td class="px-4 py-3 text-red-600 bg-red-50/50 break-all">{{ is_array($activityLog->properties['old'][$key]) ? json_encode($activityLog->properties['old'][$key]) : $activityLog->properties['old'][$key] }}</td>
                                    <td class="px-4 py-3 text-emerald-600 bg-emerald-50/50 break-all">{{ is_array($newValue) ? json_encode($newValue) : $newValue }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Create/Delete Event --}}
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-xs text-emerald-400 font-mono">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
