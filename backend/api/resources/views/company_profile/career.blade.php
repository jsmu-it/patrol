@extends('layouts.company_profile')

@section('title', 'Career - JSMU Guard')

@section('content')
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Join Our Team</h1>
            <p class="text-lg text-gray-500">Build your career with JSMU Guard. We are looking for dedicated professionals.</p>
        </div>

        <div class="space-y-6">
            @foreach($careers as $career)
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-600 hover:shadow-lg transition">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $career->title }}</h2>
                        <div class="flex items-center text-sm text-gray-500 mt-2 space-x-4">
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> {{ $career->location }}</span>
                            <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ $career->type }}</span>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="mailto:hr@jsmuguard.com?subject=Application for {{ $career->title }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition">Apply Now</a>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-2">Description:</h3>
                    <p class="text-gray-600 mb-4">{{ $career->description }}</p>
                    @if($career->requirements)
                    <h3 class="font-semibold text-gray-800 mb-2">Requirements:</h3>
                    <p class="text-gray-600">{{ $career->requirements }}</p>
                    @endif
                </div>
            </div>
            @endforeach

            @if($careers->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <p>No open positions at the moment. Please check back later.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
