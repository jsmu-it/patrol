@extends('layouts.company_profile')

@section('title', 'Achievements - JSMU Guard')

@section('content')
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-12 text-center">Our Achievements</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($achievements as $achievement)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-56 bg-gray-200 relative">
                    @if($achievement->image)
                        <img src="{{ asset('storage/' . $achievement->image) }}" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="p-6">
                    @if($achievement->year)
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mb-2">{{ $achievement->year }}</span>
                    @endif
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $achievement->title }}</h3>
                    <p class="text-gray-600">{{ $achievement->description }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
