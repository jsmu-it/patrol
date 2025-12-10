@extends('layouts.admin')

@section('title', 'Detail Slip Gaji - ' . $slip->name)
@section('page_title', 'Detail Slip Gaji')

@section('content')
<div class="max-w-4xl">
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('admin.payroll.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Kembali ke Daftar</a>
        <a href="{{ route('admin.payroll.print', $slip) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
            Print Slip Gaji
        </a>
    </div>

    <div class="bg-white rounded shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-bold">TANDA TERIMA GAJI BULAN {{ strtoupper($slip->period_month) }}</h2>
            <p class="text-sm text-gray-600">PT JAYA SAKTI MANDIRI UNGGUL</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="space-y-2">
                    <div class="flex">
                        <span class="w-24 font-semibold">NAMA</span>
                        <span class="mx-2">:</span>
                        <span class="font-bold">{{ $slip->name }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-24 font-semibold">NIP</span>
                        <span class="mx-2">:</span>
                        <span class="font-bold font-mono">{{ $slip->nip }}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex">
                        <span class="w-24 font-semibold">UNIT / SITE</span>
                        <span class="mx-2">:</span>
                        <span class="font-bold">{{ $slip->unit ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-24 font-semibold">JABATAN</span>
                        <span class="mx-2">:</span>
                        <span class="font-bold">{{ $slip->position ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="border rounded overflow-hidden mb-6">
                <div class="grid grid-cols-2">
                    <div class="border-r">
                        <div class="bg-gray-100 px-4 py-2 font-bold text-center border-b">PENERIMAAN</div>
                        <div class="p-4">
                            @forelse($slip->incomeItems as $item)
                            <div class="flex justify-between py-1">
                                <span>{{ $item->label }}</span>
                                <span class="font-mono">{{ number_format($item->amount, 0, ',', '.') }}</span>
                            </div>
                            @empty
                            <p class="text-gray-500 text-center py-2">Tidak ada item pendapatan</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <div class="bg-gray-100 px-4 py-2 font-bold text-center border-b">POTONGAN</div>
                        <div class="p-4">
                            @forelse($slip->deductionItems as $item)
                            <div class="flex justify-between py-1">
                                <span>{{ $item->label }}</span>
                                <span class="font-mono text-red-600">{{ number_format($item->amount, 0, ',', '.') }}</span>
                            </div>
                            @empty
                            <p class="text-gray-500 text-center py-2">Tidak ada potongan</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t pt-4">
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-bold">PENDAPATAN</span>
                            <span class="font-mono">{{ number_format($slip->total_income, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-bold">JUMLAH POTONGAN</span>
                            <span class="font-mono text-red-600">{{ number_format($slip->total_deduction, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-bold">PENDAPATAN DITERIMA</span>
                            <span class="font-mono font-bold text-blue-600 text-lg">{{ number_format($slip->net_income, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p>{{ $slip->sign_location }}, {{ $slip->sign_date?->format('d/m/Y') }}</p>
                        <p class="mt-1">Yang menerima,</p>
                        <div class="mt-12 border-t border-gray-400 inline-block w-48"></div>
                        <p class="font-bold">({{ $slip->name }})</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
