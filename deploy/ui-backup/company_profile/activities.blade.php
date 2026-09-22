@extends('layouts.company_profile')

@section('title', 'Activities - JSMU Guard')

@section('content')
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-12 text-center">Activities & Events</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content List -->
            <div class="lg:col-span-2 space-y-8">
                @foreach($activities as $activity)
                <div class="flex flex-col md:flex-row bg-white border rounded-lg overflow-hidden hover:shadow-md transition">
                    <div class="md:w-1/3 bg-gray-100 h-48 md:h-auto">
                        @if($activity->image)
                            <img src="{{ asset('storage/' . $activity->image) }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="p-6 md:w-2/3 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                {{ $activity->date ? $activity->date->format('F d, Y') : 'N/A' }}
                                <span class="mx-2">•</span>
                                <span class="uppercase text-xs font-bold text-blue-600">{{ $activity->type }}</span>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-3">
                                <a href="{{ route('activities.show', $activity) }}" class="hover:text-blue-800">{{ $activity->title }}</a>
                            </h2>
                            <p class="text-gray-600 line-clamp-2">{!! strip_tags($activity->short_description) !!}</p>
                        </div>
                        <a href="{{ route('activities.show', $activity) }}" class="mt-4 text-blue-600 font-medium hover:underline">Read More</a>
                    </div>
                </div>
                @endforeach
                
                @if($activities->isEmpty())
                    <p class="text-center text-gray-500">No activities found.</p>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-gray-50 p-6 rounded-lg sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Categories</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><a href="{{ route('activities') }}" class="hover:text-blue-600 block py-1">All Activities</a></li>
                        <li><a href="{{ route('activities', ['type' => 'internal']) }}" class="hover:text-blue-600 block py-1">Internal Events</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
