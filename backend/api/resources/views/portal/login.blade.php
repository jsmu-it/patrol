@extends('layouts.portal')
@section('title', 'Masuk Portal')

@section('content')
<div class="max-w-md mx-auto mt-8 sm:mt-16">
    <div class="text-center mb-6">
        <img src="{{ asset('images/admin-logo.png') }}" alt="JSMU" class="h-16 w-auto mx-auto">
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Portal Kerja</h1>
        <p class="mt-1 text-sm text-gray-600">Masuk dengan NIP dan kata sandi Anda.</p>
    </div>

    <form action="{{ route('portal.login.post') }}" method="POST" class="pt-card pt-card-pad">
        @csrf
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
            <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                   inputmode="numeric" autocomplete="username"
                   class="pt-input">
        </div>
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
            <input type="password" name="password" id="password" required autocomplete="current-password"
                   class="pt-input">
        </div>
        <label class="flex items-center gap-2 mb-5 text-sm text-gray-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-gray-300">
            Ingat saya di perangkat ini
        </label>
        <button type="submit" class="pt-btn pt-btn-utama w-full">
            Masuk
        </button>
    </form>

    <p class="mt-4 text-xs text-center text-gray-500">
        Belum pernah mengisi data diri? Lengkapi dulu di <a href="{{ route('pdp.form') }}" class="text-blue-700 hover:underline">formulir PDP</a>.
    </p>
</div>
@endsection
