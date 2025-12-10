@extends('layouts.admin')

@section('page_title', 'Pengaturan Website')

@section('content')
    @if(isset($tableExists) && !$tableExists)
    <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded">
        <strong>Perhatian:</strong> Tabel settings belum dibuat. Jalankan SQL berikut di phpMyAdmin:
        <pre class="mt-2 p-2 bg-yellow-50 text-xs overflow-x-auto">CREATE TABLE settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(255) NOT NULL UNIQUE,
  value TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);</pre>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-6 border-b pb-4">
                <h3 class="text-lg font-semibold mb-4">Identitas & Logo</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo Website</label>
                    @if($logo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $logo) }}" alt="Current Logo" class="h-16 object-contain border rounded p-2">
                        </div>
                    @endif
                    <input type="file" name="logo" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    <p class="text-xs text-gray-500 mt-1">Format: PNG, JPG, SVG. Maks: 2MB. Logo lama akan ditimpa.</p>
                </div>
            </div>

            <div class="mb-6 border-b pb-4">
                <h3 class="text-lg font-semibold mb-4">Footer (Kaki Halaman)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Kantor</label>
                        <textarea name="footer_address" rows="3" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">{{ old('footer_address', $footer_address) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Kontak</label>
                        <input type="email" name="footer_email" value="{{ old('footer_email', $footer_email) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="footer_phone" value="{{ old('footer_phone', $footer_phone) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teks Copyright</label>
                        <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $footer_copyright) }}" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Social Media</h3>
                <p class="text-sm text-gray-500 mb-4">Kosongkan jika tidak ingin menampilkan link social media tertentu.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                        <input type="url" name="social_facebook" value="{{ old('social_facebook', $social_facebook ?? '') }}" placeholder="https://facebook.com/username" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                        <input type="url" name="social_instagram" value="{{ old('social_instagram', $social_instagram ?? '') }}" placeholder="https://instagram.com/username" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter / X</label>
                        <input type="url" name="social_twitter" value="{{ old('social_twitter', $social_twitter ?? '') }}" placeholder="https://twitter.com/username" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                        <input type="url" name="social_linkedin" value="{{ old('social_linkedin', $social_linkedin ?? '') }}" placeholder="https://linkedin.com/company/name" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">YouTube</label>
                        <input type="url" name="social_youtube" value="{{ old('social_youtube', $social_youtube ?? '') }}" placeholder="https://youtube.com/@channel" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                        <input type="text" name="social_whatsapp" value="{{ old('social_whatsapp', $social_whatsapp ?? '') }}" placeholder="628123456789" class="w-full border-gray-300 rounded shadow-sm focus:ring-slate-500 focus:border-slate-500">
                        <p class="text-xs text-gray-500 mt-1">Masukkan nomor tanpa + atau spasi (contoh: 628123456789)</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
@endsection
