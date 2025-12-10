@extends('layouts.company_profile')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Testimoni Klien</h1>
        <p class="text-xl opacity-90">Apa kata mereka tentang layanan kami</p>
    </div>
</section>

<!-- Testimonials Grid -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        @if($testimonials->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($testimonials as $testimonial)
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition duration-300 {{ $testimonial->is_featured ? 'ring-2 ring-yellow-400' : '' }}">
                @if($testimonial->is_featured)
                <div class="flex items-center gap-1 text-yellow-500 text-sm mb-3">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    Featured
                </div>
                @endif

                <!-- Rating -->
                <div class="flex items-center gap-1 text-yellow-400 mb-4">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $testimonial->rating)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @else
                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endif
                    @endfor
                </div>

                <!-- Content -->
                <blockquote class="text-gray-700 italic mb-6">
                    "{{ $testimonial->content }}"
                </blockquote>

                <!-- Author -->
                <div class="flex items-center gap-4">
                    @if($testimonial->client_photo)
                        <img src="{{ asset('storage/' . $testimonial->client_photo) }}" alt="{{ $testimonial->client_name }}" class="w-12 h-12 rounded-full object-cover">
                    @else
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-lg">{{ substr($testimonial->client_name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-900">{{ $testimonial->client_name }}</p>
                        @if($testimonial->client_position || $testimonial->client_company)
                        <p class="text-sm text-gray-500">
                            {{ $testimonial->client_position }}{{ $testimonial->client_position && $testimonial->client_company ? ', ' : '' }}{{ $testimonial->client_company }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p>Belum ada testimoni yang tersedia.</p>
        </div>
        @endif
    </div>
</section>
@endsection
