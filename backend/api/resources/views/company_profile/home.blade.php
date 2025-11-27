@extends('layouts.company_profile')

@section('content')

<!-- Hero Slider Section -->
<div x-data="{ activeSlide: 0, slides: {{ $heroSlides->count() }}, interval: null }" 
     x-init="interval = setInterval(() => { activeSlide = (activeSlide + 1) % slides }, 5000)"
     class="relative bg-gray-900 h-[600px] overflow-hidden group">
    
    @if($heroSlides->count() > 0)
        <!-- Slides -->
        <div class="relative w-full h-full">
            @foreach($heroSlides as $index => $slide)
            <div x-show="activeSlide === {{ $index }}"
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 transform scale-105"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-700"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-105"
                 class="absolute inset-0 w-full h-full flex items-center justify-center">
                
                <!-- Background Image -->
                <div class="absolute inset-0 overflow-hidden">
                    <img src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->title }}" class="w-full h-full object-cover opacity-50">
                    <div class="absolute inset-0 bg-gradient-to-b from-gray-900/30 via-transparent to-gray-900/70"></div>
                </div>

                <!-- Content -->
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight mb-6 drop-shadow-lg" 
                        x-show="activeSlide === {{ $index }}"
                        x-transition:enter="transition ease-out duration-1000 delay-300"
                        x-transition:enter-start="opacity-0 translate-y-10"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        {{ $slide->title }}
                    </h1>
                    <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-200 drop-shadow-md"
                       x-show="activeSlide === {{ $index }}"
                       x-transition:enter="transition ease-out duration-1000 delay-500"
                       x-transition:enter-start="opacity-0 translate-y-10"
                       x-transition:enter-end="opacity-100 translate-y-0">
                        {{ $slide->subtitle }}
                    </p>
                    <div class="mt-10"
                         x-show="activeSlide === {{ $index }}"
                         x-transition:enter="transition ease-out duration-1000 delay-700"
                         x-transition:enter-start="opacity-0 translate-y-10"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="{{ route('contact') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full transition duration-300 transform hover:scale-105 shadow-xl">
                            Get a Quote
                        </a>
                        <a href="{{ route('services') }}" class="ml-4 inline-block bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full hover:bg-white hover:text-gray-900 transition duration-300">
                            Our Services
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Navigation Arrows -->
        <button @click="activeSlide = (activeSlide === 0) ? slides - 1 : activeSlide - 1; clearInterval(interval); interval = setInterval(() => { activeSlide = (activeSlide + 1) % slides }, 5000)" 
                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white p-3 rounded-full opacity-0 group-hover:opacity-100 transition duration-300 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <button @click="activeSlide = (activeSlide + 1) % slides; clearInterval(interval); interval = setInterval(() => { activeSlide = (activeSlide + 1) % slides }, 5000)" 
                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white p-3 rounded-full opacity-0 group-hover:opacity-100 transition duration-300 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>

        <!-- Indicators -->
        <div class="absolute bottom-8 left-0 right-0 flex justify-center space-x-3">
            @foreach($heroSlides as $index => $slide)
            <button @click="activeSlide = {{ $index }}; clearInterval(interval); interval = setInterval(() => { activeSlide = (activeSlide + 1) % slides }, 5000)"
                    class="w-3 h-3 rounded-full transition-all duration-300"
                    :class="activeSlide === {{ $index }} ? 'bg-blue-600 w-8' : 'bg-white/50 hover:bg-white'">
            </button>
            @endforeach
        </div>
    @else
        <!-- Fallback if no slides -->
        <div class="absolute inset-0 bg-gray-900 flex items-center justify-center text-center">
            <div class="max-w-4xl px-4">
                <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight mb-6">
                    Professional Security Services
                </h1>
                <p class="text-xl text-gray-300">
                    Securing your assets with integrity, professionalism, and modern technology.
                </p>
            </div>
        </div>
    @endif
</div>

<!-- Highlights / Intro -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 uppercase">Why Choose Us</h2>
            <div class="w-20 h-1 bg-blue-600 mx-auto mt-4"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 bg-gray-50 rounded-lg text-center hover:shadow-lg transition">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Certified Guards</h3>
                <p class="text-gray-600">Our personnel are rigorously trained and certified to meet the highest industry standards.</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg text-center hover:shadow-lg transition">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Smart Technology</h3>
                <p class="text-gray-600">We integrate modern technology for patrol monitoring, attendance, and reporting.</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg text-center hover:shadow-lg transition">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Rapid Response</h3>
                <p class="text-gray-600">24/7 support and rapid response teams to handle any security incidents.</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="py-16 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 uppercase">Our Services</h2>
                <div class="w-20 h-1 bg-blue-600 mt-4"></div>
            </div>
            <a href="{{ route('services') }}" class="text-blue-600 font-semibold hover:text-blue-800 flex items-center">
                View All Services <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($services as $service)
            <div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gray-200 relative">
                     @if($service->image)
                        <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="flex items-center justify-center h-full text-gray-400">No Image</div>
                    @endif
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $service->title }}</h3>
                    <p class="text-gray-600 mb-4 line-clamp-3">{{ $service->short_description }}</p>
                    <a href="{{ route('services') }}#{{ $service->slug }}" class="text-blue-600 hover:text-blue-800 font-medium">Read More &rarr;</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Activities/News -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 uppercase">Latest Activity</h2>
            <div class="w-20 h-1 bg-blue-600 mx-auto mt-4"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($activities as $activity)
            <div class="border border-gray-100 rounded-lg p-6 hover:border-blue-200 transition">
                <p class="text-sm text-gray-400 mb-2">{{ $activity->date ? $activity->date->format('d M Y') : '' }}</p>
                <h3 class="text-lg font-bold text-gray-900 mb-3"><a href="{{ route('activities.show', $activity) }}" class="hover:text-blue-600">{{ $activity->title }}</a></h3>
                <p class="text-gray-600 text-sm line-clamp-3">{{ $activity->short_description }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Clients -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl font-bold text-gray-900 uppercase text-gray-500">Trusted By</h2>
        </div>
        <div class="flex flex-wrap justify-center gap-8 items-center opacity-70 grayscale hover:grayscale-0 transition duration-500">
             @foreach($clients as $client)
                <div class="w-32 h-20 flex items-center justify-center">
                    @if($client->logo)
                        <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" class="max-h-full max-w-full">
                    @else
                        <span class="font-bold text-gray-400">{{ $client->name }}</span>
                    @endif
                </div>
             @endforeach
        </div>
    </div>
</section>

@endsection
