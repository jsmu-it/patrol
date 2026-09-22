@extends('layouts.portal')
@section('title', 'Folder Terkunci')

@section('content')
<div class="max-w-md mx-auto mt-8 sm:mt-16">
    <div class="text-center mb-6">
        <span class="inline-flex w-12 h-12 rounded-xl bg-yellow-50 text-yellow-700 items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </span>
        <h1 class="mt-4 text-xl font-bold text-gray-900">{{ $folder->name }}</h1>
        <p class="mt-1 text-sm text-gray-600">Folder ini dilindungi kata sandi.</p>
    </div>

    <form action="{{ route('portal.share.buka', $token) }}" method="POST" class="pt-card pt-card-pad">
        @csrf
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata sandi folder</label>
        <input type="password" name="password" id="password" required autofocus
               class="pt-input">
        <button type="submit" class="pt-btn pt-btn-utama w-full mt-4">
            Buka Folder
        </button>
    </form>
</div>
@endsection
