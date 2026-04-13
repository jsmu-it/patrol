@extends('layouts.admin')

@section('title', 'Psikotest')
@section('page_title', 'Psikotest')

@section('content')
<div class="space-y-6">
    {{-- Action Button --}}
    <div class="flex justify-end">
        <a href="{{ route('admin.psikotest.sessions.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Undang Peserta Test
        </a>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="text-sm text-gray-500">Soal Kraepelin Aktif</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['kraepelin_questions'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="text-sm text-gray-500">Soal Papikostik Aktif</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['papikostik_questions'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="text-sm text-gray-500">Test Selesai</div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['completed_sessions'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
            <div class="text-sm text-gray-500">Test Pending</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_sessions'] }}</div>
        </div>
    </div>

    {{-- 3 Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Kraepelin Card --}}
        <a href="{{ route('admin.psikotest.kraepelin.index') }}" class="group">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:border-blue-200 transition-all duration-300">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-500 transition-colors">
                        <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Kraepelin</h3>
                        <p class="text-sm text-gray-500">Test Kecepatan & Ketelitian</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Test hitungan cepat untuk mengukur kecepatan kerja, ketelitian, konsistensi, dan ketahanan kerja.
                </p>
                <div class="flex items-center text-blue-600 text-sm font-medium">
                    Kelola Soal
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Papikostik Card --}}
        <a href="{{ route('admin.psikotest.papikostik.index') }}" class="group">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:border-purple-200 transition-all duration-300">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-500 transition-colors">
                        <svg class="w-7 h-7 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Papikostik</h3>
                        <p class="text-sm text-gray-500">Test Kepribadian</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Test kepribadian dengan 90 pasang pernyataan untuk mengukur 20 dimensi kepribadian kerja.
                </p>
                <div class="flex items-center text-purple-600 text-sm font-medium">
                    Kelola Soal
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Dashboard Card --}}
        <a href="{{ route('admin.psikotest.dashboard') }}" class="group">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-500 transition-colors">
                        <svg class="w-7 h-7 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Dashboard Test</h3>
                        <p class="text-sm text-gray-500">Hasil & Analisis</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Lihat hasil test, grafik analisis, dan export laporan PDF untuk setiap peserta test.
                </p>
                <div class="flex items-center text-emerald-600 text-sm font-medium">
                    Lihat Hasil
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
