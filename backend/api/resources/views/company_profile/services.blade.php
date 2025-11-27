@extends('layouts.company_profile')

@section('title', 'Our Services - JSMU Guard')

@section('content')
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Our Services</h1>
            <p class="text-lg text-gray-500">Comprehensive security solutions tailored to your specific needs.</p>
        </div>

        <div class="space-y-20">
            @foreach($services as $index => $service)
            <div id="{{ $service->slug }}" class="flex flex-col md:flex-row items-center {{ $index % 2 == 1 ? 'md:flex-row-reverse' : '' }} gap-10">
                <div class="md:w-1/2">
                    @if($service->image)
                        <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->title }}" class="rounded-xl shadow-2xl w-full object-cover h-80 transform hover:scale-105 transition duration-500">
                    @else
                        <div class="bg-gray-200 h-80 rounded-xl flex items-center justify-center text-gray-400 text-xl">No Image</div>
                    @endif
                </div>
                <div class="md:w-1/2">
                    <div class="flex items-center mb-4">
                         @if($service->icon)
                            <!-- Check if icon is image path or class (simplified assumption: if starts with fa-, class, else image) -->
                            <!-- For now assuming it's not implemented fully, just title -->
                         @endif
                         <h2 class="text-3xl font-bold text-gray-900">{{ $service->title }}</h2>
                    </div>
                    <div class="prose text-gray-600 mb-6">
                        {!! $service->full_description ?? $service->short_description !!}
                    </div>
                    <a href="{{ route('contact') }}" class="inline-flex items-center text-blue-600 font-bold hover:text-blue-800">
                        Inquire about this service <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
