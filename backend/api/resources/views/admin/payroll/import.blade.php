@extends('layouts.admin')

@section('title', 'Import Payroll')
@section('page_title', 'Import Payroll')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Upload File Excel Payroll</h2>
        </div>

        <form method="POST" action="{{ route('admin.payroll.import.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Gaji <span class="text-red-500">*</span></label>
                <input type="text" name="period_month" value="{{ old('period_month', date('F Y')) }}" required
                    class="w-full border rounded px-3 py-2 @error('period_month') border-red-500 @enderror"
                    placeholder="Contoh: November 2025">
                @error('period_month')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Format: Nama Bulan Tahun (contoh: November 2025)</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Tanda Tangan <span class="text-red-500">*</span></label>
                    <input type="text" name="sign_location" value="{{ old('sign_location', 'Jakarta') }}" required
                        class="w-full border rounded px-3 py-2 @error('sign_location') border-red-500 @enderror"
                        placeholder="Jakarta">
                    @error('sign_location')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Tanda Tangan <span class="text-red-500">*</span></label>
                    <input type="date" name="sign_date" value="{{ old('sign_date', date('Y-m-d')) }}" required
                        class="w-full border rounded px-3 py-2 @error('sign_date') border-red-500 @enderror">
                    @error('sign_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel <span class="text-red-500">*</span></label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                    class="w-full border rounded px-3 py-2 @error('file') border-red-500 @enderror">
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Format: .xlsx, .xls, atau .csv (max 10MB)</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded p-4">
                <h3 class="font-medium text-blue-800 mb-2">Panduan Kolom Excel</h3>
                <div class="text-sm text-blue-700 space-y-2">
                    <p><strong>Kolom Wajib:</strong> nip, nama, unit, jabatan</p>
                    <p><strong>Kolom Pendapatan:</strong> gaji, rapel_gaji, tunjangan_jabatan, tunjangan_komunikasi, tunjangan_mess, tunjangan_penempatan, tunjangan_parkir, tunjangan_lain, rapel_tunjangan, bantuan_uang_makan, bantuan_uang_transport, lembur, backup_pengganti, dinas_luar, bpjs_tk_jht_income, bpjs_tk_jkm_income, bpjs_tk_jkk_income, bpjs_tk_jp_income, bpjs_kesehatan_income, lain_lain_income</p>
                    <p><strong>Kolom Potongan:</strong> unpaid_gaji, unpaid_tunjangan, bpjs_tk_jht_deduction, bpjs_tk_jkm_deduction, bpjs_tk_jkk_deduction, bpjs_tk_jp_deduction, bpjs_kesehatan_deduction, diksar, bpr, seragam, koperasi, ketidakhadiran_1, ketidakhadiran_2, pph21_gaji, pph21_thr, lain_lain_deduction</p>
                    <p class="mt-2"><a href="{{ route('admin.payroll.template') }}" class="text-blue-600 hover:underline font-medium">Download Template Excel</a></p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Upload & Import
                </button>
                <a href="{{ route('admin.payroll.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
