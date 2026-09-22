@extends('layouts.company_profile')

@section('title', 'Our Services - Jaya Sakti Mandiri Unggul')

@section('content')
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Our Services</h1>
            <p class="text-lg text-gray-500">Comprehensive security solutions tailored to your specific needs.</p>
        </div>

        <div class="space-y-20">
            @foreach($services as $index => $service)
            @php($punyaIsi = $service->image || $service->full_description || $service->short_description)

            @if($punyaIsi)
                <div id="{{ $service->slug }}" class="flex flex-col md:flex-row items-center {{ $index % 2 == 1 ? 'md:flex-row-reverse' : '' }} gap-10">
                    <div class="md:w-1/2">
                        @if($service->image)
                            <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->title }}" class="rounded-xl shadow-2xl w-full object-cover h-80 transform hover:scale-105 transition duration-500">
                        @else
                            <div class="bg-gray-100 h-80 rounded-xl flex items-center justify-center text-gray-400">Belum ada gambar</div>
                        @endif
                    </div>
                    <div class="md:w-1/2">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $service->title }}</h2>
                        <div class="prose text-gray-600 mb-6">
                            {!! $service->full_description ?? $service->short_description !!}
                        </div>
                        <a href="{{ route('contact') }}" class="inline-flex items-center text-blue-600 font-bold hover:text-blue-800">
                            Inquire about this service <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </div>
            @else
                {{-- Menu yang isinya belum ditulis tetap punya penanda, supaya
                     tautan dari menu atas tidak mendarat di tempat yang salah. --}}
                <div id="{{ $service->slug }}">
                    <h2 class="text-3xl font-bold text-gray-900 border-b pb-4">{{ $service->title }}</h2>
                    @if($service->children->isEmpty())
                        <p class="text-gray-400 mt-4">Keterangan layanan ini akan segera ditambahkan.</p>
                    @endif
                </div>
            @endif

            {{-- Layanan yang bernaung di bawah menu ini --}}
            @foreach($service->children as $anak)
                <div id="{{ $anak->slug }}" class="md:pl-16 border-l-4 border-blue-100">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        @if($anak->image)
                            <img src="{{ asset('storage/' . $anak->image) }}" alt="{{ $anak->title }}"
                                 class="md:w-1/3 rounded-lg shadow-lg object-cover h-56 w-full">
                        @endif
                        <div class="{{ $anak->image ? 'md:w-2/3' : 'w-full' }}">
                            <p class="text-xs uppercase tracking-wider text-blue-600 font-semibold mb-1">{{ $service->title }}</p>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $anak->title }}</h3>
                            <div class="prose text-gray-600">
                                {!! $anak->full_description ?? $anak->short_description !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @endforeach
        </div>
    </div>
</div>
@endsection
