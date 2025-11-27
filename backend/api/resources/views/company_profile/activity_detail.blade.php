@extends('layouts.company_profile')

@section('title', $activity->title . ' - JSMU Guard')

@section('content')
<div class="bg-white py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('activities') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Activities
        </a>
        
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $activity->title }}</h1>
        <div class="flex items-center text-gray-500 mb-8 text-sm">
             <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            {{ $activity->date ? $activity->date->format('F d, Y') : '' }}
            <span class="mx-2">•</span>
            <span class="uppercase font-bold text-blue-600">{{ $activity->type }}</span>
        </div>

        @if($activity->image)
            <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
                <img src="{{ asset('storage/' . $activity->image) }}" class="w-full object-cover max-h-[500px]">
            </div>
        @endif

        <div class="prose max-w-none text-gray-800 leading-relaxed">
            {!! $activity->content ?? $activity->short_description !!}
        </div>
    </div>
</div>
@endsection
