@extends('layouts.company_profile')

@section('title', 'Our Clients - JSMU Guard')

@section('content')
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-12 text-center">Our Clients</h1>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
            @foreach($clients as $client)
            <div class="flex items-center justify-center p-4 border rounded-lg hover:shadow-md transition h-32">
                @if($client->logo)
                    <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" class="max-h-full max-w-full grayscale hover:grayscale-0 transition duration-300">
                @else
                    <span class="font-bold text-gray-500 text-center">{{ $client->name }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
