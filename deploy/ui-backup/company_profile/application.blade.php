@extends('layouts.company_profile')

@section('title', 'Download Application - JSMU Guard')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-900 to-blue-700 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-white mb-4">Download Application</h1>
        <p class="text-xl text-blue-100">Get our mobile application for better experience</p>
    </div>
</div>

<!-- Applications Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($applications->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($applications as $app)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            @if($app->icon)
                                <img src="{{ asset('storage/' . $app->icon) }}" alt="{{ $app->name }}" class="w-16 h-16 rounded-xl object-cover">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-blue-100 flex items-center justify-center">
                                    @if($app->platform === 'android')
                                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.523 15.34l1.552-2.685a.255.255 0 00-.094-.348l-.001-.001a.254.254 0 00-.348.094l-1.573 2.72a8.448 8.448 0 00-3.059-.573 8.449 8.449 0 00-3.059.573l-1.573-2.72a.254.254 0 00-.348-.094.255.255 0 00-.094.348l1.552 2.685A8.42 8.42 0 006 22.3h12a8.42 8.42 0 00-4.477-6.96zM9.5 19.8a.75.75 0 110-1.5.75.75 0 010 1.5zm5 0a.75.75 0 110-1.5.75.75 0 010 1.5zM5.998 5.993A1.997 1.997 0 004 7.99v7.01a1.997 1.997 0 001.998 1.998h.002a1.997 1.997 0 001.998-1.998V7.99a1.997 1.997 0 00-1.998-1.997zm12.004 0A1.997 1.997 0 0016 7.99v7.01a1.997 1.997 0 001.998 1.998h.002A1.997 1.997 0 0020 15V7.99a1.997 1.997 0 00-1.998-1.997z"/>
                                        </svg>
                                    @elseif($app->platform === 'ios')
                                        <svg class="w-8 h-8 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">{{ $app->name }}</h3>
                                @if($app->version)
                                    <span class="text-sm text-gray-500">Version {{ $app->version }}</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($app->description)
                            <p class="text-gray-600 mb-4">{{ $app->description }}</p>
                        @endif
                        
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($app->platform === 'android') bg-green-100 text-green-800
                                @elseif($app->platform === 'ios') bg-gray-100 text-gray-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ ucfirst($app->platform) }}
                            </span>
                            @if($app->file_size)
                                <span>{{ $app->file_size_formatted }}</span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                {{ number_format($app->download_count) }} downloads
                            </span>
                            <a href="{{ route('application.download', $app) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No Applications Available</h3>
                <p class="text-gray-500">Please check back later for our mobile applications.</p>
            </div>
        @endif
    </div>
</section>
@endsection
