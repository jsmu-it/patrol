@extends('layouts.company_profile')

@section('title', 'Profile - JSMU Guard')

@section('content')
<div class="bg-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-8 text-center uppercase">Company Profile</h1>
        
        <div class="space-y-12">
            <!-- About Us -->
            <div id="about" class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col md:flex-row">
                <div class="md:w-1/3 bg-blue-900 p-8 text-white flex items-center justify-center">
                    <h2 class="text-3xl font-bold uppercase tracking-widest">About Us</h2>
                </div>
                <div class="md:w-2/3 p-8">
                    <div class="prose max-w-none text-gray-600">
                        {!! $about->body ?? 'Information about the company.' !!}
                    </div>
                     @if($about && $about->image)
                        <div class="mt-6">
                            <img src="{{ asset('storage/' . $about->image) }}" class="rounded-lg w-full object-cover h-64">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Visi Misi -->
            <div id="visi-misi" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow-lg p-8 border-t-4 border-blue-600">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded mr-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></span>
                        Vision
                    </h3>
                    <div class="prose text-gray-600">
                         {!! $visi->body ?? 'To be the leading security provider.' !!}
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-8 border-t-4 border-gray-800">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-gray-100 text-gray-800 p-2 rounded mr-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></span>
                        Mission
                    </h3>
                    <div class="prose text-gray-600">
                        {!! $misi->body ?? 'Providing excellent service.' !!}
                    </div>
                </div>
            </div>

            <!-- HSSE -->
            <div id="hsse" class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">Health, Safety, Security & Environment (HSSE)</h2>
                <div class="flex flex-col md:flex-row gap-8">
                    @if($hsse && $hsse->image)
                    <div class="md:w-1/3">
                        <img src="{{ asset('storage/' . $hsse->image) }}" class="rounded-lg w-full shadow-md">
                    </div>
                    @endif
                    <div class="md:w-2/3 prose max-w-none text-gray-600">
                         {!! $hsse->body ?? 'HSSE Policy Content.' !!}
                    </div>
                </div>
            </div>

            <!-- Archipelago -->
            <div id="archipelago" class="bg-blue-50 rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-blue-900 mb-6 text-center">Archipelago Coverage</h2>
                <div class="text-center">
                    <div class="prose max-w-none mx-auto text-gray-700 mb-8">
                         {!! $archipelago->body ?? 'Our operations cover the entire archipelago.' !!}
                    </div>
                    @if($archipelago && $archipelago->image)
                        <img src="{{ asset('storage/' . $archipelago->image) }}" class="rounded-lg shadow-xl mx-auto max-h-96">
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
