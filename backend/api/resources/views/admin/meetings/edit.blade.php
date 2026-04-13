@extends('layouts.admin')

@section('title', 'Edit Meeting')
@section('page_title', 'Edit Meeting')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Edit Meeting</h2>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi meeting.</p>
        </div>
        <form method="POST" action="{{ route('admin.meetings.update', $meeting) }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Meeting <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $meeting->title) }}" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: Rapat Koordinasi Harian">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Deskripsi singkat tentang meeting...">{{ old('description', $meeting->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password (Opsional)</label>
                    <input type="text" name="password" id="password" value="{{ old('password', $meeting->password) }}"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Kosongkan jika tidak perlu">
                </div>
                <div>
                    <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-1">Maks. Peserta <span class="text-red-500">*</span></label>
                    <input type="number" name="max_participants" id="max_participants" value="{{ old('max_participants', $meeting->max_participants) }}" min="2" max="500" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">Jadwal Meeting</label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                    value="{{ old('scheduled_at', $meeting->scheduled_at ? $meeting->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika ingin mulai kapan saja.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Link Meeting</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $meeting->join_url }}"
                        class="flex-1 bg-gray-50 border border-gray-300 rounded px-3 py-2 text-sm text-gray-600">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $meeting->join_url }}').then(() => alert('Link disalin!'))"
                        class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">
                        Copy
                    </button>
                </div>
            </div>

            <div class="border-t pt-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Pengaturan Meeting</h3>
                @php $settings = $meeting->settings ?? []; @endphp
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="settings[mute_on_join]" value="1"
                            {{ old('settings.mute_on_join', $settings['mute_on_join'] ?? false) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Mute mikrofon saat bergabung</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="settings[disable_camera_on_join]" value="1"
                            {{ old('settings.disable_camera_on_join', $settings['disable_camera_on_join'] ?? false) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Matikan kamera saat bergabung</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="settings[enable_lobby]" value="1"
                            {{ old('settings.enable_lobby', $settings['enable_lobby'] ?? false) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Aktifkan ruang tunggu (Lobby)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="settings[enable_recording]" value="1"
                            {{ old('settings.enable_recording', $settings['enable_recording'] ?? false) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Izinkan rekaman meeting</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.meetings.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
