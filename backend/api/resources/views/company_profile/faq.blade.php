@extends('layouts.company_profile')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Frequently Asked Questions</h1>
        <p class="text-xl opacity-90">Temukan jawaban untuk pertanyaan yang sering diajukan</p>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4">
        @if($categories->isNotEmpty())
        <!-- Category Filter -->
        <div class="flex flex-wrap justify-center gap-2 mb-8" x-data="{ activeCategory: 'all' }">
            <button @click="activeCategory = 'all'" 
                    :class="activeCategory === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition">
                Semua
            </button>
            @foreach($categories as $category)
            <button @click="activeCategory = '{{ Str::slug($category) }}'" 
                    :class="activeCategory === '{{ Str::slug($category) }}' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition">
                {{ $category }}
            </button>
            @endforeach
        </div>
        @endif

        <!-- FAQ Accordion -->
        <div class="space-y-4" x-data="{ openFaq: null }">
            @foreach($faqs as $faq)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                 x-show="activeCategory === 'all' || activeCategory === '{{ Str::slug($faq->category) }}'"
                 x-transition>
                <button @click="openFaq = openFaq === {{ $faq->id }} ? null : {{ $faq->id }}"
                        class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="font-medium text-gray-900 pr-4">{{ $faq->question }}</span>
                    <svg class="w-5 h-5 text-gray-500 flex-shrink-0 transition-transform duration-200"
                         :class="openFaq === {{ $faq->id }} ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="openFaq === {{ $faq->id }}" x-collapse>
                    <div class="px-6 pb-4 text-gray-600 border-t border-gray-100 pt-4">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($faqs->isEmpty())
        <div class="text-center py-12 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p>Belum ada FAQ yang tersedia.</p>
        </div>
        @endif
    </div>
</section>

<!-- Contact CTA -->
<section class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Masih ada pertanyaan?</h2>
        <p class="text-gray-600 mb-6">Tim kami siap membantu menjawab pertanyaan Anda</p>
        <a href="{{ route('contact') }}" class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
            Hubungi Kami
        </a>
    </div>
</section>
@endsection
