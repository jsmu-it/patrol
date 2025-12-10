@extends('layouts.company_profile')

@section('content')
<div class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center py-12 px-6">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-4">Terima Kasih!</h1>
            <p class="text-gray-600 mb-6">Testimoni Anda telah berhasil dikirim dan sedang menunggu persetujuan dari tim kami.</p>

            <div class="bg-gray-50 rounded-lg p-6 text-left">
                <h3 class="font-semibold text-gray-900 mb-2">Testimoni yang Anda kirim:</h3>
                <div class="flex items-center gap-1 text-yellow-400 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $testimonial->rating)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @else
                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endif
                    @endfor
                </div>
                <p class="text-gray-700 italic">"{{ $testimonial->content }}"</p>
                <p class="text-gray-500 text-sm mt-3">— {{ $testimonial->client_name }}, {{ $testimonial->client_position }} @ {{ $testimonial->client_company }}</p>
            </div>

            <a href="{{ route('home') }}" class="inline-block mt-8 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
