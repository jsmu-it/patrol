@extends('layouts.admin')

@section('title', 'Preview PKWT')
@section('page_title', 'Preview PKWT - ' . $pkwt->name)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Action Bar --}}
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('admin.pkwt.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
            <div class="flex gap-2">
                <form action="{{ route('admin.pkwt.send', $pkwt) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                        Kirim ke Karyawan
                    </button>
                </form>
                <a href="{{ route('admin.pkwt.print', $pkwt) }}" target="_blank" 
                    class="px-4 py-2 bg-slate-900 text-white rounded text-sm hover:bg-slate-800">
                    Cetak
                </a>
            </div>
        </div>

        {{-- PKWT Info --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <label class="text-gray-500 text-xs uppercase">Nomor PKWT</label>
                    <p class="font-semibold font-mono">{{ $pkwt->pkwt_number }}</p>
                </div>
                <div>
                    <label class="text-gray-500 text-xs uppercase">Nama</label>
                    <p class="font-semibold">{{ $pkwt->name }}</p>
                </div>
                <div>
                    <label class="text-gray-500 text-xs uppercase">Jabatan</label>
                    <p class="font-semibold">{{ $pkwt->position?->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-gray-500 text-xs uppercase">Unit/Project</label>
                    <p class="font-semibold">{{ $pkwt->project?->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-gray-500 text-xs uppercase">Masa Kontrak</label>
                    <p class="font-semibold">{{ $pkwt->contract_period }}</p>
                </div>
                <div>
                    <label class="text-gray-500 text-xs uppercase">Total Pendapatan</label>
                    <p class="font-semibold text-green-600">Rp {{ number_format($pkwt->total_income, 0, ',', '.') }}</p>
                </div>
                <div>
                    <label class="text-gray-500 text-xs uppercase">Total Potongan</label>
                    <p class="font-semibold text-red-600">Rp {{ number_format($pkwt->total_deduction, 0, ',', '.') }}</p>
                </div>
                <div>
                    <label class="text-gray-500 text-xs uppercase">Gaji Bersih</label>
                    <p class="font-semibold text-blue-600">Rp {{ number_format($pkwt->net_salary, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Income Breakdown --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Rincian Pendapatan</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                @foreach($pkwt->incomes as $income)
                    @if($income->amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $income->incomeType->name }}</span>
                            <span class="font-medium">Rp {{ number_format($income->amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="border-t mt-4 pt-4 flex justify-between font-semibold">
                <span>Total Pendapatan</span>
                <span class="text-green-600">Rp {{ number_format($pkwt->total_income, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Deduction Breakdown --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Rincian Potongan</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                @foreach($pkwt->deductions as $deduction)
                    @if($deduction->amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $deduction->deductionType->name }}</span>
                            <span class="font-medium">Rp {{ number_format($deduction->amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="border-t mt-4 pt-4 flex justify-between font-semibold">
                <span>Total Potongan</span>
                <span class="text-red-600">Rp {{ number_format($pkwt->total_deduction, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Document Preview --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Preview Dokumen PKWT</h3>
            @if($template)
                <div class="prose max-w-none border rounded p-6 bg-gray-50">
                    {!! $template !!}
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p>Template PKWT belum tersedia untuk project ini.</p>
                    @if($pkwt->project)
                        <a href="{{ route('admin.projects.pkwt.edit', $pkwt->project) }}" class="text-blue-600 hover:underline mt-2 inline-block">
                            Buat Template PKWT
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
