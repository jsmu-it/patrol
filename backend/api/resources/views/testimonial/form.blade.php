@extends('layouts.company_profile')

@section('content')
<div class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-8 text-center">
                <h1 class="text-2xl font-bold">Form Testimoni</h1>
                <p class="mt-2 opacity-90">Bagikan pengalaman Anda menggunakan layanan kami</p>
            </div>

            @if(session('success'))
            <div class="px-6 py-4 bg-green-50 border-b border-green-200">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('testimonial.submit', $testimonial->token) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="client_name" value="{{ old('client_name', $testimonial->client_name) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('client_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <input type="text" name="client_position" value="{{ old('client_position', $testimonial->client_position) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Manager">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan</label>
                    <input type="text" name="client_company" value="{{ old('client_company', $testimonial->client_company) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: PT Contoh Indonesia">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto (Opsional)</label>
                    <input type="file" name="client_photo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maksimal 2MB.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2" x-data="{ rating: {{ old('rating', $testimonial->rating ?? 5) }} }">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" @click="rating = {{ $i }}" class="text-3xl focus:outline-none transition-transform hover:scale-110" :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'">
                            &#9733;
                        </button>
                        @endfor
                        <input type="hidden" name="rating" x-model="rating">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Testimoni Anda <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="5" required minlength="20" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ceritakan pengalaman Anda menggunakan layanan kami...">{{ old('content', $testimonial->content) }}</textarea>
                    @error('content')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Minimal 20 karakter</p>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                        Kirim Testimoni
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
            Terima kasih atas kepercayaan Anda kepada layanan kami.
        </p>
    </div>
</div>
@endsection
