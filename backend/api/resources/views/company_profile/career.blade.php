@extends('layouts.company_profile')

@section('title', 'Career - Jaya Sakti Mandiri Unggul')

@section('content')
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Join Our Team</h1>
            <p class="text-lg text-gray-500">Build your career with PT. JAYA SAKTI MANDIRI UNGGUL. We are looking for dedicated professionals.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($careers as $career)
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4">
                    <h2 class="text-lg font-bold text-white line-clamp-2">{{ $career->title }}</h2>
                </div>
                
                <!-- Body -->
                <div class="p-4 flex-1 flex flex-col">
                    <!-- Location & Type Tags -->
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ $career->location }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            {{ $career->type }}
                        </span>
                    </div>
                    
                    <!-- Truncated Description -->
                    <div class="text-sm text-gray-600 mb-3 line-clamp-3" id="desc-short-{{ $career->id }}">
                        {!! Str::limit(strip_tags($career->description), 120) !!}
                    </div>
                    
                    <!-- Expandable Full Description -->
                    <div class="hidden text-sm text-gray-600 mb-3" id="desc-full-{{ $career->id }}">
                        <div class="prose prose-sm max-w-none mb-3">
                            <strong class="text-gray-800">Description:</strong><br>
                            {!! $career->description !!}
                        </div>
                        @if($career->requirements)
                        <div class="prose prose-sm max-w-none">
                            <strong class="text-gray-800">Requirements:</strong><br>
                            {!! $career->requirements !!}
                        </div>
                        @endif
                    </div>
                    
                    <!-- Read More Toggle -->
                    <button onclick="toggleDetails({{ $career->id }})" id="toggle-btn-{{ $career->id }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium mb-4 text-left focus:outline-none">
                        Read More ▼
                    </button>
                    
                    <!-- Apply Button -->
                    <div class="mt-auto">
                        <a href="{{ route('career.apply-form', $career->id) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg transition">
                            Apply Now
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($careers->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <div class="text-6xl mb-4">💼</div>
                <p class="text-lg">No open positions at the moment. Please check back later.</p>
            </div>
        @endif
    </div>
</div>

<script>
function toggleDetails(id) {
    const shortDesc = document.getElementById('desc-short-' + id);
    const fullDesc = document.getElementById('desc-full-' + id);
    const btn = document.getElementById('toggle-btn-' + id);
    
    if (fullDesc.classList.contains('hidden')) {
        shortDesc.classList.add('hidden');
        fullDesc.classList.remove('hidden');
        btn.textContent = 'Show Less ▲';
    } else {
        shortDesc.classList.remove('hidden');
        fullDesc.classList.add('hidden');
        btn.textContent = 'Read More ▼';
    }
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection

